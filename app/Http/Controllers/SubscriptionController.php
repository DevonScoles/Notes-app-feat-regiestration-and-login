<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index()
    {
        return to_route('note.index');
    }
    public function checkout()
    {
        \Stripe\Stripe::setApiKey(config('stripe.sk'));

        $session = \Stripe\Checkout\Session::create([
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'Notes Unlimited',
                        ],
                        'recurring' => [
                            'interval' => 'month', //or 'year' for yearly subscriptions
                        ],
                        'unit_amount' => 500
                    ],
                    'quantity' => 1,
                ],
            ],
            'mode' => 'subscription',
            'success_url' => route('success'),
            'cancel_url' => route('index'),
        ]);

        return redirect()->away($session->url);
    }

    public function success()
    {
        $user = auth()->user();
        $user->premium = true;
        $user->save();
        return to_route('note.index')->with('message', 'Thanks for subscribing!');
    }

}
