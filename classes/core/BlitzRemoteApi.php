<?php
/*phpcs:disable*/
/**
 * Blitz
 *  
 * @category Theme
 * @package  Blitz
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 */
namespace blitz\core;

use blitz\core\BlitzApi;

// octopus & hexagon
use blitz\core\crypto\Octopus;

/**
 * Class BlitzRemoteApi - Blitz Remote Api
 * this class will provide functions needed to connect
 * the Site with a Remote network: https://github.com/sebot/Remote
 * 
 * @category Theme
 * @package  BlitzRemoteApi
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 * @version  1.0
 */
class BlitzRemoteApi extends Octopus
{
    /**
     * API Version
     */
    const API_VERSION = 'v1';

    /**
     * The current route accessed hash
     */
    private static $rHash = null;

    /**
     * Register all required endpoints
     */
    public function __construct()
    {
        $endpointsOpen = get_option('re__nxr', []);

        // handshake is required
        BlitzApi::registerEndpoint(
            'remote/handshake',
            [$this, 'handshake'],
            'POST'
        );

        // check if site is connected before registering the endpoint
        $isConnected = get_option('isConnected');
        if (true != $isConnected && true === in_array(self::rhashRoute('connect'), $endpointsOpen)) {
            $this->_requireWPMedia();
            if (false === BlitzApi::registerEndpoint(
                'remote/connect',
                [$this, 'connectSite'],
                'POST'
            )){
                // TODO: exception handling
                echo 'WARNING: endpoint registration failed for some reason maybe your callback is not callable.'.PHP_EOL;
            }
        }

        // other endpoints
        if (true === in_array(self::rhashRoute('update'), $endpointsOpen)) {
            $this->_requireWPMedia();
            if (false === BlitzApi::registerEndpoint(
                'remote/update',
                [$this, 'updateSite'],
                'POST'
            )){
                // TODO: exception handling
                echo 'WARNING: endpoint registration failed for some reason maybe your callback is not callable.'.PHP_EOL;
            }
        }

        if (true === in_array(self::rhashRoute('updatepost'), $endpointsOpen)) {
            $this->_requireWPMedia();
            if (false === BlitzApi::registerEndpoint(
                'remote/updatepost',
                [$this, 'updatePost'],
                'POST'
            )){
                // TODO: exception handling
                echo 'WARNING: endpoint registration failed for some reason maybe your callback is not callable.'.PHP_EOL;
            }
        }

        add_filter('rest_request_before_callbacks', [$this, 'beforeRequest'], 5, 3);

        // init Octopus
        $secret = get_field('rm_secret_code', 'option');
        parent::__construct($secret);
    }

    /**
     * Require any wp-admin lib needed for working with files
     */
    private function _requireWPMedia(): void
    {
        // required for file uploads
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
    }

    /**
     * Get an endpoint from request object which is rHashed by default
     * 
     * @param \WP_REST_Request $request - the request object
     * @param $response - the existing response
     */
    private function _getEndpointFromRequest(\WP_REST_Request $request, $response)
    {
        $endpoint = '';
        $route = $request->get_route();
        // return if no remote/blitz route is accessed
        if (! $this->_isRemoteRequest($route)) {
            return $response;
        }

        $endpoint = str_replace(
            '/blitz/v2/remote/',
            '',
            $route
        );

        return $endpoint;
    }

    /**
     * Validates a given endpoint beeing registered and then returns the
     * reverse hash of it. 
     * 
     * @param string $endpoint - the endpoint
     * 
     * @return string - the rHash of the endpoint
     */
    private function _validateAndHashEndpoint(string $endpoint): string
    {
        if (false === BlitzApi::isValidEndpoint($endpoint)) {
            $data = [
                'error' => true,
                'message' => 'unknown endpoint. What are you looking for?'
            ];
            $res = new \WP_REST_Response($data, 403);
        }
        $res = self::rHashRoute($endpoint);

        return $res;
    }
    
    /**
     * Check if it's a remote request
     * 
     * @param string $route - the route beeing accessed
     * 
     * @return bool - wether it is a Remote request or not
     */
    private function _isRemoteRequest(string $route): bool
    {
        return false != strpos($route, 'remote');
    }

    private function _sendResponse(string $rHash, array $data = []): \WP_REST_Response
    {
        $this->_cleanUpRequestVars($rHash);
        return new \WP_REST_Response($data, 200);
    }

