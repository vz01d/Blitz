<?php
/**
 * Blitz Assets
 *  
 * @category Theme
 * @package  Blitz
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 */
namespace blitz\interfaces;

/**
 * Class BlitzAsset - interface to autoload assets from a single object
 * 
 * @category Theme
 * @package  Blitz
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 */
interface BlitzAsset
{
    /**
     * Register a new asset
     */
    function registerAsset(): void;

    /**
     * Localize an asset (script)
     */
    function localizeAsset(): void;

    /**
     * Enqueue an asset
     */
    function enqueueAsset(): void;
}
