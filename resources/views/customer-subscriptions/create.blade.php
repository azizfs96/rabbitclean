@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">{{ __('Assign Subscription to Customer') }}</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('customer-subscription.assign') }}" method="POST">
                        @csrf
                        
                        <div class="form-group">
                            <label for="customer_id">{{ __('Select Customer') }} <span class="text-danger">*</span></label>
                            <select class="form-control select2 @error('customer_id') is-invalid @enderror" 
                                    id="customer_id" name="customer_id" required>
                                <option value="">{{ __('Select a customer') }}</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">
                                        {{ $customer->user->name ?? 'N/A' }} - {{ $customer->user->mobile ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">{{ __('Only customers without active subscriptions are shown') }}</small>
                        </div>

                        <div class="form-group">
                            <label for="subscription_id">{{ __('Select Subscription Plan') }} <span class="text-danger">*</span></label>
                            <select class="form-control @error('subscription_id') is-invalid @enderror" 
                                    id="subscription_id" name="subscription_id" required>
                                <option value="">{{ __('Select a plan') }}</option>
                                @foreach($subscriptions as $subscription)
                                    <option value="{{ $subscription->id }}" data-price="{{ $subscription->price }}">
                                        {{ $subscription->name }} - {{ number_format($subscription->price, 2) }} SAR 
                                        ({{ $subscription->validity }} {{ $subscription->validity_type?->value }})
                                    </option>
                                @endforeach
                            </select>
                            @error('subscription_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="payment_gateway">{{ __('Payment Method') }}</label>
                            <select class="form-control @error('payment_gateway') is-invalid @enderror" 
                                    id="payment_gateway" name="payment_gateway">
                                <option value="cash">{{ __('Cash') }}</option>
                                <option value="bank_transfer">{{ __('Bank Transfer') }}</option>
                            </select>
                            @error('payment_gateway')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Plan Preview -->
                        <div id="plan-preview" class="card bg-light d-none">
                            <div class="card-body">
                                <h5>{{ __('Plan Details') }}</h5>
                                <div id="plan-details"></div>
                            </div>
                        </div>

                        <hr>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check"></i> {{ __('Assign Subscription') }}
                            </button>
                            <a href="{{ route('customer-subscription.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> {{ __('Back') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const subscriptions = @json($subscriptions);

document.getElementById('subscription_id').addEventListener('change', function() {
    const selectedId = this.value;
    const preview = document.getElementById('plan-preview');
    const details = document.getElementById('plan-details');
    
    if (selectedId) {
        const plan = subscriptions.find(s => s.id == selectedId);
        if (plan) {
            details.innerHTML = `
                <p><strong>{{ __('Price') }}:</strong> ${parseFloat(plan.price).toFixed(2)} SAR</p>
                <p><strong>{{ __('Validity') }}:</strong> ${plan.validity} ${plan.validity_type}</p>
                <p><strong>{{ __('Credits') }}:</strong></p>
                <ul>
                    <li>Laundry: ${plan.laundry_credits || 0}</li>
                    <li>Clothing: ${plan.clothing_credits || 0}</li>
                    <li>Delivery: ${plan.delivery_credits || 0}</li>
                    <li>Towel: ${plan.towel_credits || 0}</li>
                    <li>Special: ${plan.special_credits || 0}</li>
                </ul>
            `;
            preview.classList.remove('d-none');
        }
    } else {
        preview.classList.add('d-none');
    }
});
</script>
@endpush
@endsection
