@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="row py-4">
            <div class="col-md-10 col-lg-8 col-sm-12 m-auto">
                {{-- Success/Error Messages --}}
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card shadow rounded-12 border-0">
                    <div class="card-header bg-primary py-3">
                        <h3 class="text-white m-0">
                            <i class="fas fa-bell mr-2"></i>{{ __('Firebase_Cloud_Messaging') }}
                        </h3>
                    </div>
                    <div class="card-body">
                        {{-- Current Status Section --}}
                        <div class="mb-4">
                            <h5 class="mb-3">
                                <i class="fas fa-info-circle text-info mr-2"></i>{{ __('Current Status') }}
                            </h5>
                            @if($hasCredentials && $projectInfo)
                                <div class="alert alert-success">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-check-circle fa-2x mr-3"></i>
                                        <div>
                                            <strong>{{ __('Firebase Connected') }}</strong>
                                            <div class="mt-1 text-sm">
                                                <span class="badge bg-dark">{{ __('Project ID') }}: {{ $projectInfo['project_id'] }}</span>
                                                <span class="badge bg-secondary ml-1">{{ __('Type') }}: {{ $projectInfo['type'] }}</span>
                                            </div>
                                            <div class="text-muted small mt-1">{{ $projectInfo['client_email'] }}</div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-exclamation-triangle fa-2x mr-3"></i>
                                        <div>
                                            <strong>{{ __('No Firebase Credentials') }}</strong>
                                            <div class="text-muted small mt-1">
                                                {{ __('Push notifications will not work until you upload Firebase service account credentials.') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <hr>

                        {{-- Instructions Section --}}
                        <div class="mb-4">
                            <h5 class="mb-3">
                                <i class="fas fa-book mr-2 text-primary"></i>{{ __('How to Get Firebase Credentials') }}
                            </h5>
                            <div class="alert alert-light border">
                                <ol class="mb-0">
                                    <li class="mb-2">Go to <a href="https://console.firebase.google.com" target="_blank" class="text-primary">Firebase Console</a></li>
                                    <li class="mb-2">Select your project (e.g., <strong>laundry-49a80</strong>)</li>
                                    <li class="mb-2">Click <strong>Project Settings</strong> (gear icon) → <strong>Service Accounts</strong></li>
                                    <li class="mb-2">Click <strong>"Generate new private key"</strong></li>
                                    <li class="mb-0">Download the JSON file and upload it below</li>
                                </ol>
                            </div>
                        </div>

                        <hr>

                        {{-- Upload Form --}}
                        <form action="{{ route('fcm.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <h5 class="mb-3">
                                <i class="fas fa-upload mr-2 text-success"></i>{{ __('Upload Firebase Credentials') }}
                            </h5>
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    {{ __('Firebase Service Account JSON File') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="file" 
                                       name="firebase_credentials" 
                                       class="form-control @error('firebase_credentials') is-invalid @enderror" 
                                       accept=".json"
                                       required>
                                <div class="form-text">
                                    {{ __('Upload the JSON file downloaded from Firebase Console. Must be a service account type.') }}
                                </div>
                                @error('firebase_credentials')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-cloud-upload-alt mr-2"></i>{{ __('Upload Credentials') }}
                            </button>
                        </form>

                        @if($hasCredentials)
                            <hr class="my-4">
                            
                            {{-- Actions for existing credentials --}}
                            <h5 class="mb-3">
                                <i class="fas fa-cogs mr-2 text-secondary"></i>{{ __('Manage Credentials') }}
                            </h5>
                            <div class="d-flex gap-2">
                                <a href="{{ route('fcm.download') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-download mr-2"></i>{{ __('Download Current File') }}
                                </a>
                                <form action="{{ route('fcm.delete') }}" method="POST" 
                                      onsubmit="return confirm('{{ __('Are you sure you want to delete Firebase credentials? Push notifications will stop working.') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="fas fa-trash mr-2"></i>{{ __('Delete Credentials') }}
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Info Card --}}
                <div class="card border-info mt-4">
                    <div class="card-body">
                        <h6 class="text-info mb-2">
                            <i class="fas fa-lightbulb mr-2"></i>{{ __('Important Notes') }}
                        </h6>
                        <ul class="mb-0 small text-muted">
                            <li>The service account JSON file contains sensitive private keys - keep it secure</li>
                            <li>Make sure the Firebase project matches the one configured in the mobile app</li>
                            <li>Your mobile app uses project: <strong>laundry-49a80</strong></li>
                            <li>Push notifications will only work after valid credentials are uploaded</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
