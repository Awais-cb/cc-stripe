@extends('layouts.master')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Stripe Payment Form</div>

                <div class="card-body">

                    @if(session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form id="payment-form" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="amount">Amount</label>
                            <input type="number" name="amount" id="amount" class="form-control" placeholder="Amount in cents (e.g., 1000 for $10)" required>
                        </div>

                        <div class="form-group">
                            <label for="currency">Currency</label>
                            <select name="currency" id="currency" class="form-control" required>
                                <option value="usd">USD - United States Dollar</option>
                                <option value="eur">EUR - Euro</option>
                                <option value="gbp">GBP - British Pound Sterling</option>
                                <option value="aed">AED - Dirham</option>
                                <!-- Add more currencies as needed -->
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="order_id">Order ID</label>
                            <input type="text" name="order_id" id="order_id" class="form-control" placeholder="Unique Order ID" required>
                        </div>

                        <button type="submit" class="btn btn-primary" id="initiate-payment">Initiate Payment</button>

                        <button type="submit" class="btn btn-secondary" id="hosted-payment">Go to Stripe Hosted Option</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var form = document.getElementById('payment-form');
        var initiateButton = document.getElementById('initiate-payment');
        var hostedButton = document.getElementById('hosted-payment');

        initiateButton.addEventListener('click', function(e) {
            e.preventDefault();
            form.action = '{{ route('stripe.payment') }}';
            form.submit();
        });

        hostedButton.addEventListener('click', function(e) {
            e.preventDefault();
            form.action = '{{ route('stripe.hosted.payment') }}';
            form.submit();
        });
    });
</script>
@endsection
