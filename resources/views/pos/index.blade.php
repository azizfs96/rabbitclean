@extends('layouts.app')

@section('content')
    <style>
        .client_payment_box.selected {
            /* border: 2px solid #00b894; */
            border: 1px solid #487be9;
            border-radius: 5px;
        }

        .client_payment_box {
            cursor: pointer;
            transition: border 0.3s;
            height: 50px;
        }
    </style>

    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-3">
            <h3 class="mb-0">
                {{ __('Point_Of_Sale') }}
            </h3>
        </div>

        <form id="buscatForm" method="POST">
            @csrf
            <div class="row mb-3">
                <div class="col-xl-8">



                    {{-- services --}}
                    <div class="card border-0 overflow-hidden">
                        <div class="card-header py-3">
                            <h3 class="m-0">{{ __('Select') . ' ' . __('Service') }}</h3>
                        </div>
                        <div class="card-body">
                            <div class="d-flex gap-3 flex-wrap">
                                @forelse ($services as $service)
                                    <div class="service-card" onclick="selectService({{ $service->id }})"
                                        id="service-{{ $service->id }}">
                                        <img src="{{ asset($service->thumbnailPath) }}" alt="" width="100%"
                                            class="rounded">
                                        <h3 class="text-center mb-0">{{ $service->name }}</h3>
                                    </div>
                                @empty
                                    <h3 class="text-center">
                                        {{ __('No') . ' ' . __('Service') }}
                                    </h3>
                                @endforelse

                            </div>
                        </div>
                    </div>

                    {{-- products --}}



                    <div class="card mt-3 border-0 overflow-hidden" style="border-radius: 12px;">
                        <div class="card-header py-3">
                            <h3 class="m-0">{{ __('Select') . ' ' . __('Products') }}</h3>
                        </div>
                        <div class="card-body">

                            {{-- variants --}}
                            <div class="d-flex flex-wrap mb-3" id="varients" style="gap: 12px 0">

                            </div>

                            {{-- product list --}}
                            <div class="d-flex gap-3 flex-wrap" id="products">

                            </div>

                        </div>
                    </div>

                </div>
                <div class="col-xl-4 mt-2 mt-xl-0">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="m-0">{{ __('Order') . ' ' . __('Basket') }}</h3>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-wrap gap-3 border-bottom border-light pb-3 mb-3"
                                style="border-top-right-radius: 0 !important;">
                                <div class="flex-grow-1">
                                    <div class="input-group d-flex">
                                        <span class="input-group-text border-start border bg-white ps-2 pe-1 ">
                                            <i class="fas fa-user-circle text-muted"></i>
                                        </span>
                                        <div class="flex-grow-1 customerSelect">
                                            <select name="customer_id" class="form-control select2" style="width: 100%;"
                                                data-placeholder="{{ __('Enter customer name or phone number') }}"
                                                id="customerId">
                                                <option selected value="">
                                                    {{ __('Select Customer') }}
                                                </option>
                                                @foreach ($customers as $customer)
                                                    <option value="{{ $customer->id }}">
                                                        {{ $customer->user?->name . '-(' . $customer->user?->mobile . ')' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                    </div>
                                </div>
                                <div class="flex-grow-1 flex-lg-grow-0">
                                    <button type="button" class="btn btn-outline-primary w-100 py-2" data-toggle="modal"
                                        data-target="#customerModal">
                                        <i class="bi bi-plus-circle-fill me-2"></i>
                                        {{ __('Add Customer') }}
                                    </button>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-3 border-bottom border-light pb-3 mb-3"
                                style="border-top-right-radius: 0 !important;">
                                <div class="flex-grow-1">
                                    <div class="input-group d-flex">

                                        <div class="flex-grow-1 addressSelect">
                                            <select name="address_id" class="form-control select2" style="width: 100%;"
                                                data-placeholder="{{ __('Enter customer address') }}" id="addressId">
                                                <option selected value="">
                                                    {{ __('Select Address') }}
                                                </option>

                                            </select>
                                            <input type="hidden" name="address_id" id="addressInfo">
                                        </div>

                                    </div>
                                </div>
                                <div class="flex-grow-1 flex-lg-grow-0">
                                    <button type="button"
                                        class="btn btn-outline-primary w-100 py-2 d-none"id="addAddressButton"
                                        data-toggle="modal" data-target="#addressModal">
                                        <i class="bi bi-plus-circle-fill me-2"></i>
                                        {{ __('Add Address') }}
                                    </button>
                                </div>
                            </div>

                            <div class="d-flex flex-column gap-3" id="basketProducts">

                            </div>

                            <div class="border-top mt-3 d-flex flex-column">

                                <p
                                    class="mt-1 mb-0 border-bottom border-light py-2 d-flex justify-content-between font-weight-500">
                                    {{ __('Total_Amount:') }} <span id="totalAmount"></span>
                                </p>
                                <input type="hidden" name="total_amount" id="totalAmountInput">

                                <p
                                    class="mb-0 border-bottom border-light py-2 d-flex justify-content-between font-weight-500">
                                    {{ __('Discount:') }} <input type="text" name="discount" id="discountInput"
                                        value="" placeholder="{{ currencyPosition(0) }}" class="form-control w-25" onkeydown="return event.key !== 'Enter';">
                                </p>


                                <p
                                    class="mb-0 border-bottom border-light py-2 d-flex justify-content-between font-weight-500">
                                    {{ __('Delivery_Charge:') }} <input type="text" name="delivery_charge"
                                        id="deliveryChargeInput" value="" placeholder="{{ currencyPosition(0) }}"
                                        class="form-control w-25" onkeydown="return event.key !== 'Enter';">
                                </p>

                                <h3 class="mb-0 border-bottom border-light py-2 d-flex justify-content-between">
                                    {{ __('Grand_Total:') }} <span id="grandTotal"></span>

                                </h3>
                                <input type="hidden" name="grand_total" id="grandTotalInput">

                                <span class="detail mt-4">{{ __('Payment_method') }}</span>
                                <div class="credit rounded flex row mt-3 row mx-1">
                                    <div
                                        class="col-3 client_payment_box d-flex justify-content-center align-items-center selected">
                                        <input type="radio" name="payment_gateway" value="cash" id="paymentCash"
                                            class="d-none">
                                        <label for="paymentCash">
                                            <img src="{{ asset('images/cash2.png') }}" class="rounded" width="100"
                                                alt="">
                                        </label>

                                    </div>

                                    @foreach ($paymentGateways as $paymentGateway)
                                        <div
                                            class=" col-3 d-flex justify-content-center align-items-center client_payment_box ">
                                            <label for="payment_gateway_{{ $paymentGateway->id }}">
                                                <img src="{{ $paymentGateway->logo }}" class="rounded" width="100"
                                                    alt="">
                                            </label>
                                            <input type="radio" name="payment_gateway"
                                                value="{{ $paymentGateway->name->value }}"
                                                id="payment_gateway_{{ $paymentGateway->id }}" class="d-none">
                                        </div>
                                    @endforeach
                                </div>
                                <a href="#" class="btn btn-primary btn-sm mb-1 py-2 mt-3" onclick="submitform()">
                                    {{ __('Confirm_Order') }}</a>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <iframe id="webView"></iframe>
    </div>
    <!-- address create modal -->
    <form action="#" id="addressForm">
        <div class="modal fade" id="addressModal">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content p-4">
                    <div class="modal-header border-0">
                        <h1 class="modal-title fs-4" id="productModalLabel">
                            {{ __('Add_New_Address') }}
                        </h1>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body py-0">
                        <div class="row">
                            <div class="col-12">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mt-3">
                                            <label class="form-label">{{ __('Address_name') }}</label>
                                            <x-input label="First Name" name="address_name" type="text"
                                                placeholder="{{ __('Address_name') }}" class="form-control"
                                                required="true" />
                                        </div>
                                        <input name="customer_id" type="hidden" class="form-control" required="true"
                                            id="hiddenCustomerId" />
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mt-3">
                                            <label class="form-label">{{ __('Road_no') }}</label>
                                            <x-input label="Last Name" name="road_no" type="text"
                                                placeholder="{{ __('Road_no') }}" />
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mt-3">
                                            <label class="form-label">{{ __('House_number') }}</label>
                                            <x-input label="First Name" name="house_no" type="text"
                                                placeholder="{{ __('House_number') }}" class="form-control" required />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mt-3">
                                            <label class="form-label">{{ __('Houser_name') }}</label>
                                            <x-input label="First Name" name="house_name" type="text"
                                                placeholder="{{ __('House_name') }}" class="form-control" required />
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mt-3">
                                            <label class="form-label">{{ __('Area') }}</label>
                                            <x-input label="First Name" name="area" type="text"
                                                placeholder="{{ __('Area') }}" class="form-control" required />
                                        </div>
                                    </div>

                                </div>

                            </div>
                        </div>

                    </div>
                    <div
                        class="modal-footer border-0 mt-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <button type="button" class="btn btn-danger py-3 flex-grow-1" data-dismiss="modal">
                            {{ __('Close') }}
                        </button>

                        <button type="submit" class="btn btn-primary py-3 flex-grow-1">
                            {{ __('Submit') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <!-- customer create modal -->
    <form action="#" id="customerForm">
        <div class="modal fade" id="customerModal">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content p-4">
                    <div class="modal-header border-0">
                        <h1 class="modal-title fs-4" id="productModalLabel">
                            {{ __('Add_New_Customer') }}
                        </h1>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body py-0">
                        <div class="row">
                            <div class="col-12">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mt-3">
                                            <label class="form-label">{{ __('First_Name') }}</label>
                                            <x-input label="First Name" name="first_name" type="text"
                                                placeholder="{{ __('First_Name') }}" class="form-control"
                                                required="true" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mt-3">
                                            <label class="form-label">{{ __('Last_Name') }}</label>
                                            <x-input label="Last Name" name="last_name" type="text"
                                                placeholder="{{ __('Enter_Last_Name') }}" />
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="form-label">{{ __('Phone_Number') }}</label>
                                    <x-input label="Phone Number" name="phone" type="number"
                                        placeholder="{{ __('Enter_Phone_Number') }}" required="true" />
                                </div>

                                <div class="mt-3">
                                    <label class="form-label">{{ __('Gender') }}</label>
                                    <x-select label="Gender" name="gender">
                                        <option value="male" selected>{{ __('Male') }}</option>
                                        <option value="female">{{ __('Female') }}</option>
                                    </x-select>
                                </div>
                                <div class="mt-3">
                                    <label class="form-label">{{ __('Email') }}</label>
                                    <x-input type="email" name="email" label="Email"
                                        placeholder="{{ __('Enter_Email_Address') }}" />
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="is_active" value="1">
                    </div>
                    <div
                        class="modal-footer border-0 mt-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <button type="button" class="btn btn-danger py-3 flex-grow-1" data-dismiss="modal">
                            {{ __('Close') }}
                        </button>

                        <button type="submit" class="btn btn-primary py-3 flex-grow-1">
                            {{ __('Submit') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
@push('scripts')
    <script>
        $(document).ready(function() {
            $('#customerId').on('change', function() {
                let customerId = $(this).val();
                let addressSelect = $('#addressId');

                $('#hiddenCustomerId').val(customerId);

                let addAddressButton = $('#addAddressButton');

                if (customerId) {

                    addAddressButton.removeClass('d-none');
                } else {

                    addAddressButton.addClass('d-none');
                }
                if (customerId) {
                    addressSelect.html('<option selected>{{ __('Loading...') }}</option>');

                    $.ajax({
                        url: '/get-customer-addresses/' +
                            customerId,
                        type: 'GET',
                        success: function(response) {
                            addressSelect.html(
                                '<option selected value="">{{ __('Select Address') }}</option>'
                            );


                            response.forEach(function(address) {

                                addressSelect.append(
                                    `<option value="${address.id}">${address.id} ${address.address_name} (${address.road_no})</option>`

                                );

                            });

                        },
                        error: function() {
                            addressSelect.html(
                                '<option selected value="">{{ __('Failed to load addresses') }}</option>'
                            );
                        },
                    });
                } else {
                    addressSelect.html('<option selected value="">{{ __('Select Address') }}</option>');
                }
            });
        });



        $(document).on('click', '.client_payment_box', function() {
            $(this).addClass('selected').siblings().removeClass('selected');
        });

        function submitform() {

            let stripe, elements, cardElement;
            const form = document.getElementById('buscatForm');
            const formData = new FormData(form);

            const data = {};
            formData.forEach((value, key) => {
                data[key] = value;
            });


            data['products'] = basket.map(product => ({
                id: product.id,
                quantity: product.quantity,
                price: product.current_price
            }));

            if (!data['customer_id']) {
                alert('customer must be selected');
            }
            if (data['products'] == 0) {
                alert('product must be selected');
            }
            var payment_gateway = data['payment_gateway'] ?? 'cash'

            let addressId =   $('select[name="address_id"]').val();
            console.log(addressId,'hhh');


            $.ajax({
                url: "{{ route('pos.store') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    customer_id: data['customer_id'],
                    delivery_charge: data['delivery_charge'],
                    payment_id: payment_gateway,
                    discount: data['discount'],
                    products: data['products'],
                    address_id: $('#addressId').val(),
                },
                success: (response) => {


                    if (response.data['payment_type'] != 'cash') {

                        const iframe = document.getElementById('webView');
                        let url = response.data['payment_url'];

                        const popupWidth = 600;
                        const popupHeight = 600;
                        const screenWidth = window.screen.width;
                        const screenHeight = window.screen.height;
                        const left = (screenWidth - popupWidth) / 2;
                        const top = (screenHeight - popupHeight) / 2;

                        const popup = window.open(
                            url,
                            "WebViewPopup",
                            `width=${popupWidth},height=${popupHeight},top=${top},left=${left},resizable=yes,scrollbars=yes`
                        );
                        if (popup) {

                            const checkPopupLoaded = setInterval(() => {

                                if (popup.document.readyState === 'complete') {
                                    popup.postMessage({
                                        order: response.orders
                                    }, '*');
                                    clearInterval(
                                        checkPopupLoaded);
                                }
                            }, 100);
                            setTimeout(() => {
                                location.reload();
                            }, 2000);


                        } else {
                            console.error('Popup could not be opened.');
                        }


                    } else if (response.data['payment_type'] === 'cash') {

                        Swal.fire({
                            title: 'Success!',
                            text: response.message,
                            icon: 'success',
                            showConfirmButton: false,
                            timer: 5000, // Display the alert for 5 seconds
                            timerProgressBar: true
                        }).then(() => {
                            location.reload();
                        });

                    }


                },
                error: (error) => {
                    Toast.fire({
                        icon: 'error',
                        title: error.responseJSON.message,
                    });
                    console.log(error.responseJSON.message);
                },


            });


        }
    </script>
    <script>
        var currentServiceId = null;

        function selectService(serviceId) {
            $('.service-card').removeClass('active');
            $('#service-' + serviceId).addClass('active');
            currentServiceId = serviceId;
            fetchVariants(serviceId);
        }

        function fetchVariants(serviceId) {
            var variantId = null;
            $('#varients').empty();
            $.ajax({
                url: "{{ route('pos.fetch.variants') }}",
                type: 'GET',
                data: {
                    service_id: serviceId
                },
                success: function(response) {
                    // Clear existing variants
                    $('#varients').empty();

                    // Append new variants
                    response.data.variants.forEach(function(variant, index) {
                        var variantButton = $('<button>')
                            .addClass('btn border')
                            .text(variant.name)
                            .attr('onclick', 'selectVariant(' + variant.id + ')')
                            .attr('id', 'variant-' + variant.id)
                            .attr('type', 'button');

                        // Add the btn-primary class to the first variant
                        if (index === 0) {
                            variantButton.addClass('btn-primary');
                            variantId = variant.id;
                        }

                        $('#varients').append(variantButton);
                    });

                    if (variantId) {
                        fetchProducts(variantId);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching variants:', error);
                }
            });
        }

        function selectVariant(variantId) {
            $('#varients .btn').removeClass('btn-primary');
            $('#variant-' + variantId).addClass('btn-primary');

            fetchProducts(variantId);
        }

        function fetchProducts(varintId) {
            $.ajax({
                url: "{{ route('pos.fetch.products') }}",
                type: 'GET',
                data: {
                    service_id: currentServiceId,
                    variant_id: varintId
                },
                success: function(response) {
                    // Clear existing products
                    $('#products').empty();

                    // Append new products
                    response.data.products.forEach(function(product) {
                        var productCard = $('<div>')
                            .addClass('service-card')
                            .attr('onclick', 'addProductToBasket(' + JSON.stringify(product) + ')');

                        var productImage = $('<img>')
                            .attr('src', product.image_path)
                            .attr('alt', '')
                            .css('width', '100%')
                            .addClass('rounded');

                        var productTitle = $('<h3>')
                            .addClass('text-center mb-0')
                            .text(product.name);

                        productCard.append(productImage).append(productTitle);
                        $('#products').append(productCard);
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching products:', error);
                }
            });
        }

        let basket = [];

        function addProductToBasket(product) {
            // Check if product is already in basket
            let existingProduct = basket.find(p => p.id === product.id);

            if (existingProduct) {
                existingProduct.quantity += 1;
            } else {
                product.quantity = 1;
                basket.push(product);
            }

            renderBasket();
            updateTotalAmount();
        }


        function renderBasket() {
            $('#basketProducts').empty();

            basket.forEach(product => {
                let productDiv = $(`
                <input type="hidden" name="products[${product.id}][id]" value="${product.id}">
                <input type="hidden" name="products[${product.id}][quantity]" value="${product.quantity}">
                <div class="d-flex gap-3 align-items-center pt-2" style="border-top: 1px dashed #eee" data-product-id="${product.id}">
                    <div class="border rounded">
                        <img src="${product.image_path}" alt="" width="100" height="90" class="rounded object-fit-cover">
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="m-0 d-flex justify-content-between flex-wrap gap-3">
                            ${product.name}
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeProductFromBasket(${product.id})">
                                <i class="fa fa-trash"></i>
                            </button>
                        </h3>
                        <p class="m-0 font-weight-500">
                            ${currencyPosition(product.current_price)}
                        </p>
                        <input type="hidden" name="products[${product.id}][price]" value="${product.current_price}">
                        <div class="d-flex align-items-center gap-3">
                            <button class="btn btn-sm m-0" onclick="changeQuantity(${product.id}, -1)">
                                <i class="fa fa-minus"></i>
                            </button>
                            <input type="text" class="text-center border rounded" value="${product.quantity}" readonly style="width: 46px">
                            <button class="btn btn-sm m-0" onclick="changeQuantity(${product.id}, 1)">
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `);
                $('#basketProducts').append(productDiv);
            });

            updateTotalAmount();
        }

        function changeQuantity(productId, change) {
            let product = basket.find(p => p.id === productId);

            if (product) {
                product.quantity += change;
                if (product.quantity <= 0) {
                    removeProductFromBasket(productId);
                } else {
                    renderBasket();
                }
            }
        }

        function removeProductFromBasket(productId) {
            basket = basket.filter(p => p.id !== productId);
            renderBasket();
        }

        $('#discountInput').on('input', function() {
            var input = document.getElementById('discountInput');
            input.value = input.value.replace(/[^0-9.]/g, '');
            updateTotalAmount();
        });

        $('#deliveryChargeInput').on('input', function() {
            var input = document.getElementById('deliveryChargeInput');
            input.value = input.value.replace(/[^0-9.]/g, '');
            updateTotalAmount();
        });

        function updateTotalAmount() {
            let totalAmount = basket.reduce((total, product) => {
                let price = product.current_price;
                return total + (price * product.quantity);
            }, 0);

            $('#totalAmount').text(currencyPosition(totalAmount.toFixed(2)));
            $('#totalAmountInput').val(totalAmount.toFixed(2));

            let discount = parseFloat($('#discountInput').val()) || 0;
            let deliveryCharge = parseFloat($('#deliveryChargeInput').val()) || 0;
            let grandTotal = totalAmount - discount + deliveryCharge;

            if (grandTotal < 0) {
                grandTotal = 0;
            }

            $('#grandTotal').text(currencyPosition(grandTotal.toFixed(2)));
            $('#grandTotalInput').val(grandTotal.toFixed(2));
        }

        function currencyPosition(amount) {
            // Assuming USD currency
            return '$' + amount;
        }

        function submitOrder() {
            console.log('Order submitted:', basket);
        }

        $('#customerForm').submit(function(e) {
            e.preventDefault();

            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                },
            });

            $.ajax({
                url: "{{ route('pos.customerStore') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    first_name: $('input[name="first_name"]').val(),
                    last_name: $('input[name="last_name"]').val(),
                    mobile: $('input[name="phone"]').val(),
                    gender: $('select[name="gender"]').val(),
                    email: $('input[name="email"]').val(),
                    password: $('input[name="phone"]').val(),
                },
                success: (response) => {
                    $('#customerModal').modal('hide');
                    Toast.fire({
                        icon: 'success',
                        title: response.message,
                    });

                    var customer = $('select[name="customer_id"]');
                    var user = response.data.user;

                    customer.append(
                        `<option value="${user.id}" selected>${user.name}-(${user.mobile})</option>`
                    );
                    customer.val(user.id);

                    $('input[name="first_name"]').val('');
                    $('input[name="last_name"]').val('');
                    $('input[name="phone"]').val('');
                    $('select[name="gender"]').val('');
                    $('input[name="email"]').val('');
                },
                error: (error) => {
                    Toast.fire({
                        icon: 'error',
                        title: error.responseJSON.message,
                    });
                    console.log(error.responseJSON.message);
                },
            });
        });

        $('#addressForm').submit(function(e) {
            e.preventDefault();

            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                },
            });

            $.ajax({
                url: "{{ route('pos.addressStore') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    address_name: $('input[name="address_name"]').val(),
                    road_no: $('input[name="road_no"]').val(),
                    house_no: $('input[name="house_no"]').val(),
                    house_name: $('input[name="house_name"]').val(),
                    area: $('input[name="area"]').val(),
                    customer_id: $('input[name="customer_id"]').val(),
                },
                success: (response) => {
                    $('#addressModal').modal('hide');
                    Toast.fire({
                        icon: 'success',
                        title: response.message,
                    });

                    var addressinfo = $('select[name="address_id"]');
                    var address = response.data.address;
                    console.log(response.data,address);


                    addressinfo.append(
                        `<option value="${address.id}"> ${address.address_name} (${address.road_no})</option>`
                    );
                    addressinfo.val(address.id);

                    $('input[name="address_name"]').val('');
                    $('input[name="road_no"]').val('');
                },
                error: (error) => {
                    Toast.fire({
                        icon: 'error',
                        title: error.responseJSON.message,
                    });
                    console.log(error.responseJSON.message);
                },
            });
        });
    </script>
@endpush
