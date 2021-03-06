<?php
/*phpcs:disable*/
/**
 * Sliders
 *  
 * @category Theme
 * @package  Blitz
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 */
namespace Blitz\objects;

use StoutLogic\AcfBuilder\FieldsBuilder;
use blitz\interfaces\BlitzObject;
use blitz\interfaces\BlitzApiObject;
use blitz\core\BlitzApi;
use blitz\core\BlitzObjects;

/**
 * Class Sliders - load Sliders object for the Theme
 * providing a way to create sliders using acf-builder
 * 
 * @category Theme
 * @package  Blitz
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 */
class Sliders extends BlitzObjects implements BlitzObject, BlitzApiObject
{
    const CPT_NAME = 'fet-slider';

    /**
     *
     */
    public function __construct()
    {
    }

    /**
     * Load Object properties - empty in this class as the
     * related post type "post" is already registered
     * 
     * @return void
     */
    public function loadObject(): void
    {
        // register post type
        register_extended_post_type(self::CPT_NAME, 
            [
                'supports'           => ['title'],
                'with_front'         => false,
                'public'             => false,
                'publicly_queryable' => false,
                'show_in_nav_menus'  => true,
                'show_ui'            => true
            ],
            [
                'singular' => 'Slider',
                'plural'   => 'Sliders'
            ]
        );
    }

    /**
     * Load objects custom fields
     * 
     * @return void
     */
    public function loadObjectMeta(): void
    {
        /**
         * Slider Meta
         */
		$slider = new FieldsBuilder('slider');
        $slider
            ->addRepeater('slides', [
                'label' => 'Slides',
                'min' => 1,
                'max' => 7,
                'button_label' => 'Add Slide',
                'layout' => 'block',
            ])
                ->addImage('slider_image', [
                    'label' => 'Image',
                    'instructions' => 'Select an image for the slide',
                    'wrapper' => [
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ],
                    'return_format' => 'array',
                    'preview_size' => 'thumbnail',
                    'library' => 'all',
                    'mime_types' => 'png, jpg, gif, webp, svg',
                ])
                ->addWysiwyg('slide_text', [
                    'label' => 'Slide text',
                    'instructions' => 'Text on the slide',
                ])
                ->addRadio('slide_content_position')
                    ->addChoices('left', 'center', 'right')
                    ->conditional('slide_text', '!=', '')
                ->addTrueFalse('show_button', [
                    'label' => 'Show button on slide?',
                    'default_value' => 0,
                    'ui' => 1,
                    'ui_on_text' => 'Yes',
                    'ui_off_text' => 'No',
                ])
                ->addLink('button_link', [
                    'label' => 'Link Field',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => [
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ],
                    'return_format' => 'array',
                ])
                    ->conditional('show_button', '==', '1')
            ->setGroupConfig('position', 'acf_after_title')
			->setLocation('post_type', '==', self::CPT_NAME);

        acf_add_local_field_group($slider->build());
    }

    /**
     * Register an endpoint to fetch slides for a given slider
     */
    public function registerEndpoints(): void
    {
        if (false === BlitzApi::registerEndpoint(
            'slides',
            [$this, 'getSlides']
        )){
            echo 'WARNING: endpoint registration failed for some reason maybe your callback is not callable.'.PHP_EOL;
        }
    }

    /**
     * Return the slides for the slider
     * 
     * @param \WP_REST_Request $request - the request object
     * 
     * @return \WP_REST_Response - the slides array containing image urls
     */
    public function getSlides(\WP_REST_Request $request): \WP_REST_Response
    {
        $sliderId = $request->get_header('B-PID');
        if (false != $sliderId) {
            $slides = $this->_getSlidesBySliderId($sliderId);
            $returnCode = 200;
        } else {
            $slides['is404'] = true;
            $returnCode = 404;
        }

        $response = new \WP_REST_Response($slides, $returnCode);
        return $response;
    }

    /**
     * Return the slides by provided slider id
     * the slider MUST be an acf repeater
     * 
     * @param int $sliderId - the id of the slider
     * 
     * @return array - the array containing the slides including all data
     */
    private function _getSlidesBySliderId(int $sliderId): array
    {
        $sliderData = [];

        // slider is a WP_Post object containing acf repeater subfields
        $slides = get_field('slides', $sliderId);
        if (false != $slides) {
            foreach ($slides as $slide) {
                $s = new \stdClass;
                $imgSrc = wp_get_attachment_image_src($slide['slider_image']['ID'], 'full');
                $s->imageUrl = isset($imgSrc[0]) ? $imgSrc[0] : '';
                $s->text = $slide['slide_text'];

                if (false !== $slide['show_button']) {
                    $s->btnLink = $slide['button_link'];
                }

                $sliderData[] = $s;
            }
        }

        return $sliderData;
    }
}
