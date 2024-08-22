<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Checkout\Session;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use UnexpectedValueException;
use Illuminate\Support\Facades\Log;

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


    public function handleWebhook(Request $request)
    {
        $endpointSecret = config('app.stripe_webhook_secret');

        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent(
                $payload, $sigHeader, $endpointSecret
            );
        } catch (SignatureVerificationException $e) {
            Log::error('Webhook signature verification failed: '.$e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        } catch (UnexpectedValueException $e) {
            Log::error('Invalid payload: '.$e->getMessage());
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        Log::info('Webhook received');
        Log::debug((array) $event);

        // Handle the event based on its type
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object; // Contains a StripePaymentIntent
                // Handle successful payment here
                Log::info('PaymentIntent was successful!');
                break;
            case 'payment_intent.failed':
                $paymentIntent = $event->data->object; // Contains a StripePaymentIntent
                // Handle failed payment here
                Log::info('PaymentIntent failed.');
                break;
            // Add more cases to handle other event types
            default:
                Log::info('Received unknown event type '.$event->type);
        }

        return response()->json(['status' => 'success']);
    }


}