    /**
     * Run before any request and validate the incoming messages
     * 
     * @param $response - the response object
     * @param array $handler - the handler for the request
     * @param \WP_REST_Request - the request object
     */
    public function beforeRequest($response, array $handler, \WP_REST_Request $request)
    {
        $endpoint = $this->_getEndpointFromRequest($request, $response);
        if ($endpoint === $response) return $response;
        $rHash = $this->_validateAndHashEndpoint($endpoint);

        $mac = $request->get_param('mac');
        $msg = $request->get_param('msg');
        if (!is_string($mac) || null == $mac
        || !is_string($msg) || null == $msg) {
            $data = [
                'error' => true,
                'message' => 'malformed package received. next time I will bite.'
            ];
            $response = new \WP_REST_Response($data, 403);
        }

        // on hs request get saltVector from request, else from options
        if ('handshake' === $endpoint) {
            $saltVector = $request->get_param('sv');
        } else {
            $saltVector = $this->_getNxOption('re__nxsV', $rHash);
        }

        $msgValid = $this->verifyMessageBySaltVector($msg, $mac, $saltVector);
        if (true != $msgValid) {
            $data = [
                'error' => true,
                'message' => 'invalid message.'
            ];
            $response = new \WP_REST_Response($data, 403);
        }

        return $response;
    }

    /**
     * Update request for this site
     * 
     * @param \WP_REST_Request $request - the request object
     * 
     * @return \WP_REST_Response - wether the update succeeded or not
     */
    public function updateSite(\WP_REST_Request $request): \WP_REST_Response
    {
        // read message
        [$msgObj, $rHash] = $this->_readMessageFromRequest($request);
        if (false !== isset($msgObj->requestData) && false === is_a($msgObj, 'WP_REST_Response')) {
            // extract data
            [
                $showHeader, 
                $showNavigation, 
                $showSlider, 
                $showFeaturedImage,
                $siteLogoUrl
            ] = $msgObj->requestData;

            // update settings for site
            update_field('show_header', $showHeader, 'option');
            update_field('show_navigation', $showNavigation, 'option');
            update_field('show_slider', $showSlider, 'option');
            update_field('show_featured_image', $showFeaturedImage, 'option');

            // get current logo
            $currentLogo = get_field('logo', 'option');
            if (false === $currentLogo) {
                // no logo set right now
                [$logoUrl, $logoId] = $this->_fetchImageFromUrl($siteLogoUrl);
                update_field('logo', $logoId, 'option');    
            } else {
                // upload new logo if changed
                $currentLogoName = basename($currentLogo['url']);
                $siteLogo        = basename($siteLogoUrl);
                if (false === strpos($currentLogoName, $siteLogo)) {
                    wp_delete_attachment($currentLogo['ID'], true);
                
                    // down && upload logo
                    [$logoUrl, $logoId] = $this->_fetchImageFromUrl($siteLogoUrl);
                    update_field('logo', $logoId, 'option');
                }
            }
        }

        // respond
        $data = [
            'error' => false,
            'message' => 'OK'
        ];

        return $this->_sendResponse($rHash, $data);
    }
    
    /**
     * Connect request for this site
     * 
     * @param \WP_REST_Request $request - the request object
     * 
     * @return \WP_REST_Response - wether the connection succeeded or not
     */
    public function connectSite(\WP_REST_Request $request): \WP_REST_Response
    {
        // read message
        [$msgObj, $rHash] = $this->_readMessageFromRequest($request);
        if (false !== isset($msgObj->requestData) && false === is_a($msgObj, 'WP_REST_Response')) {
            // extract data
            [
                $showHeader, 
                $showNavigation, 
                $showSlider, 
                $showFeaturedImage,
                $siteLogoUrl
            ] = $msgObj->requestData;

            // update settings for site
            update_field('show_header', $showHeader, 'option');
            update_field('show_navigation', $showNavigation, 'option');
            update_field('show_slider', $showSlider, 'option');
            update_field('show_featured_image', $showFeaturedImage, 'option');
            
            // get current logo
            $currentLogo = get_field('logo', 'option');
            if (false === $currentLogo) {
                // no logo set right now
                [$logoUrl, $logoId] = $this->_fetchImageFromUrl($siteLogoUrl);
                update_field('logo', $logoId, 'option');    
            }

            // if no errors happened do any custom actions on the site here
            update_option('isConnected', true);
        }

        $data = [
            'error' => false,
            'message' => 'OK'
        ];
        
        return $this->_sendResponse($rHash, $data);
    }

