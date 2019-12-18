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
namespace blitz\interfaces;

/**
 * Class BlitzApiObject - api objects need to implement
 * certain endpoints defined by this interface
 * 
 * @category Theme
 * @package  Blitz
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 */
interface BlitzApiObject {
    /**
     * Register all endpoints related to the object
     */
    public function registerEndpoints(): void;
}
