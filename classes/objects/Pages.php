<?php
/*phpcs:disable*/
/**
 * Pages
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
 * Class Pages - load Pages object for Theme
 * 
 * @category Theme
 * @package  Blitz
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 */
class Pages extends BlitzObjects implements BlitzObject
{
    const CPT_NAME = 'page';

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
         * Page settings
         */
		$pageSettings = new FieldsBuilder('page_settings');
        $pageSettings
            ->addPostObject('page_slider', [
                'label' => 'Slider',
                'instructions' => 'Select a slider which will replace the featured image.',
                'required' => 0,
                'post_type' => [Sliders::CPT_NAME],
                'taxonomy' => [],
                'allow_null' => 1,
                'multiple' => 0,
                'return_format' => 'object',
                'ui' => 1,
            ])
            ->setGroupConfig('position', 'side')
			->setLocation('post_type', '==', self::CPT_NAME);

        acf_add_local_field_group($pageSettings->build());
    }

    /**
     * Load Object properties - empty in this class as the
     * related page type "page" is already registered
     * 
     * @return void
     */
    public function loadObject(): void
    {
    }
}
