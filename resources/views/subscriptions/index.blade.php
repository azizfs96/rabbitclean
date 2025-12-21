@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title float-left my-1">{{ __('Subscription Plans') }}</h2>
                    <div class="w-100 text-right">
                        <a href="{{ route('customer-subscription.index') }}" class="btn btn-info my-md-0 my-1">
                            <i class="fas fa-users"></i> {{ __('Customer Subscriptions') }}
                        </a>
                        <a href="{{ route('subscription.create') }}" class="btn btn-primary my-md-0 my-1">
                            <i class="fas fa-plus"></i> {{ __('Add New Plan') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped verticle-middle table-responsive-sm" id="myTable">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">{{ __('Name') }}</th>
                                    <th scope="col">{{ __('Price') }} (SAR)</th>
                                    <th scope="col">{{ __('Validity') }}</th>
                                    <th scope="col">{{ __('Credits') }}</th>
                                    <th scope="col">{{ __('Featured') }}</th>
                                    <th scope="col">{{ __('Status') }}</th>
                                    <th scope="col">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($subscriptions as $subscription)
                                <tr>
                                    <td>{{ $subscription->sort_order }}</td>
                                    <td>
                                        <strong>{{ $subscription->name }}</strong>
                                        @if($subscription->name_ar)
                                            <br><small class="text-muted">{{ $subscription->name_ar }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-success" style="font-size: 14px;">
                                            {{ number_format($subscription->price, 2) }} SAR
                                        </span>
                                    </td>
                                    <td>{{ $subscription->validity }} {{ $subscription->validity_type?->value }}</td>
                                    <td>
                                        <small>
                                            <span class="badge badge-info">L: {{ $subscription->laundry_credits }}</span>
                                            <span class="badge badge-primary">C: {{ $subscription->clothing_credits }}</span>
                                            <span class="badge badge-warning">D: {{ $subscription->delivery_credits }}</span>
                                            <span class="badge badge-secondary">T: {{ $subscription->towel_credits }}</span>
                                            <span class="badge badge-dark">S: {{ $subscription->special_credits }}</span>
                                        </small>
                                    </td>
                                    <td>
                                        @if($subscription->is_featured)
                                            <span class="badge badge-warning"><i class="fas fa-star"></i> Featured</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <label class="switch">
                                            <a href="{{ route('subscription.toggle', $subscription->id) }}">
                                                <input {{ $subscription->is_active ? 'checked':'' }} type="checkbox">
                                                <span class="slider round"></span>
                                            </a>
                                        </label>
                                    </td>
                                    <td>
                                        <a href="{{ route('subscription.edit', $subscription->id) }}" class="btn btn-sm btn-primary">
                                            <i class="far fa-edit"></i>
                                        </a>
                                        <form action="{{ route('subscription.destroy', $subscription->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="far fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $subscriptions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
