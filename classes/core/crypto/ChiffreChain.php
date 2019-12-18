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

/**
 * Class ChiffreChain - returns a set of functions for 
 * Octopus Tentacles to use to 'encrypt' the packages with
 * random chiffres like rot13 and some other ones. 
 * This way the client can reproduce salt needed
 * to use the secret
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
final class ChiffreChain
{
    /**
     * ChiffreChain for this request
     */
    private $chiffreChain;

    private $chiffres = [
        // '', // dirty hack: first one is unused
        'r13',
        'rev',
        'm5',
        'b64',
        'fuzzlebox'
    ];

    /**
     * Create a new chiffre chain
     * 
     * @param int $entropy - the entropy level to use everything above 10 is usually not necessary
     * @param int $vector - if you want to initialize a chain to reproduce a chiffre
     * 
     */
    public function __construct(int $entropy = 6, int $vector = null)
    {
        // lock to 16 for now
        if ($entropy > 16) {
            throw new \Exception('This is way to much entropy and will not provide any further security.');
        }
        
        $this->chiffreChain = $vector;
        // var_dump($this->chiffreChain);
        if (null === $vector) {
            $this->_buildChiffreChain($entropy);
        }
    }

    /**
     * Return multiple chiffre functions which have to
     * be used in the exact order to get a matching string.
     * 
     * @return int
     */
    public function getChain(): int
    {
        return intval($this->chiffreChain);
    }

    /**
     * Return rot13
     */
    public function r13(string $str): string
    {
        return str_rot13($str);
    }

    /**
     * Return string reversed
     */
    public function rev(string $str): string
    {
        return strrev($str);
    }

    /**
     * Return string md5
     */
    public function m5(string $str): string
    {
        return md5($str);
    }

    /**
     * Return base64
     */
    public function b64(string $str): string
    {
        return base64_encode($str);
    }

    /**
     * Return fuzzlebox (derived from rot47)
     */
    public function fuzzlebox(string $str): string
    {
        return strtr($str, 
            strrev('"#()*+,-./0123456789=@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_abcdefghijklmnopqrstuvwxyz'), 
            str_rot13('PQRSTUVWXYZ[\]^_abcdefghijklmnopqrstuvwxyz"#()*+,-./0123456789=@ABCDEFGHIJKLMNO')
        );
    }

    /**
     * Run chiffre chain
     * 
     * @param string $secret - the secret to run the chain on
     * @param int $direction - run forward (1) or backward (0)
     * 
     * @return string - the result secret
     */
    public function run(string $secret, int $direction = Tentacles::DIR_FORWARD): string
    {
        $chain = array_map(
            'intval', 
            str_split(
                strval(
                    $this->chiffreChain
                )
            )
        );
        
        // run chain backward
        if (Tentacles::DIR_BACKWARD === $direction) {
            $chain = array_reverse($chain);  
        }

        // do run - return the chiffre
        $chiffre = $this->_doRun($chain, $secret);
        return $this->b64($chiffre);
    }

    /**
     * Run the chain on the secret
     * 
     * @param array $chiffres - the chiffre chain in order
     * @param string $secret - the secret
     * 
     * @return string - the final chiffre and the vector used to generate it
     */
    private function _doRun(array $chiffres, string $secret): string
    {
        $s = $secret;
        foreach ($chiffres as $chiffre) {
            $func = $this->chiffres[$chiffre];
            if (true === is_callable($func)) {
                $s = $this->$func($s);
            }
        }

        return $s;
    }

    /**
     * Build the chiffre chain for the current request
     * 
     * @param int $entropy - the amount of functions run on the secret
     * 
     * @return void
     */
    private function _buildChiffreChain(int $entropy): void
    {
        // safeguard
        $i = 0;
        $max = count($this->chiffres);
        while (strlen($this->chiffreChain) < $entropy) {
            $rnd = random_int(1, $max-1);
            $this->chiffreChain .= $rnd;
            $i++;
            if ($i > 64) {
                break;
            }
        }
    }
}
