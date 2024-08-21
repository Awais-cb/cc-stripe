@extends('layouts.master')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Confirm Your Payment</div>

                <div class="card-body">
                    <div id="payment-message" class="alert" style="display:none;"></div>

                    <form id="payment-confirm-form" action="{{ route('stripe.payment.confirm') }}" method="POST">
                        @csrf
                        <input type="hidden" id="clientSecret" value="{{ $clientSecret }}">

                        <div class="form-group">
                            <label for="amount">Amount</label>
                            <input type="text" id="amount" class="form-control" value="{{ number_format($amount / 100, 2) }}" readonly>
                        </div>

                        <div class="form-group">
                            <label for="currency">Currency</label>
                            <input type="text" id="currency" class="form-control" value="{{ strtoupper($currency) }}" readonly>
                        </div>

                        <div class="form-group">
                            <label for="order_id">Order ID</label>
                            <input type="text" id="order_id" class="form-control" value="{{ $order_id }}" readonly>
                        </div>

                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" id="name" name="name" class="form-control" placeholder="Enter your name" required>
                        </div>

                        <div class="form-group">
                            <label for="card-element">Credit or debit card</label>
                            <div id="card-element" class="form-control">
                                <!-- Stripe Element will be inserted here -->
                            </div>
                            <div id="card-errors" role="alert"></div>
                        </div>

                        <button id="submit" class="btn btn-primary">Pay</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Stripe.js -->
<script src="https://js.stripe.com/v3/"></script>
<script>
    var stripe = Stripe('{{ config('app.stripe_public_key') }}');
    var elements = stripe.elements();
    var card = elements.create('card');
    card.mount('#card-element');

    card.on('change', function(event) {
        var displayError = document.getElementById('card-errors');
        if (event.error) {
            displayError.textContent = event.error.message;
        } else {
            displayError.textContent = '';
        }
    });

    var form = document.getElementById('payment-confirm-form');
    form.addEventListener('submit', function(event) {
        event.preventDefault();

        // Disable the form submit button to prevent multiple clicks
        document.getElementById('submit').disabled = true;

        var name = document.getElementById('name').value;

        stripe.confirmCardPayment(document.getElementById('clientSecret').value, {
            payment_method: {
                card: card,
                billing_details: {
                    name: name
                }
            }
        }).then(function(result) {
            document.getElementById('submit').disabled = false;
            var paymentMessage = document.getElementById('payment-message');
            console.log(result);
            if (result.error) {
                // Show error message
                paymentMessage.textContent = result.error.message;
                paymentMessage.className = "alert alert-danger";
                paymentMessage.style.display = 'block';

                // Re-enable the form submit button
                // document.getElementById('submit').disabled = false;
            } else {
                if (result.paymentIntent.status === 'succeeded') {
                    // Show success message
                    paymentMessage.textContent = 'Your payment processing was successful!';
                    paymentMessage.className = "alert alert-success";
                    paymentMessage.style.display = 'block';

                    // Optionally, you can redirect to another page here
                    // window.location.href = '/success-page';
                }
            }
        });
    });
</script>
@endsection
