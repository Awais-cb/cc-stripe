<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Checkout\Session;

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


    /**
     * @return RedirectResponse
     * @throws ApiErrorException
     */
    public function stripeHosted(Request $request)
    {
        Stripe::setApiKey(config('app.stripe_secret_key'));

        $session = Session::create([
            'line_items'  => [
                [
                    'price_data' => [
                        'currency'     => $request->input('currency'),
                        'product_data' => [
                            'name' => 'Therapy Session',
                        ],
                        'unit_amount'  => $request->input('amount'),
                    ],
                    'quantity'   => 1,
                ],
            ],
            'mode'        => 'payment',
            'success_url' => route('stripe.payment.confirm'),
            'cancel_url'  => route('stripe.payment.failed'),
        ]);

        return redirect()->away($session->url);
    }

    public function confirmPayment(Request $request)
    {
        // Here you can handle post-payment logic, like saving order details or updating records
        return redirect()->route('stripe.payment.form')->with('success', 'Payment successful!');
    }
    
    public function failedPayment(Request $request)
    {
        // Here you can handle post-payment logic, like saving order details or updating records
        return redirect()->route('stripe.payment.form')->with('error', 'Payment Failed!');
    }

}
