@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">{{ __('Edit Subscription Plan') }}: {{ $subscription->name }}</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('subscription.update', $subscription->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- Basic Info -->
                            <div class="col-md-6">
                                <h5 class="mb-3">{{ __('Basic Information') }}</h5>
                                
                                <div class="form-group">
                                    <label for="name">{{ __('Name (English)') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $subscription->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="name_ar">{{ __('Name (Arabic)') }}</label>
                                    <input type="text" class="form-control @error('name_ar') is-invalid @enderror" 
                                           id="name_ar" name="name_ar" value="{{ old('name_ar', $subscription->name_ar) }}" dir="rtl">
                                    @error('name_ar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="description">{{ __('Description (English)') }}</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="3">{{ old('description', $subscription->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="description_ar">{{ __('Description (Arabic)') }}</label>
                                    <textarea class="form-control @error('description_ar') is-invalid @enderror" 
                                              id="description_ar" name="description_ar" rows="3" dir="rtl">{{ old('description_ar', $subscription->description_ar) }}</textarea>
                                    @error('description_ar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Pricing & Validity -->
                            <div class="col-md-6">
                                <h5 class="mb-3">{{ __('Pricing & Validity') }}</h5>
                                
                                <div class="form-group">
                                    <label for="price">{{ __('Price (SAR)') }} <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" 
                                           id="price" name="price" value="{{ old('price', $subscription->price) }}" required>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="validity">{{ __('Validity') }} <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control @error('validity') is-invalid @enderror" 
                                                   id="validity" name="validity" value="{{ old('validity', $subscription->validity) }}" min="1" required>
                                            @error('validity')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="validity_type">{{ __('Validity Type') }} <span class="text-danger">*</span></label>
                                            <select class="form-control @error('validity_type') is-invalid @enderror" 
                                                    id="validity_type" name="validity_type" required>
                                                @foreach($validityTypes as $type)
                                                    <option value="{{ $type->value }}" {{ old('validity_type', $subscription->validity_type?->value) == $type->value ? 'selected' : '' }}>
                                                        {{ ucfirst($type->value) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('validity_type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="color">{{ __('Color') }}</label>
                                    <input type="color" class="form-control @error('color') is-invalid @enderror" 
                                           id="color" name="color" value="{{ old('color', $subscription->color ?? '#007bff') }}" style="height: 40px;">
                                    @error('color')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', $subscription->is_active) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_active">{{ __('Active') }}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="is_featured" name="is_featured" value="1" {{ old('is_featured', $subscription->is_featured) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_featured">{{ __('Featured Plan') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Credits -->
                        <h5 class="mb-3">{{ __('Credits Allocation') }}</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="laundry_credits">{{ __('Laundry Credits') }}</label>
                                    <input type="number" class="form-control" id="laundry_credits" name="laundry_credits" 
                                           value="{{ old('laundry_credits', $subscription->laundry_credits) }}" min="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="clothing_credits">{{ __('Clothing Credits') }}</label>
                                    <input type="number" class="form-control" id="clothing_credits" name="clothing_credits" 
                                           value="{{ old('clothing_credits', $subscription->clothing_credits) }}" min="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="delivery_credits">{{ __('Delivery Credits') }}</label>
                                    <input type="number" class="form-control" id="delivery_credits" name="delivery_credits" 
                                           value="{{ old('delivery_credits', $subscription->delivery_credits) }}" min="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="towel_credits">{{ __('Towel Credits') }}</label>
                                    <input type="number" class="form-control" id="towel_credits" name="towel_credits" 
                                           value="{{ old('towel_credits', $subscription->towel_credits) }}" min="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="special_credits">{{ __('Special Credits') }}</label>
                                    <input type="number" class="form-control" id="special_credits" name="special_credits" 
                                           value="{{ old('special_credits', $subscription->special_credits) }}" min="0">
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Features -->
                        <h5 class="mb-3">{{ __('Features') }}</h5>
                        <div id="features-container">
                            @forelse($subscription->features ?? [] as $feature)
                            <div class="form-group feature-row">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="features[]" value="{{ $feature }}" placeholder="{{ __('Enter feature') }}">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-danger remove-feature"><i class="fas fa-times"></i></button>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="form-group feature-row">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="features[]" placeholder="{{ __('Enter feature') }}">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-danger remove-feature"><i class="fas fa-times"></i></button>
                                    </div>
                                </div>
                            </div>
                            @endforelse
                        </div>
                        <button type="button" class="btn btn-secondary btn-sm mb-3" id="add-feature">
                            <i class="fas fa-plus"></i> {{ __('Add Feature') }}
                        </button>

                        <hr>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> {{ __('Update Plan') }}
                            </button>
                            <a href="{{ route('subscription.index') }}" class="btn btn-secondary">
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
document.getElementById('add-feature').addEventListener('click', function() {
    const container = document.getElementById('features-container');
    const newRow = document.createElement('div');
    newRow.className = 'form-group feature-row';
    newRow.innerHTML = `
        <div class="input-group">
            <input type="text" class="form-control" name="features[]" placeholder="{{ __('Enter feature') }}">
            <div class="input-group-append">
                <button type="button" class="btn btn-danger remove-feature"><i class="fas fa-times"></i></button>
            </div>
        </div>
    `;
    container.appendChild(newRow);
});

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-feature') || e.target.closest('.remove-feature')) {
        const row = e.target.closest('.feature-row');
        if (document.querySelectorAll('.feature-row').length > 1) {
            row.remove();
        }
    }
});
</script>
@endpush
@endsection
