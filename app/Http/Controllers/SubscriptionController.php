<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;


class SubscriptionController extends Controller
{
    public function create(Request $request)
    {
        $user = $request->user();

        $paymentMethod = $request->input('payment_method');

        // Attach a payment method to the user
        $user->createOrGetStripeCustomer();
        $user->addPaymentMethod($paymentMethod);

        // Create a new subscription
        $user->newSubscription('default', 'plan-id')
            ->create($paymentMethod);

        return redirect('/home')->with('success', 'Subscription created successfully!');
    }
}
