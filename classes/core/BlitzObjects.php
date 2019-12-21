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

use blitz\Blitz;
use blitz\core\BlitzApi;

/**
 * Class BlitzObjects - Blitz master object container
 * 
 * @category Theme
 * @package  Blitz
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 */
class BlitzObjects extends Blitz
{
    /**
     * Stored objects
     */
    private $_objects = [];

    /**
     * Load all required objects if they
     * have BlitzObject interface implemented
     * 
     * @param array $objects - the objects array containing
     * namespaced FQN to the object
     */
    public function __construct(array $objects)
    {
        // load objects if they exist
        array_map(function($object){
            $interfaces = class_implements($object);
            if (class_exists($object)) {
                $o = new $object;
                if (isset($interfaces['blitz\interfaces\BlitzObject'])) {
                    add_action('init', [$o, 'loadObject']);
                    add_action('acf/init', [$o, 'loadObjectMeta']);
                }

                if (isset($interfaces['blitz\interfaces\BlitzApiObject'])) {
                    $o->registerEndpoints();
                }

                // not all objects require this
                if (true === is_callable([$o, 'loadRestFields'])) {
                    $o->loadRestFields();
                }

                $this->_objects[$this->_getName($o)] = $o;
            }
        }, $objects);
    }

    /**
     * Return a theme object by it's name
     * 
     * @param string $name - the name of the object
     * 
     */
    protected function getObjectByName(string $name)
    {
        $n = strtolower($name);
        if (isset($this->_objects[$n])) {
            return $this->_objects[$n];
        }
    }

	private function _getName($o) {
		return strtolower((new \ReflectionClass($o))->getShortName());
	}
}
