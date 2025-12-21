@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <!-- Subscription Details -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('Subscription Details') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-6"><strong>{{ __('Customer') }}:</strong></div>
                        <div class="col-6">{{ $customerSubscription->customer->user->name ?? 'N/A' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6"><strong>{{ __('Phone') }}:</strong></div>
                        <div class="col-6">{{ $customerSubscription->customer->user->mobile ?? 'N/A' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6"><strong>{{ __('Plan') }}:</strong></div>
                        <div class="col-6">
                            <span class="badge badge-primary">{{ $customerSubscription->subscription->name ?? 'N/A' }}</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6"><strong>{{ __('Amount Paid') }}:</strong></div>
                        <div class="col-6">{{ number_format($customerSubscription->amount_paid, 2) }} SAR</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6"><strong>{{ __('Start Date') }}:</strong></div>
                        <div class="col-6">{{ $customerSubscription->start_date?->format('d M Y') }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6"><strong>{{ __('End Date') }}:</strong></div>
                        <div class="col-6">{{ $customerSubscription->end_date?->format('d M Y') }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6"><strong>{{ __('Days Remaining') }}:</strong></div>
                        <div class="col-6">
                            @if($customerSubscription->isActive())
                                <span class="text-success">{{ $customerSubscription->daysRemaining() }} days</span>
                            @else
                                <span class="text-danger">Expired</span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6"><strong>{{ __('Status') }}:</strong></div>
                        <div class="col-6">
                            @switch($customerSubscription->status)
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
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6"><strong>{{ __('Auto Renew') }}:</strong></div>
                        <div class="col-6">
                            @if($customerSubscription->auto_renew)
                                <span class="badge badge-info"><i class="fas fa-check"></i> Yes</span>
                            @else
                                <span class="badge badge-secondary">No</span>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <!-- Actions -->
                    @if($customerSubscription->status === 'active')
                    <div class="row">
                        <div class="col-12">
                            <form action="{{ route('customer-subscription.extend', $customerSubscription->id) }}" method="POST" class="d-inline">
                                @csrf
                                <div class="input-group mb-2">
                                    <input type="number" name="days" class="form-control" placeholder="Days" min="1" max="365" required style="max-width: 100px;">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-info">
                                            <i class="fas fa-calendar-plus"></i> {{ __('Extend') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                            
                            <form action="{{ route('customer-subscription.cancel', $customerSubscription->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this subscription?')">
                                @csrf
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-times"></i> {{ __('Cancel Subscription') }}
                                </button>
                            </form>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Credits Balance -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('Credits Balance') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4 mb-3">
                            <div class="p-3 bg-info text-white rounded">
                                <h3>{{ $customerSubscription->laundry_credits_remaining }}</h3>
                                <small>{{ __('Laundry') }}</small>
                            </div>
                        </div>
                        <div class="col-4 mb-3">
                            <div class="p-3 bg-primary text-white rounded">
                                <h3>{{ $customerSubscription->clothing_credits_remaining }}</h3>
                                <small>{{ __('Clothing') }}</small>
                            </div>
                        </div>
                        <div class="col-4 mb-3">
                            <div class="p-3 bg-warning text-dark rounded">
                                <h3>{{ $customerSubscription->delivery_credits_remaining }}</h3>
                                <small>{{ __('Delivery') }}</small>
                            </div>
                        </div>
                        <div class="col-4 mb-3">
                            <div class="p-3 bg-secondary text-white rounded">
                                <h3>{{ $customerSubscription->towel_credits_remaining }}</h3>
                                <small>{{ __('Towel') }}</small>
                            </div>
                        </div>
                        <div class="col-4 mb-3">
                            <div class="p-3 bg-dark text-white rounded">
                                <h3>{{ $customerSubscription->special_credits_remaining }}</h3>
                                <small>{{ __('Special') }}</small>
                            </div>
                        </div>
                        <div class="col-4 mb-3">
                            <div class="p-3 bg-success text-white rounded">
                                <h3>{{ $customerSubscription->getTotalCreditsRemaining() }}</h3>
                                <small>{{ __('Total') }}</small>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Adjust Credits Form -->
                    <h5>{{ __('Adjust Credits') }}</h5>
                    <form action="{{ route('customer-subscription.adjust-credits', $customerSubscription->id) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{ __('Credit Type') }}</label>
                                    <select name="credit_type" class="form-control" required>
                                        <option value="laundry">{{ __('Laundry') }}</option>
                                        <option value="clothing">{{ __('Clothing') }}</option>
                                        <option value="delivery">{{ __('Delivery') }}</option>
                                        <option value="towel">{{ __('Towel') }}</option>
                                        <option value="special">{{ __('Special') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>{{ __('Amount') }}</label>
                                    <input type="number" name="amount" class="form-control" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>{{ __('Action') }}</label>
                                    <select name="adjustment_type" class="form-control" required>
                                        <option value="add">{{ __('Add') }}</option>
                                        <option value="deduct">{{ __('Deduct') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-save"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Notes') }}</label>
                            <input type="text" name="notes" class="form-control" placeholder="{{ __('Reason for adjustment') }}">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Credit History -->
    <div class="row mt-4">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('Credit Transaction History') }}</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Credit Type') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Balance') }}</th>
                                    <th>{{ __('Reference') }}</th>
                                    <th>{{ __('Notes') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($creditHistory as $transaction)
                                <tr>
                                    <td>{{ $transaction->created_at->format('d M Y H:i') }}</td>
                                    <td>
                                        @if($transaction->transaction_type === 'credit')
                                            <span class="badge badge-success">Credit</span>
                                        @else
                                            <span class="badge badge-danger">Debit</span>
                                        @endif
                                    </td>
                                    <td>{{ ucfirst($transaction->credit_type) }}</td>
                                    <td>
                                        @if($transaction->transaction_type === 'credit')
                                            <span class="text-success">+{{ $transaction->amount }}</span>
                                        @else
                                            <span class="text-danger">-{{ $transaction->amount }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $transaction->balance_before }} → {{ $transaction->balance_after }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $transaction->reference_type ?? '-')) }}</td>
                                    <td>{{ $transaction->notes ?? '-' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">{{ __('No transactions found') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mb-4">
    <a href="{{ route('customer-subscription.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> {{ __('Back to List') }}
    </a>
</div>
@endsection
