<?php

namespace Tests\Unit;

use App\Models\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * A basic unit test example.
     */
    public function test_that_orders_are_encrypted_and_can_be_queried(): void
    {
        $email = 'john@doe.com';
        $remote_user = 'Amy';

        $order = Order::create(['email' => $email, 'remote_user' => $remote_user]);
        // Assert that the order craetion has created hash fields.
        $this->assertNotNull($order->email_hash);
        $this->assertNotNull($order->remote_user_hash);
        $order->save();

        // Assert the fields we want to be encrypted has been stored encrypted.
        $this->assertNotEquals($order->getRawOriginal('email'), $email);
        $this->assertNotEquals($order->getRawOriginal('remote_user'), $remote_user);

        // Assert that the acceessors decrypt the fields like we want them to.
        $this->assertEquals($order->email, $email);
        $this->assertEquals($order->remote_user, $remote_user);


        // Assert that we can lookup order by the email_hash.
        $lookup_order_by_email = new Order;
        $lookup_order_by_email->email = $email;
        $email_hash = $lookup_order_by_email->email_hash;

        $found_email_orders = Order::where('email_hash', $email_hash)->get();
        $this->assertEquals($found_email_orders[0]->email, $email);

        // Assert that we can lookup order by the remote_user_hash.
        $lookup_order_by_remote_user = new Order;
        $lookup_order_by_remote_user->remote_user = $remote_user;
        $remote_user_hash = $lookup_order_by_remote_user->remote_user_hash;

        $found_remote_user_orders = Order::where('remote_user_hash', $remote_user_hash)->get();
        $this->assertEquals($found_remote_user_orders[0]->remote_user, $remote_user);
    }
}
