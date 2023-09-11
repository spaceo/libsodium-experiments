<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateOrderKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-order-keys';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates order keys for hashing and encryption';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $private_key = random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
        $index_key = random_bytes(SODIUM_CRYPTO_PWHASH_SALTBYTES);


        $this->info('Private key: ' . base64_encode($private_key));
        $this->info('Index key: ' . base64_encode($index_key));
    }
}
