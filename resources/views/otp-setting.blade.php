@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="row align-items-center" style="min-height: 80vh">
            <div class="col-md-8 col-lg-7 col-sm-12 m-auto">
                <form action="{{ route('otpSetting.update') }}" method="POST">
                    @csrf
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary py-2">
                            <h3 class="text-white m-0">{{ __('OTP Settings') }}</h3>
                        </div>
                        <div class="card-body pb-3">
                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="mb-1 text-dark font-weight-bold">
                                            {{ __('OTP Expiry Time') }} 
                                            <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <input type="number" name="otp_expiry_minutes" class="form-control"
                                                value="{{ $settings?->otp_expiry_minutes ?? 5 }}" 
                                                min="1" max="60" required>
                                            <div class="input-group-append">
                                                <span class="input-group-text">{{ __('minutes') }}</span>
                                            </div>
                                        </div>
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle"></i>
                                            {{ __('How long the OTP code remains valid after being sent (1-60 minutes)') }}
                                        </small>
                                        @error('otp_expiry_minutes')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="mb-1 text-dark font-weight-bold">
                                            {{ __('Maximum Verification Attempts') }} 
                                            <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <input type="number" name="otp_max_attempts" class="form-control"
                                                value="{{ $settings?->otp_max_attempts ?? 5 }}" 
                                                min="1" max="10" required>
                                            <div class="input-group-append">
                                                <span class="input-group-text">{{ __('attempts') }}</span>
                                            </div>
                                        </div>
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle"></i>
                                            {{ __('Maximum number of times a user can try to verify an OTP before it becomes invalid (1-10 attempts)') }}
                                        </small>
                                        @error('otp_max_attempts')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="mb-1 text-dark font-weight-bold">
                                            {{ __('Resend OTP Delay') }} 
                                            <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <input type="number" name="otp_resend_delay_seconds" class="form-control"
                                                value="{{ $settings?->otp_resend_delay_seconds ?? 60 }}" 
                                                min="30" max="300" required>
                                            <div class="input-group-append">
                                                <span class="input-group-text">{{ __('seconds') }}</span>
                                            </div>
                                        </div>
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle"></i>
                                            {{ __('Wait time before user can request a new OTP (30-300 seconds). Prevents spam and abuse.') }}
                                        </small>
                                        @error('otp_resend_delay_seconds')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card bg-light border-0">
                                        <div class="card-body">
                                            <h5 class="mb-3">{{ __('Current Settings') }}</h5>
                                            
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="font-weight-bold">{{ __('OTP Valid For:') }}</span>
                                                    <span class="badge badge-primary badge-pill">
                                                        {{ $settings?->otp_expiry_minutes ?? 5 }} {{ __('minutes') }}
                                                    </span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="font-weight-bold">{{ __('Max Attempts:') }}</span>
                                                    <span class="badge badge-warning badge-pill">
                                                        {{ $settings?->otp_max_attempts ?? 5 }} {{ __('times') }}
                                                    </span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="font-weight-bold">{{ __('Resend After:') }}</span>
                                                    <span class="badge badge-info badge-pill">
                                                        {{ $settings?->otp_resend_delay_seconds ?? 60 }} {{ __('seconds') }}
                                                    </span>
                                                </div>
                                            </div>

                                            <hr>

                                            <h6 class="mb-2">{{ __('Recommended Settings') }}</h6>
                                            
                                           
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('root') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> {{ __('Back') }}
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> {{ __('Save Settings') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .badge-pill {
            font-size: 0.9rem;
            padding: 0.4rem 0.8rem;
        }
    </style>
@endpush
