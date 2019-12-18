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
 * Class Octopus - an interface to ParagonIE\Halite
 * use this class to initialize HexagonKeychain and get keys 
 * for EZRHA (Encrypted 2-Requests Handshake Authorization)
 * 
 * TODO: extract from project to standalone composer package
 *  
 * @category Multisite
 * @package  Remote
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 */
final class Hexagon extends HexagonKeychain
{
    /**
     * Generate a new keychain
     * 
     * @param string $chiffre - the chiffre to use for initialization of 
     * the HexagonKeychain
     * @param string $salt - the salt
     * @param bool $generateKeychain - generate a new keychain
     */
    public final function __construct(string $chiffre, string $salt, bool $generateKeychain = true)
    {
        if (true === $generateKeychain) {
            new HexagonKeychain;
            $this->generate($chiffre, $salt);
        }
    }

    /**
     * Return NxSalt on current object
     * 
     * @return string
     */
    public function nxSalt(): string
    {
        return $this->getNxSalt();
    }

    /**
     * Sign a message
     * 
     * @param string $ciphertext - the encrypted message
     * @param \ParagonIE\Halite\Symmetric\EncryptionKey $key = the 
     * key to sign the Message
     * 
     * @return string - the signed message
     */
    public function sign(
        \ParagonIE\Halite\Symmetric\Crypto $ciphertext,
        \ParagonIE\Halite\Symmetric\EncryptionKey $key = null
    ): string {
        return $this->signMessage($ciphertext, $key);
    }

    /**
     * Sign the 2nd request
     * 
     * @param string $ciphertext - the encrypted message to sign
     * 
     * @return string - the signed message hmac_hkdf
     */
    public function signRequest(string $ciphertext): string
    {
        return $this->sign2ndRequest($ciphertext);
    }

    /**
     * Get data for handshake
     * 
     * @param int $chiffreVector - the vector used to generate the chiffre
     * @param string $nxRequest - the name of the next endpoint beeing called next
     * 
     * @return array - an array containing hmac_hkdf && ciphertext
     */
    public function getHSData(int $chiffreVector, string $nxRequest): array
    {
        return $this->getHandshakePackage($chiffreVector, $nxRequest);
    }
    
    /**
     * Verify an incoming Message
     * 
     * @param $salt - the salt used to generate the key
     * @param string $message - the message to verify
     * @param string $mac - the hmac_hdkf sign
     * 
     * @return bool - wether the message is valid and safe to decrypt or not
     */
    public static function verify($salt, string $message, string $mac): bool
    {
        // TODO: verify input (!)
        return self::verifyMessage($salt, $message, $mac);
    }
    
    /**
     * Decrypt an incoming Message
     * 
     * @param string $salt - the salt used to generate the key
     * @param string $message - the message to decrypt (ciphertext)
     * 
     * @return string - the plaintext message
     */
    public static function decryptHSMessage(string $salt, string $message): string
    {
        // TODO: verify input (!)
        return self::decryptHandshakeMessage($salt, $message);
    }

    /**
     * Decrypt a message by providing encryption key
     * 
     * @param string $message - the message to decrypt
     * @param \ParagonIE\Halite\Symmetric\EncryptionKey $key - the key to use
     */
    public static function decrypt(string $message, \ParagonIE\Halite\Symmetric\EncryptionKey $key)
    {
        return self::decryptMessage($message, $key);
    }

    /**
     * Regenerate next request key
     * 
     * @param string $chiffre - the chiffre used to generate the key
     * @param string $nxSalt - the salt used to generate the key
     * 
     * @return \ParagonIE\Halite\Symmetric\EncryptionKey - the key
     */
    public function regenerateNxKey(string $chiffre, string $nxSalt): \ParagonIE\Halite\Symmetric\EncryptionKey
    {
        // TODO: verify input (!)
        return $this->getEncryptionKeyByChiffre($chiffre, $nxSalt);
    }

    /**
     * Generate Ciphertext using either provided or generated nxKey
     * 
     * @param string $message - the message to encrypt
     * @param \ParagonIE\Halite\Symmetric\EncryptionKey $key - the key
     * you want to use for your ciphertext
     * 
     * @return string - the encrypted message
     */
    public function generateCiphertext(
        string $message, 
        \ParagonIE\Halite\Symmetric\EncryptionKey $key = null,
        string $salt = null
    ): string {
        // TODO: verify input (!)
        return $this->ciphertext($message, $key, $salt);
    }
}
