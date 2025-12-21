@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-lg-12">
                <div class="card-header py-2 d-flex justify-content-between align-items-center">
                    <h3 class="card-title m-0">
                        {{ __('Order') . ' ' . __('Details') . ' ' . __('of') }} {{ $order->customer->user->name }}
                    </h3>

                    <div class="">
                        <a class="btn btn-light" href="{{ route('order.index') }}"> {{ __('Back') }} </a>
                        @can('order.print.invioce')
                            <a class="btn btn-danger" href="{{ route('order.print.invioce', $order->id) }}" target="_blank">
                                <i class="fas fa-print"></i> {{ __('Print') }}
                            </a>
                        @endcan

                        @can('order.status.change')
                            <div class="drop-down">
                                <a class="btn btn-primary @role('visitor') visitorMessage @endrole" style="min-width:150px" href="#status @role('visitor') visitor @endrole" data-toggle="collapse"
                                    aria-expanded="false" role="button" aria-controls="navbar-examples">
                                    <span class="nav-link-text">{{ __($order->order_status) }}</span>
                                    <i class="fa fa-chevron-down"></i>
                                </a>

                                <div class="collapse drop-down-items mt-1" id="status">
                                    <ul class="nav nav-sm flex-column">
                                        @foreach (config('enums.order_status') as $key => $order_status)
                                            <li class="nav-item">
                                                <a class="nav-link"
                                                    href="{{ route('order.status.change', ['order' => $order->id, 'status' => $key]) }}">
                                                    {{ __($order_status) }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endcan

                    </div>
                </div>
            </div>
            <div class="col-md-6 mt-2">
                <div class="card d-flex flex-column h-100">
                    <div class="card-header py-2">
                        <h2 class="m-0">{{ __('Order') . ' ' . __('Details') }}</h2>
                    </div>
                    <div class="card-body pt-2">
                        <div class="table-responsive-md">
                            <table class="table table-bordered table-striped {{ session()->get('local') }}">
                                <tr>
                                    <th class="py-2">{{ __('Order') . ' ' . __('Status') }}</th>
                                    <td class="py-2">{{ __($order->order_status) }}</td>
                                </tr>
                                <tr>
                                    <th class="py-2">{{ __('Payment') . ' ' . __('Status') }}</th>
                                    <td class="py-2">
                                        @if ($order->payment_status != 'Paid')
                                            <div class="d-flex justify-content-between align-items-center">
                                                {{ __($order->payment_status) }}
                                                <a href="{{ route('orderIncomplete.paid', $order->id) }}"
                                                    class="btn btn-primary py-2 px-4 paid-confirm">{{ __('Paid') }}</a>
                                            </div>
                                        @else
                                            {{ __($order->payment_status) }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="py-2">{{ __('Total') . ' ' . __('Amount') }}</th>
                                    <td class="py-2">{{ currencyPosition($order->total_amount) }}</td>
                                </tr>
                                <tr>
                                    <th class="py-2">{{ __('Discount') }}</th>
                                    <td class="py-2">{{ currencyPosition($order->discount) }}</td>
                                </tr>
                                <tr>
                                    <th class="py-2">{{ __('Delivery_charge') }}</th>
                                    <td class="py-2">{{ currencyPosition($order->delivery_charge) }}</td>
                                </tr>
                                <tr>
                                    <th class="py-2">{{ __('Total') . ' ' . __('Quantity') }}</th>
                                    <td class="py-2">{{ $quantity . ' ' . __('Pieces') }}</td>
                                </tr>
                                <tr>
                                    <th class="py-2">{{ __('Items') }}</th>
                                    <td class="py-2">{{ $order->products->count() }}</td>
                                </tr>
                                <tr>
                                    <th class="py-2">{{ __('Order') . ' ' . __('At') }}</th>
                                    <td class="py-2">{{ $order->created_at->format('F d, Y h:i A') }}</td>
                                </tr>
                                <tr>
                                    <th class="py-2">{{ __('Picked') . ' ' . __('Date') }}</th>
                                    <td class="py-2">
                                        {{ Carbon\Carbon::parse($order->pick_date)->format('F d, Y') }}
                                        @if($order->pick_hour)
                                        <span class="badge badge-pill bg-light text-dark" style="font-size: 14px">
                                            {{ $order->getTime(substr($order->pick_hour, 0, 2)) }}
                                        </span>
                                        @else
                                        <span class="badge badge-pill bg-warning text-dark" style="font-size: 14px">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="py-2">{{ __('Delivery') . ' ' . __('Date') }}</th>
                                    <td class="py-2">
                                        {{ Carbon\Carbon::parse($order->delivery_date)->format('F d, Y') }}
                                        @if($order->delivery_hour)
                                        <span class="badge badge-pill bg-light text-dark" style="font-size: 14px">
                                            {{ $order->getTime(substr($order->delivery_hour, 0, 2)) }}
                                        </span>
                                        @else
                                        <span class="badge badge-pill bg-warning text-dark" style="font-size: 14px">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mt-2">
                <div class="card d-flex flex-column h-100">
                    <div class="card-header py-2">
                        <h2 class="m-0">{{ __('Customer') . ' ' . __('Details') }}</h2>
                    </div>
                    <div class="card-body pt-2">
                        <div class="table-responsive-md">
                            <table class="table table-bordered table-striped {{ session()->get('local') }}">
                                <tr>
                                    <th class="py-2">{{ __('Name') }}</th>
                                    <td class="py-2" class="py-2">{{ $order->customer->user->name }}</td>
                                </tr>
                                <tr>
                                    <th class="py-2">{{ __('Customer') . ' ' . __('Photo') }}</th>
                                    <td class="py-2">
                                        <img style="max-width: 80px" src="{{ $order->customer->profilePhotoPath }}"
                                            alt="">
                                    </td>
                                </tr>
                                <tr>
                                    <th class="py-2">{{ __('Email') }}</th>
                                    <td class="py-2">{{ $order->customer->user->email }}
                                        @if ($order->customer->user->email_verified_at)
                                            <span class="badge bg-success text-white">{{ __('verified') }}</span>
                                        @else
                                            <span class="badge bg-danger text-white">{{ __('Unverified') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="py-2">{{ __('Phone_number') }}</th>
                                    <td class="py-2">
                                        @if ($order->customer->user->mobile)
                                            {{ $order->customer->user->mobile }}
                                            @if ($order->customer->user->mobile_verified_at)
                                                <span class="badge bg-success text-white">{{ __('verified') }}</span>
                                            @else
                                                <span class="badge bg-danger text-white">{{ __('Unverified') }}</span>
                                            @endif
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Admin Product Management Section --}}
            @if (!$order->admin_completed || !$order->sent_to_customer)
            <div class="col-12 my-2">
                <div class="card">
                    <div class="card-header py-2 bg-warning">
                        <h2 class="m-0">
                            <i class="fas fa-exclamation-triangle"></i>
                            {{ __('Order Requires Admin Action') }}
                        </h2>
                    </div>
                    <div class="card-body">
                        @if (!$order->admin_completed)
                            <div class="alert alert-info">
                                <h4><i class="fas fa-info-circle"></i> {{ __('This order needs products to be added') }}</h4>
                                <p>Customer has submitted the order with service details, date/time, and address. Please add products and their quantities below.</p>
                            </div>

                            <form method="POST" action="{{ route('order.products.update', $order->id) }}" id="productForm">
                                @csrf
                                <div class="row">
                                    <div class="col-md-12">
                                        <h4>{{ __('Add Products to Order') }}</h4>
                                        <div id="productsList">
                                            <div class="product-row card mb-3 p-3">
                                                <div class="row">
                                                    <div class="col-md-5">
                                                        <label>{{ __('Product') }}</label>
                                                        <select name="products[0][product_id]" class="form-control product-select" required>
                                                            <option value="">{{ __('Select Product') }}</option>
                                                            @foreach($products as $product)
                                                                <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                                                                    {{ $product->name }} ({{ currencyPosition($product->price) }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label>{{ __('Quantity') }}</label>
                                                        <input type="number" name="products[0][quantity]" class="form-control quantity-input" min="1" value="1" required>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label>{{ __('Price') }}</label>
                                                        <input type="number" step="0.01" name="products[0][price]" class="form-control price-input" required>
                                                    </div>
                                                    <div class="col-md-1">
                                                        <label>&nbsp;</label>
                                                        <button type="button" class="btn btn-danger btn-block remove-product" style="display:none;">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <button type="button" class="btn btn-success" id="addProduct">
                                            <i class="fas fa-plus"></i> {{ __('Add Another Product') }}
                                        </button>
                                    </div>

                                    <div class="col-md-12 mt-3">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h4>{{ __('Order Summary') }}</h4>
                                                <table class="table">
                                                    <tr>
                                                        <th>{{ __('Subtotal') }}:</th>
                                                        <td class="text-right"><span id="subtotal">0.00</span> {{ __('SAR') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>{{ __('Delivery Charge') }}:</th>
                                                        <td class="text-right">
                                                            <input type="number" step="0.01" name="delivery_charge" class="form-control" style="width:150px;display:inline-block;" 
                                                                value="{{ $order->delivery_charge ?? 0 }}" id="deliveryCharge">
                                                        </td>
                                                    </tr>
                                                    <tr class="h4">
                                                        <th>{{ __('Total Amount') }}:</th>
                                                        <td class="text-right"><strong><span id="totalAmount">0.00</span> {{ __('SAR') }}</strong></td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12 mt-3">
                                        <label>{{ __('Admin Notes') }} ({{ __('Optional') }})</label>
                                        <textarea name="admin_notes" class="form-control" rows="3" placeholder="{{ __('Add any notes about this order...') }}"></textarea>
                                    </div>

                                    <div class="col-md-12 mt-3">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-save"></i> {{ __('Save Products') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        @elseif (!$order->sent_to_customer)
                            <div class="alert alert-success">
                                <h4><i class="fas fa-check-circle"></i> {{ __('Products Added Successfully') }}</h4>
                                <p>{{ __('Total Amount') }}: <strong>{{ currencyPosition($order->total_amount) }}</strong></p>
                                <p>{{ __('The order is ready to be sent to the customer for payment.') }}</p>
                            </div>

                            <form method="POST" action="{{ route('order.send.customer', $order->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-paper-plane"></i> {{ __('Send Order to Customer') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <div class="col-12 my-2">
                <div class="card">
                    <div class="card-header py-2">
                        <h2 class="m-0"> {{ __('Others Details') }}</h2>
                    </div>
                    <div class="card-body pt-2">
                        <div class="table-responsive-md">
                            <table class="table table-bordered table-striped">
                                <tr>
                                    <th class="py-2 {{ session()->get('local') == 'ar' ? 'text-right' : '' }}">
                                        {{ __('Address') }}</th>
                                    <td class="py-2">
                                        <table class="table table-bordered {{ session()->get('local') }}">
                                            <tr>
                                                <th>{{ __('Area') }}</th>
                                                <th>{{ __('Address') . ' ' . __('Name') }}</th>
                                                <th>{{ __('Flat_No') }}.</th>
                                                <th>{{ __('House_No') }}</th>
                                                <th>{{ __('Block') }}</th>
                                                <th>{{ __('Road_No') }}.</th>
                                            </tr>
                                            <tr>
                                                <td><strong>{{ $order->address->area }}</strong></td>
                                                <td><strong>{{ $order->address->address_name }}</strong></td>
                                                <td><strong>{{ $order->address->flat_no }}</strong></td>
                                                <td><strong>{{ $order->address->house_no }}</strong></td>
                                                <td><strong>{{ $order->address->block }}</strong></td>
                                                <td><strong>{{ $order->address->road_no }}</strong></td>
                                            </tr>

                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="py-2 {{ session()->get('local') == 'ar' ? 'text-right' : '' }}">
                                        {{ __('Products') }}</th>
                                    <td class="py-2">
                                        <!-- Button trigger modal -->
                                        <button type="button" class="btn btn-primary" data-toggle="modal"
                                            data-target="#staticBackdrop">
                                            {{ __('Show') . ' ' . __('All') . ' ' . __('Order') . ' ' . __('Products') }}
                                        </button>

                                        <!-- Modal -->
                                        <div class="modal fade" id="staticBackdrop">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="staticBackdropLabel">
                                                            {{ __('All') . ' ' . __('Order') . ' ' . __('Products') }}
                                                        </h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        @foreach ($order->products as $product)
                                                            <div class="bg-white my-2 py-2 overflow-hidden">
                                                                <img width="120" class="float-left mr-4"
                                                                    src="{{ $product->thumbnailPath }}" alt="">
                                                                <div class="overflow-hidden">
                                                                    <h4>{{ session()->get('local' == 'ar' ? $product->name_bn : $product->name) }}
                                                                    </h4>
                                                                    <p class="m-0">{{ __('Price') }}:
                                                                        {{ $product->discount_price ? $product->discount_price : $product->price }}
                                                                    </p>
                                                                    <p>{{ __('Quantity') }}:
                                                                        {{ $product->pivot->quantity }}</p>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-dark"
                                                            data-dismiss="modal">{{ __('Close') }}</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <th class="py-2 {{ session()->get('local') == 'ar' ? 'text-right' : '' }}">
                                        {{ __('Labels') }}</th>
                                    <td class="py-2">
                                        <!-- Button trigger modal -->
                                        <button type="button" class="btn btn-primary" data-toggle="modal"
                                            data-target="#labals">
                                            {{ __('Order') . ' ' . __('Products') }}
                                        </button>

                                        @can('order.print.labels')
                                            <a href="{{ route('order.print.labels', ['order' => $order->id, 'quantity' => $quantity]) }}"
                                                target="_blank" class="btn btn-danger">
                                                {{ __('Print') }} <i class="fas fa-print"></i>
                                            </a>
                                        @endcan

                                        <!-- Modal -->
                                        <div class="modal fade" id="labals">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content" style="background: #f6f6f6;">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="staticBackdropLabel">
                                                            {{ __('All') . ' ' . __('Order') . ' ' . __('Labels') }}
                                                        </h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            @php
                                                                $r = 1;
                                                            @endphp
                                                            @foreach ($order->products as $key => $product)
                                                                @for ($i = 0; $i < $product->pivot->quantity; $i++)
                                                                    <div class="col-4">
                                                                        <div
                                                                            class="card text-dark bg-white shadow bg-body rounded my-2 p-2">
                                                                            <h4 class="m-0">{{ __('Name') }}:
                                                                                {{ $order->customer->user->name }}</h4>
                                                                            <h4 class="m-0">{{ __('Order Id') }}:
                                                                                #{{ $order->prefix . $order->order_code }}
                                                                            </h4>
                                                                            <h4 class="m-0">{{ __('Date') }}:
                                                                                {{ $order->created_at->format('M d, Y h:i A') }}
                                                                            </h4>
                                                                            <h4 class="m-0">{{ __('Title') }}:
                                                                                {{ $product->name }}
                                                                            </h4>
                                                                            <h4 class="m-0">{{ __('Item') }}:
                                                                                {{ $r . '/' . $quantity }}</h4>
                                                                        </div>
                                                                    </div>
                                                                @endfor
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-dark"
                                                            data-dismiss="modal">{{ __('Close') }}</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <th class="py-2 {{ session()->get('local') == 'ar' ? 'text-right' : '' }}">
                                        {{ __('Additional_Instruction') }}:</th>
                                    <td class="py-2">{{ $order->instruction ?? __('N/A') }}</td>
                                </tr>

                                <tr>
                                    <th class="py-2 {{ session()->get('local') == 'ar' ? 'text-right' : '' }}">
                                        {{ __('Additional_Service') }}</th>
                                    <td class="py-2">
                                        <button type="button" data-target="#additional" data-toggle="modal"
                                            class="btn btn-primary">
                                            {{ __('Additional_Services') }} <span
                                                class="badge badge-dark m-0">{{ $order->additionals->count() }}</span>
                                        </button>
                                        <!-- Modal -->
                                        <div class="modal fade" id="additional">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content" style="background: #f6f6f6;">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="staticBackdropLabel">
                                                            {{ __('All') . ' ' . __('Order') . ' ' . __('Labels') }}
                                                        </h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <table
                                                            class="table table-bordered table-striped verticle-middle table-responsive-sm">
                                                            <tr>
                                                                <th>{{ __('Title') }}</th>
                                                                <th>{{ __('Description') }}</th>
                                                                <th>{{ __('Price') }}</th>
                                                            </tr>
                                                            @foreach ($order->additionals as $additional)
                                                                <tr>
                                                                    <td>{{ $additional->title }}</td>
                                                                    <td>{{ $additional->description }}</td>
                                                                    <td>{{ $additional->price }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </table>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-dark"
                                                            data-dismiss="modal">{{ __('Close') }}</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <th class="py-2 {{ session()->get('local') == 'ar' ? 'text-right' : '' }}">
                                        {{ __('Rating') }}</th>
                                    <td class="py-2">
                                        @php
                                            $rate = $order->rating ? $order->rating->rating : 0;
                                        @endphp
                                        <i class="fas fa-star {{ $rate >= 1 ? 'rate' : 'unrate' }}"></i>
                                        <i class="fas fa-star {{ $rate >= 2 ? 'rate' : 'unrate' }}"></i>
                                        <i class="fas fa-star {{ $rate >= 3 ? 'rate' : 'unrate' }}"></i>
                                        <i class="fas fa-star {{ $rate >= 4 ? 'rate' : 'unrate' }}"></i>
                                        <i class="fas fa-star {{ $rate == 5 ? 'rate' : 'unrate' }}"></i>

                                        <br>
                                        {{ $order->rating ? $order->rating->content : null }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .rate {
                color: rgb(255, 166, 0)
            }

            .unrate {
                color: rgb(136, 136, 136)
            }
        </style>
    </div>
@endsection

@push('scripts')
    <script>
        $('.paid-confirm').on('click', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#00B894',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Paid it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            })
        });

        // Dynamic product management
        let productIndex = 1;
        
        $('#addProduct').on('click', function() {
            const newRow = `
                <div class="product-row card mb-3 p-3">
                    <div class="row">
                        <div class="col-md-5">
                            <label>{{ __('Product') }}</label>
                            <select name="products[${productIndex}][product_id]" class="form-control product-select" required>
                                <option value="">{{ __('Select Product') }}</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                                        {{ $product->name }} ({{ currencyPosition($product->price) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>{{ __('Quantity') }}</label>
                            <input type="number" name="products[${productIndex}][quantity]" class="form-control quantity-input" min="1" value="1" required>
                        </div>
                        <div class="col-md-3">
                            <label>{{ __('Price') }}</label>
                            <input type="number" step="0.01" name="products[${productIndex}][price]" class="form-control price-input" required>
                        </div>
                        <div class="col-md-1">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-danger btn-block remove-product">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            $('#productsList').append(newRow);
            productIndex++;
            updateRemoveButtons();
        });

        $(document).on('click', '.remove-product', function() {
            $(this).closest('.product-row').remove();
            updateRemoveButtons();
            calculateTotal();
        });

        $(document).on('change', '.product-select', function() {
            const price = $(this).find(':selected').data('price');
            $(this).closest('.row').find('.price-input').val(price);
            calculateTotal();
        });

        $(document).on('input', '.quantity-input, .price-input, #deliveryCharge', function() {
            calculateTotal();
        });

        function updateRemoveButtons() {
            const rowCount = $('.product-row').length;
            if (rowCount <= 1) {
                $('.remove-product').hide();
            } else {
                $('.remove-product').show();
            }
        }

        function calculateTotal() {
            let subtotal = 0;
            
            $('.product-row').each(function() {
                const quantity = parseFloat($(this).find('.quantity-input').val()) || 0;
                const price = parseFloat($(this).find('.price-input').val()) || 0;
                subtotal += (quantity * price);
            });

            const deliveryCharge = parseFloat($('#deliveryCharge').val()) || 0;
            const total = subtotal + deliveryCharge;

            $('#subtotal').text(subtotal.toFixed(2));
            $('#totalAmount').text(total.toFixed(2));
        }

        // Initial calculation
        calculateTotal();
    </script>
@endpush
