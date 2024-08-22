@extends('layouts.master')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="">
            <div class="card-header">Stripe Payment Form</div>
            <h2>Payment details</h2>
            <div class="card-body">
                <div id="payment-message" class="alert" style="display:none;"></div>
                <form id="payment-form">
                    @csrf
                    <input type="hidden" id="clientSecret" value="{{ $clientSecret }}">

                    <div class="form-group">
                        <label for="name">Cardholder Full Name</label>
                        <input id="name" name="name" type="text" required class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" required class="form-control">
                    </div>

                    <div id="card-element">
                        <!-- Stripe Elements will be inserted here. -->
                    </div>

                    <div id="card-errors" role="alert"></div>

                    <button id="submit-button" class="btn btn-primary">Authorize my credit card</button>
                </form>
            </div>
        </div>
    </div>
</div>
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        let stripe = Stripe('{{ config('app.stripe_public_key') }}');
        let clientSecret = document.getElementById('clientSecret').value;

        const elements = stripe.elements({ clientSecret });
        const paymentElement = elements.create('payment');
        paymentElement.mount('#card-element');

        paymentElement.on('change', function(event) {
            var displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });

        var form = document.getElementById('payment-form');
        form.addEventListener('submit', async function(event) {
            event.preventDefault();
            document.getElementById('submit-button').disabled = true;
            document.getElementById('payment-message').style.display = 'none';

            stripe.confirmSetup({
                elements,
                redirect: 'if_required'
            }).then(function(result) {
                
                console.log(result);

                document.getElementById('submit-button').disabled = false;
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
                    if (result.setupIntent.status === 'succeeded') {
                        // Show success message
                        paymentMessage.textContent = 'Your have been registered as potential custom successfully!';
                        paymentMessage.className = "alert alert-success";
                        paymentMessage.style.display = 'block';

                        // Handle server-side authorization (this is where processPayment is called)
                        fetch('{{ route('payment.process') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                payment_method: result.setupIntent.payment_method, // Send the payment method ID
                                email: document.getElementById('email').value,
                                name: document.getElementById('name').value
                            })
                        }).then(response => response.json()).then(data => {
                            if (data.success) {
                                // Handle success
                                alert(data.message);
                            } else {
                                // Handle failure
                                alert('Payment failed');
                            }
                        });
                    }
                }
            });
        });
    </script>
@endsection
