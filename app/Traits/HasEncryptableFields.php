<?php

namespace App\Traits;

trait HasEncryptableFields
{

    private $order_private_key = null;
    private $order_index_key = null;


    protected function getNonce(): string
    {
        return random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
    }

    protected function getBlindIndex(string $string): string
	{

        return bin2hex(
            sodium_crypto_pwhash(
                32,
                $string,
                $this->getIndexKey(),
                SODIUM_CRYPTO_PWHASH_OPSLIMIT_MODERATE,
                SODIUM_CRYPTO_PWHASH_MEMLIMIT_MODERATE
            )
        );
	}

    protected function getPrivateKey(): string
    {
        if (!$this->order_private_key) {
            if (!$private_key = config('app.order_private_key')) {
                throw new \Exception('No private key found in config/app.php');
            }
            $this->order_private_key = base64_decode($private_key);
        }

        return $this->order_private_key;
    }

    protected function getIndexKey(): string
    {
        if (!$this->order_index_key) {
            if (!$index_key = config('app.order_index_key')) {
                throw new \Exception('No index key found in config/app.php');
            }
            $this->order_index_key = base64_decode($index_key);
        }

        return $this->order_index_key;
    }

    protected function decrypt($string): string {
        $decoded = base64_decode($string);
        $key = $this->getPrivateKey();
        $nonce = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
        $ciphertext = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES , null, '8bit');

        try  {
          $plaintext = sodium_crypto_secretbox_open($ciphertext, $nonce, $key);
          return $plaintext;
        }
        catch (Error $ex) {
          return "Cannot decrypt data.";
        }
        catch (Exception $ex) {
          return "Cannot decrypt data.";
        }
      }

}
