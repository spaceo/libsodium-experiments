<?php

namespace App\Models;

use App\Traits\HasEncryptableFields;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    use HasEncryptableFields;
/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['email', 'remote_user'];


    public function setEmailAttribute(string $value): void
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

    /**
     * Get the user's first name.
     */
    protected function email(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => $this->decrypt($value),
        );
    }

    /**
     * Get the remote user id.
     */
    protected function remoteUser(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => $this->decrypt($value),
        );
    }

}