    /**
     * Update a post on a remote site. The post will be added if it
     * does not exist yet.
     * 
     * @param \WP_REST_Request $request - the request object
     * 
     * @return \WP_REST_Response - the remote id of the post
     */
    public function updatePost(\WP_REST_Request $request): \WP_REST_Response
    {
        // read message
        [$msgObj, $rHash] = $this->_readMessageFromRequest($request);
        if (false !== isset($msgObj->requestData) && false === is_a($msgObj, 'WP_REST_Response')) {
            // get post data
            $title           = $msgObj->requestData->title;
            $content         = $msgObj->requestData->content;
            $isHome          = $msgObj->requestData->isHome;
            $postType        = $msgObj->requestData->postType;
            $featuredImage   = $msgObj->requestData->featuredImage;
            $inContentImages = $msgObj->requestData->inContentImages;
            $status          = $msgObj->requestData->status;
            $uniqueId        = $msgObj->requestData->uniqueId;

            // check if post exists by unique id
            $args = [
                'post_type'   => $postType,
                'post_status' => $status,
                'meta_key'    => 'uniqueId',
                'meta_value'  => $uniqueId
            ];
            
            $qry = new \WP_Query($args);
            // insert new post and store it's id
            if ($qry->found_posts > 0) {
                // post exists
                if (isset($qry->posts[0])) {
                    $post = $qry->posts[0];

                    // update post thumbnail/featured image
                    $currentThumb = get_the_post_thumbnail_url($post->ID, 'full');

                    // no featured image set
                    if (false === $currentThumb) {
                        [$imgUrl, $thumbId] = $this->_fetchImageFromUrl($featuredImage);
                        set_post_thumbnail($post->ID, $thumbId);
                    } else {
                        $currentThumbFilename = str_replace('-scaled', '', basename($currentThumb));
                        $newFeaturedImage     = basename($featuredImage);
                        
                        // new image - delete old and create new one
                        if (false === strpos($currentThumbFilename, $newFeaturedImage)) {
                            $currentThumbId = get_post_thumbnail_id($post->ID);
                            if ('' != $currentThumbId) {
                                // deleted
                                wp_delete_attachment($currentThumbId, true);

                                // create
                                [$thumbUrl, $thumbId] = $this->_fetchImageFromUrl($featuredImage);
                                set_post_thumbnail($post->ID, $thumbId);
                            }
                        }
                    }

                    // ####### UPDATE IMAGES BEGIN ##########
                    // get current images in content
                    $currentInContentImages = $this->_getCurrentContentImages($post->post_content);

                    $imagesToDelete = [];
                    foreach ($currentInContentImages as $currentICImage) {
                        $imagesToDelete[$currentICImage['hash']] = $currentICImage;
                        if (false !== array_search($currentICImage['hash'], array_column($inContentImages, 'hash'))) {
                            // unset images if found in array b
                            unset($imagesToDelete[$currentICImage['hash']]);
                            $content = str_replace($currentICImage['hash'], $currentICImage['url'], $content);
                        }
                    }

                    $imagesToAdd = [];
                    // in content has items stored as objects instead of arrays
                    // keep in mind when refactoring
                    foreach ($inContentImages as $newICImage) {
                        $match = array_search($newICImage->hash, array_column($currentInContentImages, 'hash'));
                        if (false === $match) {
                            // image does not exist in content yet
                            $imagesToAdd[$newICImage->hash] = $newICImage;
                        }
                    }

                    if (count($imagesToDelete) > 0) {
                        foreach ($imagesToDelete as $mediaItem) {
                            // try to get the image id
                            $imageId = attachment_url_to_postid($mediaItem['url']);
                            if (is_int($imageId) && $imageId > 0) {
                                wp_delete_attachment($imageId, true);
                            }
                        }
                    }
                    
                    if (count($imagesToAdd) > 0) {
                        foreach ($imagesToAdd as $newMediaItem) {
                            // download file from remote
                            [$imgUrl] = $this->_fetchImageFromUrl($newMediaItem->url);
                            
                            // replace hash in content with new url
                            if (false != $imgUrl && is_string($imgUrl)) {
                                $content = str_replace($newMediaItem->hash, $imgUrl, $content);
                            }
                        }
                    }
                    // ####### UPDATE IMAGES END ##########

                    // update post with new urls
                    wp_update_post([
                        'ID'           => $post->ID,
                        'post_content' => $content,
                        'post_title'   => $title,
                        'post_status'  => $status
                    ]);

                    // is frontpage
                    if (true === boolval($isHome)) {
                        update_option('page_on_front', $post->ID);
                        update_option('show_on_front', 'page');
                    } else {
                        $currentFrontPid = get_option('page_on_front');
                        if (is_int($currentFrontPid) && $currentFrontPid > 0 && $currentFrontPid === $post->ID) {
                            update_option('page_on_front', '');
                            update_option('show_on_front', 'page');
                        }
                    }
                }
            } else {
                // create new post
                $pid = wp_insert_post([
                    'post_title'   => $title,
                    'post_content' => $content,
                    'post_status'  => $status,
                    'post_type'    => $postType
                ]);

                // extract and upload inContent images
                foreach ($inContentImages as $image) {
                    $imgUrl = media_sideload_image(
                        $image->url,
                        $pid,
                        '',
                        'src'
                    );
                    
                    // replace hash in content with new url
                    if (false != $imgUrl) {
                        $content = str_replace($image->hash, $imgUrl, $content);
                    }
                }

                // update post with new urls
                wp_update_post([
                    'ID' => $pid,
                    'post_content' => $content
                ]);

                // set page uniqueId to ensure it's only stored once
                update_post_meta($pid, 'uniqueId', $uniqueId);
                
                // is frontpage
                if (true === boolval($isHome)) {
                    update_option('page_on_front', $pid);
                    update_option('show_on_front', 'page');
                }

                // set featured image
                if ('' !== $featuredImage) {
                    $imgId = media_sideload_image(
                        $featuredImage,
                        $pid,
                        '',
                        'id'
                    );

                    if (is_int($imgId)) {
                        set_post_thumbnail($pid, $imgId);
                    }
                }
            }
        }

        $data = [
            'error' => false,
            'message' => '',
            'responseData' => [
                'pid' => isset($pid) ? $pid : $post->ID
            ]
        ];
        
        return $this->_sendResponse($rHash, $data);
    }

