<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .heading {
            font-size: 23px;
            font-weight: 700;
        }

        .text {
            font-size: 16px;
            font-weight: 500;
            color: #b1b6bd;
        }

        .pricing {
            border: 2px solid #304FFE;
            background-color: #f2f5ff;
        }

        .business {
            font-size: 20px;
            font-weight: 500;
        }

        .plan {
            color: #aba4a4;
        }

        .dollar {
            font-size: 16px;
            color: #6b6b6f;
        }

        .amount {
            font-size: 50px;
            font-weight: 500;
        }

        .year {
            font-size: 20px;
            color: #6b6b6f;
            margin-top: 19px;
        }

        .detail {
            font-size: 22px;
            font-weight: 500;
        }

        .cvv {
            height: 44px;
            width: 73px;
            border: 2px solid #eee;
        }

        .cvv:focus {
            box-shadow: none;
            border: 2px solid #304FFE;
        }

        .email-text {
            height: 55px;
            border: 2px solid #eee;
        }

        .email-text:focus {
            box-shadow: none;
            border: 2px solid #304FFE;
        }

        .payment-button {
            height: 70px;
            font-size: 20px;
        }

        #card-element {
            height: 40px;
        }

        .client_payment_box {
            cursor: pointer;
            transition: border 0.3s;
        }

        .client_payment_box.selected {
            border: 1px solid #487be9;
            /* border: 2px solid #007bff; */
            border-radius: 5px;
        }
    </style>
</head>

<body>


    <div class=" mt-5 mx-5 mb-5 ">
        <div class="card p-5 my-5">
            @php
                $gateway = request()->query('gateway');
                $order = request()->query('order');
                $orders = DB::table('orders')->get();

            @endphp
            @foreach ($orders as $ord)

                @if ((int) $order == $ord->id)
                    @if ($ord->payment_status != 'Paid')
                        <span class="detail">{{ __('Payment_method:') }} {{ $gateway }}</span>
                        <form id="payment-form">
                            @csrf
                            <input type="hidden" name="payment_method" id="selected-method"
                                value="{{ $gateway }}">
                            <input type="hidden" name="token_id" id="token_id">

                            <input type="hidden" name="order_id" value="{{ $order }}">
                            @foreach ($orders as $ord)
                                @if ((int) $order == $ord->id)
                                    <input type="hidden" name="total_amount" id="total_amount"
                                        value="{{ $ord->total_amount }}">
                                @endif
                            @endforeach

                            <div id="paytabs-info" class="alert alert-info mt-3" style="display: none;">
                                <i class="fa fa-info-circle"></i> You will be redirected to PayTabs secure payment page.
                            </div>
                            <span class="text-danger" id="payment-error"></span>


                            <div class="mt-3">
                                <button type="button" class="btn btn-success btn-sm common-btn btn-block  w-100"
                                    id="pay-btn">
                                    {{ __('Proceed_to_payment') }} <i class="fa fa-long-arrow-right"></i>
                                </button>
                            </div>
                        </form>
                    @else

                    <script>
                        window.location.href = "{{ route('pos.payment', ['status' => $ord->payment_status]) }}"; // Replace with your route name
                    </script>
                    @endif
                @endif
            @endforeach


        </div>
    </div>
    {{-- </div> --}}
    {{-- </div> --}}
    {{-- </div> --}}

    {{-- </section> --}}

</body>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<script>
    let formSubmitted = false;

    $(document).ready(function() {
        const urlParams = new URLSearchParams(window.location.search);
        const gateway = urlParams.get('gateway');
        const paymentMethod = document.getElementById("selected-method").value;

        // Show PayTabs info for paytabs payment
        if (gateway === 'paytabs' || paymentMethod === 'paytabs') {
            $('#paytabs-info').show();
            $('#pay-btn').prop('disabled', false);
        } else if (gateway === 'cash' || paymentMethod === 'cash') {
            // Cash payment - just enable button
            $('#pay-btn').prop('disabled', false);
        }
    });

    // Pay button click handler
    $(document).on('click', '#pay-btn', function() {
        const paymentMethod = document.getElementById("selected-method").value;
        
        if (formSubmitted) {
            return;
        }

        formSubmitted = true;
        $('#pay-btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

        if (paymentMethod === 'paytabs') {
            // PayTabs - redirect to payment gateway
            processPayTabsPayment();
        } else if (paymentMethod === 'cash') {
            // Cash payment - just update order
            processCashPayment();
        } else {
            $('#payment-error').text('Invalid payment method selected');
            formSubmitted = false;
            $('#pay-btn').prop('disabled', false).html('{{ __('Proceed_to_payment') }} <i class="fa fa-long-arrow-right"></i>');
        }
    });

    // Process PayTabs Payment
    function processPayTabsPayment() {
        const orderId = $('input[name="order_id"]').val();
        const url = `{{ route('payment.process', ':order') }}`.replace(':order', orderId);
        const totalAmount = $('input[name="total_amount"]').val();

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                payment_method: 'paytabs',
                order: orderId,
                total_amount: totalAmount,
            },
            success: function(response) {
                if (response.success && response.redirect_url) {
                    // Redirect to PayTabs payment page
                    window.location.href = response.redirect_url;
                } else if (response.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: response.message || 'Payment processed successfully',
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    }).then(() => {
                        window.close();
                    });
                } else {
                    $('#payment-error').text(response.message || 'Payment failed');
                    formSubmitted = false;
                    $('#pay-btn').prop('disabled', false).html('{{ __('Proceed_to_payment') }} <i class="fa fa-long-arrow-right"></i>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Payment error:', error);
                const errorMsg = xhr.responseJSON?.message || 'An error occurred processing your payment';
                $('#payment-error').text(errorMsg);
                formSubmitted = false;
                $('#pay-btn').prop('disabled', false).html('{{ __('Proceed_to_payment') }} <i class="fa fa-long-arrow-right"></i>');
            }
        });
    }

    // Process Cash Payment
    function processCashPayment() {
        const orderId = $('input[name="order_id"]').val();
        const url = `{{ route('payment.process', ':order') }}`.replace(':order', orderId);
        const totalAmount = $('input[name="total_amount"]').val();

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                payment_method: 'cash',
                order: orderId,
                total_amount: totalAmount,
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: response.message || 'Order placed successfully',
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    }).then(() => {
                        window.close();
                    });
                } else {
                    $('#payment-error').text(response.message || 'Payment processing failed');
                    formSubmitted = false;
                    $('#pay-btn').prop('disabled', false).html('{{ __('Proceed_to_payment') }} <i class="fa fa-long-arrow-right"></i>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Payment error:', error);
                const errorMsg = xhr.responseJSON?.message || 'An error occurred';
                $('#payment-error').text(errorMsg);
                formSubmitted = false;
                $('#pay-btn').prop('disabled', false).html('{{ __('Proceed_to_payment') }} <i class="fa fa-long-arrow-right"></i>');
            }
        });
    }
</script>


</html>
