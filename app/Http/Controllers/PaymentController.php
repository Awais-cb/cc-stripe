<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class PaymentController extends Controller
{
    public function stripeIntent()
    {
        return view('stripe-payment-intent');
    }

    public function submitIntent(Request $request)
    {
        // Set your secret key. Remember to switch to your live secret key in production!
        Stripe::setApiKey(config('app.stripe_secret_key'));

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $request->input('amount'),
                'currency' => $request->input('currency'),
                'metadata' => ['order_id' => $request->input('order_id')],
            ]);

            // Here you would typically redirect to a success page or back with a success message
            return redirect()->back()->with('success', 'Payment Intent created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error creating payment intent: ' . $e->getMessage());
        }
    }


    public function showPaymentForm()
    {
        return view('stripe-flow.stripe_payment');
    }

    public function initiatePayment(Request $request)
    {
        Stripe::setApiKey(config('app.stripe_secret_key'));

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $request->input('amount'),
                'currency' => $request->input('currency'),
                'metadata' => ['order_id' => $request->input('order_id')],
            ]);

            // Pass the client secret to the next view for confirmation
            return view('stripe-flow.stripe_payment_confirm', [
                'clientSecret' => $paymentIntent->client_secret,
                'amount' => $request->input('amount'),
                'currency' => $request->input('currency'),
                'order_id' => $request->input('order_id'),
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error creating payment intent: ' . $e->getMessage());
        }
    }

    public function confirmPayment(Request $request)
    {
        // Here you can handle post-payment logic, like saving order details or updating records
        return redirect()->route('stripe.payment.form')->with('success', 'Payment successful!');
    }

}
