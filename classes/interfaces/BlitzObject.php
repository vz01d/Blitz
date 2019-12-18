<?php
/**
 * Blitz Nyan Automation automation
 *  
 * @category Theme
 * @package  Blitz
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 */
namespace blitz\interfaces;

/**
 * Class BlitzObject - object interface for Blitz objects
 * 
 * @category Theme
 * @package  Blitz
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 */
interface BlitzObject
{
    /**
     * Used for basic object loading
     * like post type registration and alike
     * running on WP init
     */
    function loadObject(): void;

    /**
     * Used for object meta field loading
     * running on acf/init
     */
    function loadObjectMeta(): void;
}
