@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title float-left my-1">{{ __('Customer Subscriptions') }}</h2>
                    <div class="w-100 text-right">
                        <a href="{{ route('subscription.index') }}" class="btn btn-info my-md-0 my-1">
                            <i class="fas fa-list"></i> {{ __('Manage Plans') }}
                        </a>
                        <a href="{{ route('customer-subscription.create') }}" class="btn btn-primary my-md-0 my-1">
                            <i class="fas fa-plus"></i> {{ __('Assign Subscription') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <form action="{{ route('customer-subscription.index') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="search" 
                                           value="{{ request('search') }}" placeholder="{{ __('Search by name or phone') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <select class="form-control" name="status">
                                        <option value="">{{ __('All Status') }}</option>
                                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>{{ __('Expired') }}</option>
                                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> {{ __('Filter') }}
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped verticle-middle table-responsive-sm">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">{{ __('Customer') }}</th>
                                    <th scope="col">{{ __('Plan') }}</th>
                                    <th scope="col">{{ __('Credits Remaining') }}</th>
                                    <th scope="col">{{ __('Period') }}</th>
                                    <th scope="col">{{ __('Status') }}</th>
                                    <th scope="col">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($customerSubscriptions as $cs)
                                <tr>
                                    <td>{{ $cs->id }}</td>
                                    <td>
                                        <strong>{{ $cs->customer->user->name ?? 'N/A' }}</strong>
                                        <br><small class="text-muted">{{ $cs->customer->user->mobile ?? '' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary">{{ $cs->subscription->name ?? 'N/A' }}</span>
                                        <br><small>{{ number_format($cs->amount_paid, 2) }} SAR</small>
                                    </td>
                                    <td>
                                        <small>
                                            <span class="badge badge-info" title="Laundry">L: {{ $cs->laundry_credits_remaining }}</span>
                                            <span class="badge badge-primary" title="Clothing">C: {{ $cs->clothing_credits_remaining }}</span>
                                            <span class="badge badge-warning" title="Delivery">D: {{ $cs->delivery_credits_remaining }}</span>
                                            <span class="badge badge-secondary" title="Towel">T: {{ $cs->towel_credits_remaining }}</span>
                                            <span class="badge badge-dark" title="Special">S: {{ $cs->special_credits_remaining }}</span>
                                        </small>
                                    </td>
                                    <td>
                                        <small>
                                            {{ $cs->start_date?->format('d M Y') }}<br>
                                            to {{ $cs->end_date?->format('d M Y') }}
                                            @if($cs->isActive())
                                                <br><span class="text-success">({{ $cs->daysRemaining() }} days left)</span>
                                            @endif
                                        </small>
                                    </td>
                                    <td>
                                        @switch($cs->status)
                                            @case('active')
                                                <span class="badge badge-success">{{ __('Active') }}</span>
                                                @break
                                            @case('expired')
                                                <span class="badge badge-danger">{{ __('Expired') }}</span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge badge-warning">{{ __('Cancelled') }}</span>
                                                @break
                                            @case('pending')
                                                <span class="badge badge-secondary">{{ __('Pending') }}</span>
                                                @break
                                        @endswitch
                                        @if($cs->auto_renew)
                                            <br><small class="text-info"><i class="fas fa-sync"></i> Auto-renew</small>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('customer-subscription.show', $cs->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">{{ __('No subscriptions found') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $customerSubscriptions->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
