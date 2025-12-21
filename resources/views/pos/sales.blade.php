@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card border-0 mb-3 shadow rounded-12">
                    <div class="card-header py-3 d-flex align-items-center justify-content-between">
                        <h2 class="card-title m-0">{{ __('Orders') }}</h2>
                        @role('store|root|admin')
                                <form class="d-flex justify-content-end" action="{{ route('order.index') }}" method="GET">
                                    <select class="form-control mr-1" name="status" style="width: 200px;height: 43px;">
                                        <option value="">{{ __('All') }}</option>
                                        @foreach ($orderStatus as $status)
                                            <option value="{{ $status->value }}"
                                                {{ $status->value == \request('status') ? 'selected' : '' }}>{{ __($status->value) }}</option>
                                        @endforeach
                                    </select>

                                    <button class="btn btn-primary mx-md-0 mx-2"> <i  class="fa fa-search"></i> </button>
                                </form>
                        @endrole
                    </div>
                    <div class="card-body pt-2">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="myTable">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">{{ __('Order_ID') }}</th>
                                        <th scope="col">{{ __('Order_By') }}</th>
                                        <th scope="col">{{ __('Pickup_Date') }}</th>
                                        <th scope="col">{{ __('Amount') }}</th>
                                        <th scope="col">{{ __('Order_Status') }}</th>
                                        @canany(['order.show', 'order.print.invioce'])
                                            <th scope="col" class="px-2">{{ __('Actions') }}</th>
                                        @endcanany
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($orders as $order)
                                        <tr>
                                            <td>{{ $order->prefix . $order->order_code }}</td>
                                            <td>
                                                {{ $order->customer->user?->name ?? 'N/A' }}</td>
                                            <td>
                                                {{ $order->pick_date }}
                                            </td>
                                            <td>{{ currencyPosition($order->total_amount) }}</td>
                                            <td>{{ ($order->order_status) }}</td>
                                            @canany(['order.show', 'order.print.invioce'])
                                                <td class="p-1 ">

                                                    @can('order.show')
                                                        <a href="{{ route('order.show', $order->id) }}"
                                                            class="btn btn-primary btn-sm mb-1">
                                                            <i class="fa fa-eye"></i>
                                                        </a>
                                                    @endcan

                                                    @can('order.print.invioce')
                                                        <a class="btn btn-danger btn-sm mb-1"
                                                            href="{{ route('order.print.invioce', $order->id) }}"
                                                            target="_blank"><i class="fas fa-print"></i>
                                                        </a>
                                                    @endcan
                                                </td>
                                            @endcanany
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
