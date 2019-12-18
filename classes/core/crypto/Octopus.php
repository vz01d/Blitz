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
 * Class Octopus - generate a new Keychain for the RemoteApi to consume
 *  
 * TODO: extract from project to standalone composer package
 * 
 * @category Multisite
 * @package  Remote
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 */
class Octopus
{
    /**
     * string secret
     */
    private $secret = null;

    /**
     * int entropy - the entropy level used for chiffre
     */
    private $entropy = null;

    /**
     * Ref to Hexagon Object
     */
    private $hexagon = null;

    /**
     * chiffreVector
     */
    private $chiffreVector = null;

    /**
     * saltVector
     */
    private $saltVector = null;

    /**
     * Create a new Octopus *yay*
     * 
     * @param string $secret - the secret only you and the Octopus know
     * @param bool $generateChain - generate a new keychain. this should be done for handshake
     * requests, you can build them manually if desired
     * @param int $entropy - how often the tentacles should swing
     */
    protected function __construct(string $secret, bool $generateChain = false, int $entropy = 9)
    {
        $this->secret = $secret;
        $this->entropy = $entropy;

        if (true === $generateChain) {
            $this->_generateKeychain();
        }
    }

    /**
     * Generate a new keychain for EZRHA
     * 
     * @param string $secret - the secret used to access contents
     * @param int $entropy - the level of entropy for the chiffre Chain
     * default: 9 -> 9 chiffre functions have to be run in order
     * 
     * @return void
     */
    private function _generateKeychain(): void
    {
        Tentacles::generateChiffreChain($this->entropy);
        [$chiffre, $this->chiffreVector] = Tentacles::runChiffreChain($this->secret, Tentacles::DIR_BACKWARD);
        [$salt, $this->saltVector] = Tentacles::generateSalt($this->secret);
        $this->hexagon = new Hexagon($chiffre, $salt);
    }

    /**
     * Get data for initial Handshake
     * 
     * @param string $nxRequest - the next endpoint that will be called after handshake
     * 
     * @return array - hmac_hkdf && ciphertext containing nxSalt, chiffreVector
     * and saltVector used to generate Hexagon salt
     */
    protected function getHandshakeData(string $nxRequest): array
    {
        [$mac, $msg] = $this->hexagon->getHSData($this->chiffreVector, $nxRequest);
        $saltVector = $this->saltVector;
        
        return [
            $mac,
            $msg,
            $saltVector
        ];
    }

    /**
     * Verify an incoming message using saltVector and Symmetric::verify
     * 
     * @param string $msg - the message to verify
     * @param string $mac - the hmac_hkdf string
     * @param string $saltVector - the vector to use to generate the salt
     * 
     * @return bool - wether the message is valid or not
     */
    protected function verifyMessageBySaltVector(string $msg, string $mac, string $saltVector): bool
    {
        $salt = Tentacles::getSaltByVector($this->secret, $saltVector);
        return Hexagon::verify($salt, $msg, $mac);
    }

    /**
     * Verify an incoming message using salt and Symmetric::verify
     * 
     * @param string $msg - the message to verify
     * @param string $mac - the hmac_hkdf string
     * @param $salt - the salt
     * 
     * @return bool - wether the message is valid or not
     */
    protected function verifyMessageBySalt(string $msg, string $mac, $salt): bool
    {
        return Hexagon::verify($salt, $msg, $mac);
    }

    /**
     * Decrypt a ciphertext message
     * 
     * @param string $msg - the message to decrypt
     * @param string $saltVector - the vector used to generate the salt
     * 
     * @return string - the plaintext message or empty string if invalid
     * TODO: add pseudo generated message here and do not tell if cipher, salt
     * or anything else went wrong
     */
    protected function decryptHandshake(string $msg, string $saltVector): string
    {
        $salt = Tentacles::getSaltByVector($this->secret, $saltVector);
        return Hexagon::decryptHSMessage($salt, $msg);
    }

    /**
     * Generate Ciphertext using either provided or generated nxKey
     * 
     * @param string $message - the message to encrypt
     * @param \ParagonIE\Halite\Symmetric\EncryptionKey $key - the key
     * you want to use for your ciphertext
     */
    protected function generateCiphertext(
        string $message, 
        \ParagonIE\Halite\Symmetric\EncryptionKey $key = null,
        bool $generateSalt = false
    ) {
        if (null === $key && true === $generateSalt) {
            [$salt, $saltVector] = Tentacles::generateSalt($this->secret);

            return [
                $this->hexagon->generateCiphertext($message, $key, $salt),
                $saltVector
            ];
        } else {
            return $this->hexagon->generateCiphertext($message, $key);
        }
    }

    /**
     * Decrypt a message by it's salt and chiffreVector used to generate
     * the initial key which has been sent over during handshake request
     * 
     * @param string $message - the message to decrypt
     * @param string $salt - the salt used to generate the key
     * @param string $chiffreVector - the vector used to generate the chiffre
     */
    protected function decryptMessage(string $message, string $salt, string $chiffreVector)
    {
        $key = $this->_getNxKey($chiffreVector, $salt);
        if (isset($key)) {
            return Hexagon::decrypt($message, $key);
        }

        return false;
    }

    /**
     * Reverse string then md5 hash it
     * 
     * @param string $str - the string
     * 
     * @return string - the hash
     */
    public static function rhashRoute(string $str): string
    {
        return md5(strrev($str));
    }

    /**
     * Return nxKey to sign the next request containing the actual contents
     * 
     * @param int $chiffreVector - the chiffreVector used to generate the key
     * @param string $nxSalt - the salt used to generate the key
     * 
     * @return \ParagonIE\Halite\Symmetric\EncryptionKey
     */
    private function _getNxKey(int $chiffreVector, string $nxSalt): \ParagonIE\Halite\Symmetric\EncryptionKey
    {
        Tentacles::generateChiffreChain($this->entropy, $chiffreVector);
        [$chiffre, $this->chiffreVector] = Tentacles::runChiffreChain($this->secret, Tentacles::DIR_BACKWARD);
        $this->hexagon = new Hexagon($chiffre, hex2bin($nxSalt), false);
        return $this->hexagon->regenerateNxKey($chiffre, $nxSalt);
    }

    /**
     * Sign the message provided using authKey from keychain
     * 
     * @param string $ciphertext - the ciphertext message to sign
     * @param \ParagonIE\Halite\Symmetric\EncryptionKey $key - the
     * key to sign the message
     * 
     * @return string - the signed message
     */
    protected function signMessage(string $ciphertext): string
    {
        return $this->hexagon->sign($ciphertext);
    }

    /**
     * Sign the 2nd request
     * 
     * @param string $ciphertext - the ciphertext message to sign
     * 
     * @return string - the signed request message
     */
    protected function sign2ndRequest(string $ciphertext): string
    {
        return $this->hexagon->signRequest($ciphertext);
    }
}
