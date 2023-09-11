<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['email', 'remote_user'];

    private $order_private_key = null;
    private $order_index_key = null;

    public function setEmailAttribute($value)
    {
        $nonce = $this->getNonce();
        // encrypt data
        $email = sodium_crypto_secretbox($value, $nonce, $this->getPrivateKey());
        // encode data for saving into db
        $email = base64_encode($nonce . $email);

        $this->attributes['email'] = $email;
        $this->attributes['email_hash'] = $this->getBlindIndex($value);
    }
    public function setRemoteUserAttribute(string $value): void
    {
        $nonce = $this->getNonce();
        // encrypt data
        $remote_user = sodium_crypto_secretbox($value, $nonce, $this->getPrivateKey());
        // encode data for saving into db
        $remote_user = base64_encode($nonce . $remote_user);

        $this->attributes['remote_user'] = $remote_user;
        $this->attributes['remote_user_hash'] = $this->getBlindIndex($value);
    }

    protected function getNonce(): string
    {
        return random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
    }

    /**
     * Get the user's first name.
     */
    protected function email(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => $this->decrypt($value),
        );
    }

    protected function remoteUser(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => $this->decrypt($value),
        );
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

    protected function getPrivateKey()
    {
        if (!$this->order_private_key) {
            if (!$private_key = config('app.order_private_key')) {
                throw new \Exception('No private key found in config/app.php');
            }
            $this->order_private_key = base64_decode($private_key);
        }

        return $this->order_private_key;
    }

    protected function getIndexKey()
    {
        if (!$this->order_index_key) {
            if (!$index_key = config('app.order_index_key')) {
                throw new \Exception('No index key found in config/app.php');
            }
            $this->order_index_key = base64_decode($index_key);
        }

        return $this->order_index_key;
    }
    protected function decrypt($string) {
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
