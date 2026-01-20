<?php

declare(strict_types=1);

namespace App\Utils;

class Encryption
{
    private const CIPHER = 'aes-256-gcm';
    private const TAG_LENGTH = 16;

    private static function env(string $key, ?string $default = null): ?string
    {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }

    private static function getKey(): string
    {
        $key = self::env('ENCRYPTION_KEY', '');
        if (strlen($key) !== 64) {
            throw new \RuntimeException('ENCRYPTION_KEY must be a 64-character hex string (32 bytes)');
        }
        return hex2bin($key);
    }

    public static function encrypt(?string $plaintext): ?string
    {
        if ($plaintext === null || $plaintext === '') {
            return null;
        }

        $key = self::getKey();
        $iv = random_bytes(openssl_cipher_iv_length(self::CIPHER));
        $tag = '';

        $ciphertext = openssl_encrypt(
            $plaintext,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            self::TAG_LENGTH
        );

        if ($ciphertext === false) {
            throw new \RuntimeException('Encryption failed');
        }

        // Format: base64(iv + tag + ciphertext)
        return base64_encode($iv . $tag . $ciphertext);
    }

    public static function decrypt(?string $encrypted): ?string
    {
        if ($encrypted === null || $encrypted === '') {
            return null;
        }

        $key = self::getKey();
        $data = base64_decode($encrypted, true);

        if ($data === false) {
            throw new \RuntimeException('Invalid encrypted data format');
        }

        $ivLength = openssl_cipher_iv_length(self::CIPHER);
        $iv = substr($data, 0, $ivLength);
        $tag = substr($data, $ivLength, self::TAG_LENGTH);
        $ciphertext = substr($data, $ivLength + self::TAG_LENGTH);

        $plaintext = openssl_decrypt(
            $ciphertext,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($plaintext === false) {
            throw new \RuntimeException('Decryption failed - data may be corrupted or tampered with');
        }

        return $plaintext;
    }

    public static function generateKey(): string
    {
        return bin2hex(random_bytes(32));
    }
}
