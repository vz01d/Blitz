<?php
/*phpcs:disable*/
/**
 * Posts
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
use blitz\core\BlitzObjects;

/**
 * Class Posts - load Posts object for Theme
 * 
 * @category Theme
 * @package  Blitz
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 */
class Posts extends BlitzObjects implements BlitzObject
{
    const CPT_NAME = 'post';

    /**
     *
     */
    public function __construct()
    {
    }

    /**
     * Load objects custom fields
     * 
     * @return void
     */
    public function loadObjectMeta(): void
    {
        /**
         * Post settings
         */
		$postSettings = new FieldsBuilder('post_settings');
        $postSettings
            ->addTab('layout')
            ->addTrueFalse('hide_header', [
                'label' => 'Hide header',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => [],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'message' => '',
                'default_value' => 0,
                'ui' => 1,
                'ui_on_text' => 'hidden',
                'ui_off_text' => 'visible',
            ])
            ->setGroupConfig('position', 'acf_after_title')
			->setLocation('post_type', '==', self::CPT_NAME);

        acf_add_local_field_group($postSettings->build());
    }

    /**
     * Load Object properties - empty in this class as the
     * related post type "post" is already registered
     * 
     * @return void
     */
    public function loadObject(): void
    {
    }
}