    /**
     * Fetches an image from url
     * 
     * @param string $url - the url to fetch the image from
     * 
     * @return array - the new image url and the thumb id
     */
    private function _fetchImageFromUrl(string $url): array
    {
        $thumbId = 0;
        $imgUrl = '';
        $tmpFile = download_url($url, 3);
        if (! is_wp_error($tmpFile)) {
            $file = [
                'name'     => basename($url),
                'type'     => mime_content_type($tmpFile),
                'tmp_name' => $tmpFile,
                'error'    => 0,
                'size'     => filesize($tmpFile)
            ];
            $thumbId = media_handle_sideload($file, $post->ID, basename($url));
            $imgUrl = wp_get_attachment_url($thumbId);
        }

        return [
            $imgUrl,
            $thumbId
        ];
    }

    /**
     * Get the current images stored in content as array containing the url and
     * md5 hash of the file contents
     * 
     * @param string $content - the content to search
     * 
     * @return array - the image array
     * @todo create a Post object on the client to handle all content related functions
     */
    private function _getCurrentContentImages(string $content): array
    {
        $attachments = null;
        preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $content, $attachments);
    
        $inContentImages = [];
        if (isset($attachments[1]) && is_array($attachments[1])) {
            foreach ($attachments[1] as $attachmentUrl) {
                // get file contents
                $file = file_get_contents($attachmentUrl);
                $b64 = base64_encode($file);

                // update content with md5 of b64
                $md5 = md5($b64);

                $inContentImages[] = [
                    'url'  => $attachmentUrl,
                    'hash' => $md5
                ];
            }
        }

