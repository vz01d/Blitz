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

use ParagonIE\Halite\KeyFactory;
use ParagonIE\HiddenString\HiddenString;
use ParagonIE\Halite\Symmetric\Crypto as Symmetric;

/**
 * Class HexagonKeychain - an wrapper for ParagonIE\Halite
 * use this class to initialize HexagonKeychain and get keys 
 * for EZRHA (Encrypted 2-Requests Handshake Authorization)
 * 
 * TODO: create abstraction layer for this class
 * 
 * @category Multisite
 * @package  Remote
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 */
class HexagonKeychain
{
    /**
     * nxKey - the key for the next request
     */
    private $nxKey   = null;

    /**
     * hsKey - the key to sign the current message
     */
    private $hsKey   = null;

    /**
     * authKey - the key to generate the hmac_hkdf signature
     */
    private $authKey = null;

    /**
     * nxAuthKey - the key to generate the hmac_hkdf signature for 2nd request
     */
    private $nxAuthKey = null;

    /**
     * nxSalt - the salt used to generate the key for the next request
     */
    private $nxSalt  = null;

    /**
     * Empty
     */
    protected function __construct(){}

    /**
     * Generate a new HexagonKeychain object
     * 
     * @param string $chiffre - the chiffre used to generate keys
     * @param string $salt - the salt to use
     * 
     * @return void - store the keys required to create the crypted message
     * [
     *    nxKey => the key for the next request
     *    hsKey => the key to sign the current message
     *    authKey => the hmac_hkdf used to sign the message
     *    nxSalt => the salt used to generate the key for the next request
     * ] in keys
     */
    protected function generate(string $chiffre, string $salt): void
    {
        // generate key for the next request
        $pass = new HiddenString($chiffre);
        $nxSalt = random_bytes(16);
        $nxKey = KeyFactory::deriveEncryptionKey($pass, $nxSalt);
        
        // generate handshake key using salt
        $hsPass = new HiddenString(strrev(md5($salt)));
        $hsKey = KeyFactory::deriveEncryptionKey($hsPass, $salt);

        // generate auth key
        $authPass = new HiddenString($salt);
        $authKey = KeyFactory::deriveAuthenticationKey($authPass, $salt);

        // generate nx auth key
        $nxAuthPass = new HiddenString($salt);
        $nxAuthKey = KeyFactory::deriveAuthenticationKey($nxAuthPass, $salt);

        $this->nxKey   = $nxKey;
        $this->hsKey   = $hsKey;
        $this->authKey = $authKey;
        $this->nxAuthKey = $nxAuthKey;
        $this->nxSalt  = bin2hex($nxSalt);
    }

    /**
     * Return NxSalt on current object
     * 
     * @return string
     */
    protected function getNxSalt(): string
    {
        return null === $this->nxSalt ? '' : $this->nxSalt;
    }

    /**
     * Encrypt a message
     * 
     * @param string $message - the message to encrypt
     * @param \ParagonIE\Halite\Symmetric\EncryptionKey $key - the key to use
     * @param string $salt - use salt to generate a new key instead, this will overwrite
     * the key provided so make sure to keep salt to null if you intend to provide your own
     * key
     * 
     * @return string - the encrypted message
     */
    protected function ciphertext(
        string $message,
        \ParagonIE\Halite\Symmetric\EncryptionKey $key = null,
        string $salt = null
    ): string {
        if (null !== $key) {
            return Symmetric::encrypt(new HiddenString($message), $key);
        }

        if (null != $salt) {
            $pass = new HiddenString(md5($salt));
            $key = KeyFactory::deriveEncryptionKey($pass, $salt);
            return Symmetric::encrypt(new HiddenString($message), $key);
        }

        return Symmetric::encrypt(new HiddenString($message), $this->nxKey);
    }

    /**
     * Sign a message
     * 
     * @param string $ciphertext - the encrypted message
     * @param \ParagonIE\Halite\Symmetric\AuthenticationKey $key - the
     * key to sign the message
     * 
     * @return string - the signed message
     */
    protected function signMessage(
        string $ciphertext,
        \ParagonIE\Halite\Symmetric\AuthenticationKey $key = null
    ): string {
        return Symmetric::authenticate(
            $ciphertext, 
            null === $key ? $this->authKey : $key
        );
    }

    /**
     * Sign the 2nd request
     * 
     * @param string $ciphertext - the encrypted message to sign
     * 
     * @return string - the signed message hmac_hkdf
     */
    protected function sign2ndRequest(string $ciphertext): string
    {
        return $this->signMessage(
            $ciphertext,
            $this->nxAuthKey
        );
    }
    
    /**
     * Verify a message
     * 
     * @param binary $salt - the salt used to generate the key
     * @param string $message - the message to verify
     * @param string $mac - the hmac_hdkf sign
     * 
     * @return bool - wether the message is valid or not
     */
    protected static function verifyMessage($salt, string $message, string $mac): string
    {
        $pass = new HiddenString($salt);
        $key = KeyFactory::deriveAuthenticationKey($pass, $salt);
        return Symmetric::verify($message, $key, $mac);
    }
    
    /**
     * Decrypt a handshake message
     * 
     * @param string $salt - the salt used to generate the key
     * @param string $message - the message to verify
     * 
     * @return string - the plaintext message
     */
    protected static function decryptHandshakeMessage(string $salt, string $message): string
    {
        $pass = new HiddenString(strrev(md5($salt)));
        $key = KeyFactory::deriveEncryptionKey($pass, $salt);
        return Symmetric::decrypt($message, $key);
    }

    /**
     * Decrypt a message by providing encryption key
     * 
     * @param string $message - the message to decrypt
     * @param \ParagonIE\Halite\Symmetric\EncryptionKey $key - the key to use
     */
    protected static function decryptMessage(string $message, \ParagonIE\Halite\Symmetric\EncryptionKey $key)
    {
        return Symmetric::decrypt($message, $key);
    }

    /**
     * Get data package for handshake
     * 
     * @param int $chiffreVector - the vector used to generate the chiffre
     * @param string $nxRequest - the name of the next endpoint beeing called next
     * to include it into the handshake request. This allows for features like
     * disabled endpoints by default which will only be active remotely
     * when a handshake request happened before.
     * 
     * @return array - an array containing hmac_hkdf, ciphertext, nxSalt
     */
    protected function getHandshakePackage(int $chiffreVector, string $nxRequest): array
    {
        $message = new HiddenString($this->nxSalt.'::'.md5(strrev($nxRequest)).'::'.$chiffreVector);
        $ciphertext = Symmetric::encrypt($message, $this->hsKey);

        $hmac = $this->signMessage($ciphertext);

        return [
            $hmac,
            $ciphertext
        ];
    }

    /**
     * Get an encryption key by chiffre and the salt used to generate it
     * 
     * @param string $chiffre - the chiffre used to generate the key
     * @param string $salt - the salt used to generate the key
     * 
     * @return \ParagonIE\Halite\Symmetric\EncryptionKey - the key
     */
    protected function getEncryptionKeyByChiffre(string $chiffre, string $salt): \ParagonIE\Halite\Symmetric\EncryptionKey
    {
        $pass = new HiddenString($chiffre);
        return KeyFactory::deriveEncryptionKey($pass, hex2bin($salt));
    }
}
