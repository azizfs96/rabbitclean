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
                    @if(in_array($customerSubscription->status, ['active', 'expired']))
                    <div class="row">
                        <div class="col-12">
                            <form action="{{ route('customer-subscription.renew', $customerSubscription->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Are you sure you want to renew this subscription? This will add credits from the plan.') }}')">
                                @csrf
                                <button type="submit" class="btn btn-success mb-2">
                                    <i class="fas fa-sync"></i> {{ __('Renew Subscription') }}
                                </button>
                            </form>

                            @if($customerSubscription->status === 'active')
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
                            @endif
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
                    <!-- NEW: Simplified Credit Balance (Primary) -->
                    <div class="text-center mb-4 p-4 bg-gradient-primary rounded">
                        <h2 class="text-white mb-1">{{ number_format($customerSubscription->credit_balance ?? 0, 2) }} SAR</h2>
                        <p class="text-white-50 mb-0">{{ __('Available Credit Balance') }}</p>
                        @if($customerSubscription->total_credits_used > 0)
                            <small class="text-white-50">{{ __('Used') }}: {{ number_format($customerSubscription->total_credits_used, 2) }} SAR</small>
                        @endif
                    </div>

                    <!-- NEW: Quick Credit Adjustment (Simplified) -->
                    <div class="mb-4 p-3 bg-light rounded">
                        <h6>{{ __('Quick Credit Adjustment') }}</h6>
                        <form action="{{ route('customer-subscription.adjust-credits', $customerSubscription->id) }}" method="POST" class="d-flex align-items-end">
                            @csrf
                            <input type="hidden" name="credit_type" value="balance">
                            <div class="form-group mr-2 mb-0">
                                <label class="small">{{ __('Amount (SAR)') }}</label>
                                <input type="number" step="0.01" name="amount" class="form-control" min="0.01" required style="width: 120px;">
                            </div>
                            <div class="form-group mr-2 mb-0">
                                <label class="small">{{ __('Action') }}</label>
                                <select name="adjustment_type" class="form-control">
                                    <option value="add">{{ __('Add') }}</option>
                                    <option value="deduct">{{ __('Deduct') }}</option>
                                </select>
                            </div>
                            <div class="form-group mr-2 mb-0 flex-grow-1">
                                <label class="small">{{ __('Notes') }}</label>
                                <input type="text" name="notes" class="form-control" placeholder="{{ __('Reason') }}">
                            </div>
                            <button type="submit" class="btn btn-primary mb-0">
                                <i class="fas fa-save"></i>
                            </button>
                        </form>
                    </div>
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
