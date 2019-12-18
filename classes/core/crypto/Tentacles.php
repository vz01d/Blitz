<?php
/**
 * Remote
 *  
 * @category Multisite
 * @package  Remote
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 */
namespace blitz\core\crypto;

use blitz\core\crypto\ChiffreChain;

/**
 * Class Tentacle - returns a random chiffre used to generate the salt from
 * the secret provided to Octopus
 *  
 * TODO: extract from project to standalone composer package
 * TODO: add abstraction layer to allow for use of custom chiffre bundle or
 * adding them to the existing ones easily
 * 
 * @category Multisite
 * @package  Remote
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 */
final class Tentacles extends Octopus
{
    const DIR_BACKWARD = 0;
    const DIR_FORWARD  = 1;

    final private function __construct(){}

    /**
     * Access to ChiffreChain object
     */
    private static $chain;

    /**
     * Return multiple chiffre functions which have to
     * be used in the exact order to get a matching string.
     * 
     * @param int $entropy - the entropy level for the chain
     * @param int $vector - if you want to initialize a chain to reproduce a chiffre
     * 
     * @return void
     */
    public static function generateChiffreChain(int $entropy, int $vector = null): void
    {
        self::$chain = new ChiffreChain($entropy, $vector);
    }

    /**
     * Run through a series of provided chiffres
     * 
     * @param string $secret - the secret to run the chain on
     * @param int $direction - run forward (1) or backward (0)
     * 
     * @return array - the result secret and the vector used
     */
    public static function runChiffreChain(string $secret, int $direction): array
    {
        return [
            self::$chain->run($secret, $direction),
            self::$chain->getChain()
        ];
    }

    /**
     * Derive salt from secret
     * 
     * @param string $secret - the secret used for the salt
     * input example: 'dshar3hu4ruj3iwfmze4gtim4mg4nfeseitnx'
     * 
     * @return array - salt and the vector used to generate it
     * salt format returned (example): '\x36\x69\x9a\xff'
     * and vector: '1:11-11:30-6:4-5:27'
     */
    public static function generateSalt(string $secret): array
    {
        $parts = str_split($secret);
        $salt = '';
        $vector = '';
        for ($i = 0; $i < 4; $i++) {
            $v = random_int(0, count($parts)-1);
            $y = random_int(0, count($parts)-1);
            $vector .= $v.':'.$y.'-';
            $salt .= '\x'.$parts[$v].$parts[$y];
        }
        $vector = rtrim($vector, "-");

        return [
            $salt,
            $vector
        ];
    }

    /**
     * Get the salt providing the secret and the vector used to generate it.
     * 
     * @param string $secret - the secret used to generate the salt
     * @param string $vector - the vector used to get the salt
     * 
     * @return string - the salt format returned (example): '\x36\x69\x9a\xff'
     */
    public static function getSaltByVector(string $secret, string $vector): string
    {
        $parts = str_split($secret);
        $salt = '';
        $vectorParts = explode('-', $vector);
        foreach ($vectorParts as $vectorPart) {
            $indexes = explode(':', $vectorPart);
            $v = $indexes[0];
            $y = $indexes[1];
            $salt .= '\x'.$parts[$v].$parts[$y];
        }

        return $salt;
    }
}