        return $inContentImages;
    }

    /**
     * Handshake which will hand over the enc key
     * 
     * @param \WP_REST_Request $request - the request object
     * 
     * @return \WP_REST_Response - wether the connection succeeded or not
     * // TODO: init octopus with own keychain and encrypt response as well
     */
    public function handshake(\WP_REST_Request $request): \WP_REST_Response
    {
        $mac = $request->get_param('mac');
        $msg = $request->get_param('msg');
        $saltVector = $request->get_param('sv');

        // read message
        $plaintext = $this->decryptHandshake($msg, $saltVector);
        [$nxSalt, $nxRequest, $chiffreVector] = explode('::', $plaintext);

        // store request options
        $this->_addNxOption('re__nxs', [$nxRequest => $nxSalt]);
        $this->_addNxOption('re__nxr', [$nxRequest => $nxRequest]);
        $this->_addNxOption('re__nxcV', [$nxRequest => $chiffreVector]);
        $this->_addNxOption('re__nxsV', [$nxRequest => $saltVector]);
        
        $data = [
            'error' => false,
            'message' => 'OK'
        ];
        $response = new \WP_REST_Response($data, 200);
        return $response;
    }

    /**
     * Verify an incoming message containing
     * all required params message && hmac
     * 
     * @param \WP_REST_Request $request - the request containing the msg
     * @param string $rHash - the hash for the route
     * 
     * returns either WP_Rest_Response 403 or true
     */
    private function _verifyIncomingMsg(\WP_REST_Request $request, string $rHash)
    {
        $mac = $request->get_param('mac');
        $msg = $request->get_param('msg');
        if (!is_string($mac) || null == $mac
        || !is_string($msg) || null == $msg) {
            $data = [
                'error' => true,
                'message' => 'malformed package received. next time I will bite.'
            ];
            $response = new \WP_REST_Response($data, 403);
            return $response;
        }
        
        $saltVector = $this->_getNxOption('re__nxsV', $rHash);
        $msgValid = $this->verifyMessageBySaltVector($msg, $mac, $saltVector);
        if (true != $msgValid) {
            $data = [
                'error' => true,
                'message' => 'this did not work well. try again.'
            ];
            $response = new \WP_REST_Response($data, 403);
            return $response;
        }

        return true;
    }

    /**
     * Decrypt an incoming message
     * 
     * @param string $message - the message to decrypt
     * @param string $rHash - the hash of the route accessed
     * 
     * returns the json_decoded messag object on success or
     * 403 WP_REST_Response
     */
    private function _decryptIncomingMessage(string $message, string $rHash)
    {
        $nxSalt = $this->_getNxOption('re__nxs', $rHash);
        $chiffreVector = $this->_getNxOption('re__nxcV', $rHash);

        // TODO: abstract
        $decrypted = $this->decryptMessage($message, $nxSalt, $chiffreVector);
        $msgObj = json_decode($decrypted->getString());
        if (false === isset($msgObj->request) || $msgObj->request !== $rHash) {
            $data = [
                'error' => true,
                'message' => 'the request message is invalid.'
            ];
            $response = new \WP_REST_Response($data, 403);
            return $response;
        }

        return $msgObj;
    }

    /**
     * Cleans up any variables stored in options which are used during
     * the request
     * 
     * @param string $rHash - the hash of the route requested
     * 
     * @return void
     */
    private function _cleanUpRequestVars(string $rHash): void
    {
        $this->_removeNxOption('re__nxs', $rHash);
        $this->_removeNxOption('re__nxr', $rHash);
        $this->_removeNxOption('re__nxcV', $rHash);
        $this->_removeNxOption('re__nxsV', $rHash);
    }

    /**
     * Read the message, get the endpoint, validate endpoint,
     * decrypt message. The function will finally return an array
     * containing the message object and the rHash of the route
     * 
     * @param \WP_REST_Request $request - the request object
     * 
     * @return array - msgObject and rHash
     */
    private function _readMessageFromRequest(\WP_REST_Request $request): array
    {
        $endpoint = $this->_getEndpointFromRequest($request, null);
        $rHash = $this->_validateAndHashEndpoint($endpoint);
        $msgObj = $this->_decryptIncomingMessage($request->get_param('msg'), $rHash);

        return [
            $msgObj,
            $rHash
        ];
    }

    /**
     * Fire and Forget - add an nx option to table
     * don't forget to remove it on your 2nd request using
     * _removeNxOption
     * 
     * @param string $key - the key for the option
     * @param array $value - the value to store
     * 
     * @return void
     */
    private function _addNxOption(string $key, array $value): void
    {
        $currentVal = get_option($key);
        if (null == $currentVal) $currentVal = [];
        update_option($key, array_merge($currentVal, $value));
    }

    /**
     * Fire and Forget - remove an nx option from table
     * 
     * @param string $key - the key for the option
     * @param string $valuekey - the value to remove by it's key
     * 
     * @return void
     */
    private function _removeNxOption(string $key, string $valuekey): void
    {
        $currentVal = get_option($key, []);
        if (true === isset($currentVal[$valuekey])) {
            unset($currentVal[$valuekey]);
            update_option($key, $currentVal);
        }
    }

    /**
     * Helper function - get an option for the next request
     * 
     * @param string $key - possible values:
     * re__nxs, re__nxr, re__nxcV
     * @param string $valuekey - the rhash of the request
     * 
     * @return string - the option value
     */
    private function _getNxOption(string $key, string $valuekey): string
    {
        $opt = get_option($key, []);
        return true === isset($opt[$valuekey]) ? $opt[$valuekey] : [];
    }
}
