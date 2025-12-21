@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-5">
        <div class="row">
            <div class="col-xl-6 col-lg-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title m-0">{{__('Test Msegat SMS')}} 
                            <button class="btn btn-info btn-sm float-right" onclick="checkBalance()">
                                <i class="fas fa-wallet"></i> Check Balance
                            </button>
                        </h2>
                    </div>

                    <div class="card-body">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> SMS Test</h6>
                            <p class="mb-0">Use this form to test your Msegat SMS configuration. Make sure you have configured your API credentials first.</p>
                        </div>

                        <form action="{{ route('sms-test.send') }}" method="POST">
                            @csrf
                            <div class="form-group mb-3">
                                <label class="mb-1"><b>{{__('Mobile Number')}}</b> <span class="text-danger">*</span></label>
                                <input type="text" name="mobile" class="form-control" placeholder="966501234567 or 05xxxxxxxx" required>
                                <small class="form-text text-muted">Enter mobile number with country code (e.g., 966501234567) or Saudi format (05xxxxxxxx)</small>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label class="mb-1"><b>{{__('Test Message')}}</b> <span class="text-danger">*</span></label>
                                <textarea name="message" class="form-control" rows="3" placeholder="Enter test message..." required>This is a test message from {{ config('app.name') }} SMS system.</textarea>
                                <small class="form-text text-muted">Maximum 160 characters recommended</small>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> {{__('Send Test SMS')}}
                            </button>
                            <a href="{{ route('sms-gateway.index') }}" class="btn btn-secondary">
                                <i class="fas fa-cog"></i> {{__('SMS Configuration')}}
                            </a>
                        </form>
                    </div>
                </div>

                <!-- Balance Display Card -->
                <div class="card mt-4" id="balanceCard" style="display: none;">
                    <div class="card-header">
                        <h5 class="card-title m-0"><i class="fas fa-wallet"></i> Account Balance</h5>
                    </div>
                    <div class="card-body">
                        <div id="balanceContent">
                            <!-- Balance info will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function checkBalance() {
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking...';
            btn.disabled = true;

            fetch('{{ route("sms-test.balance") }}')
                .then(response => response.json())
                .then(data => {
                    const balanceCard = document.getElementById('balanceCard');
                    const balanceContent = document.getElementById('balanceContent');
                    
                    if (data.success) {
                        balanceContent.innerHTML = `
                            <div class="alert alert-success">
                                <h6>Balance Information:</h6>
                                <pre>${JSON.stringify(data.balance, null, 2)}</pre>
                            </div>
                        `;
                    } else {
                        balanceContent.innerHTML = `
                            <div class="alert alert-danger">
                                <strong>Error:</strong> ${data.message}
                            </div>
                        `;
                    }
                    
                    balanceCard.style.display = 'block';
                })
                .catch(error => {
                    const balanceCard = document.getElementById('balanceCard');
                    const balanceContent = document.getElementById('balanceContent');
                    
                    balanceContent.innerHTML = `
                        <div class="alert alert-danger">
                            <strong>Error:</strong> Failed to check balance. ${error.message}
                        </div>
                    `;
                    balanceCard.style.display = 'block';
                })
                .finally(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
        }
    </script>
@endsection
