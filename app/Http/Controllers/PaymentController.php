<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use UnexpectedValueException;
use Illuminate\Support\Facades\Log;

use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Stripe\PaymentIntent;
use Stripe\SetupIntent;
use Stripe\PaymentMethod;
use Stripe\Customer;


class PaymentController extends Controller
{

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

        Log::info("STRIPE WEBHOOK EVENT :: {$event->type} :: DATA :: ",  $event->toArray());
        
        /* 
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;
                Log::info('PaymentIntent was successful!');
                break;
            case 'payment_intent.failed':
                $paymentIntent = $event->data->object;
                Log::info('PaymentIntent failed.');
                break;
            default:
                Log::info('Received unknown event type '.$event->type);
        }
        */
        
        return response()->json(['status' => 'success']);
    }



    // AUTHORIZE CHARGE LATER
    public function paymentForm()
    {
        Stripe::setApiKey(config('app.stripe_secret_key'));

        // Create a SetupIntent to collect card information
        $intent = SetupIntent::create();

        return view('stripe_charge_later', [
            'clientSecret' => $intent->client_secret,
        ]);
    }

    public function processPayment(Request $request)
    {
        Stripe::setApiKey(config('app.stripe_secret_key'));

        // Create or retrieve the customer
        $customer = Customer::create([
            'email' => $request->email,
            'name' => $request->name,
        ]);

        Log::info("processPayment :: STRIPE CUSTOMER ID ::", $customer);

        // Retrieve the payment method
        $paymentMethod = PaymentMethod::retrieve($request->payment_method);
        
        Log::info("processPayment :: STRIPE PAYMENT METHOD ::", $request);
        
        // Attach the payment method to the customer
        $paymentMethod->attach([
            'customer' => $customer->id,
        ]);

        Log::info('processPayment :: STRIPE PAYMENT METHOD ::', $paymentMethod);

        // Optionally, set this as the default payment method for the customer
        Customer::update($customer->id, [
            'invoice_settings' => [
                'default_payment_method' => $paymentMethod->id,
            ]
        ]);
        
        Log::info("STRIPE CUSTOMER ID ::", $customer->id);
        
        return response()->json(['success' => true, 'message' => 'Card authorized successfully with custom id :: ' . $customer->id]);
    }

    // In your controller where you process the payment for the product:
    public function chargeCustomer($customerId, $amount)
    {
        Stripe::setApiKey(config('app.stripe_secret_key'));

        $paymentIntent = PaymentIntent::create([
            'amount' => $amount,
            'currency' => 'usd',
            'customer' => $customerId,
            'payment_method' => 'pm_card_visa', // Use the ID of the saved payment method
            'off_session' => true,
            'confirm' => true,
        ]);

        dd($paymentIntent);
    }
}
