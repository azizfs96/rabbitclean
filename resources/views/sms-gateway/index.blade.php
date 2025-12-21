@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-5 sms-gateway-container" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
        <div class="row">
            <!-- Configuration Section -->
            <div class="col-xl-6 col-lg-6 mt-2">
                <div class="card">
                    <div class="card-header" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                        <h2 class="card-title m-0">{{__('Msegat_SMS_Configuration')}} <a class="text-info" href="https://www.msegat.com/en/" target="_blank">{{__('Visit_Msegat')}}</a></h2>
                    </div>

                    <div class="card-body" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                        <div class="alert alert-info mb-4" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                            <h5 class="alert-heading"><i class="fas fa-info-circle"></i> {{__('Configuration_Guide')}}</h5>
                            <p class="mb-2">{{__('To_configure_Msegat_SMS_integration_you_need')}}:</p>
                            <ul class="mb-0">
                                <li><strong>{{__('API_Key')}}:</strong> {{__('Get_from_your_Msegat_dashboard_under_API_settings')}}</li>
                                <li><strong>{{__('User_Sender')}}:</strong> {{__('Your_registered_sender_name_number_from_Msegat')}}</li>
                                <li><strong>{{__('Base_URL')}}:</strong> {{__('Default_is_msegat_gw_usually_no_need_to_change')}}</li>
                            </ul>
                        </div>

                        <x-form route="sms-gateway.update" :method="true" type="Submit" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                            <div class="row" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                <div class="col-lg-12 mb-3" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                    <label class="mb-1" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}"><b>{{__('API_Key')}}</b> <span class="text-danger">*</span></label>
                                    <x-input :value="config('app.msegat_api_key')" name="api_key" type="text" placeholder="{{__('Enter_your_Msegat_API_Key')}}" required dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}"/>
                                    <small class="form-text text-muted" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{__('Get_your_API_Key_from_Msegat_dashboard')}}</small>
                                </div>
                                <div class="col-lg-12 mb-3" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                    <label class="mb-1" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}"><b>{{__('User_Sender')}}</b> <span class="text-danger">*</span></label>
                                    <x-input :value="config('app.msegat_user_sender')" name="user_sender" type="text" placeholder="{{__('Your_registered_sender_name')}}" required dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}"/>
                                    <small class="form-text text-muted" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{__('Your_approved_sender_name_from_Msegat')}}</small>
                                </div>
                                <div class="col-lg-12 mb-3" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                    <label class="mb-1" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}"><b>{{__('Base_URL')}}</b></label>
                                    <x-input :value="config('app.msegat_base_url', 'https://www.msegat.com/gw')" name="base_url" type="url" placeholder="https://www.msegat.com/gw" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}"/>
                                    <small class="form-text text-muted" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{__('Default_Msegat_API_endpoint_usually_no_need_to_change')}}</small>
                                </div>
                            </div>
                            
                            <div class="alert alert-warning mt-3" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                <h6 dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}"><i class="fas fa-exclamation-triangle"></i> {{__('Important_Notes')}}:</h6>
                                <ul class="mb-0" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                    <li dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{__('Make_sure_your_Msegat_account_has_sufficient_balance')}}</li>
                                    <li dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{__('Your_sender_name_must_be_approved_by_Msegat')}}</li>
                                    <li dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{__('SMS_will_be_sent_in_UTF8_encoding_to_support_Arabic_text')}}</li>
                                    <li dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{__('Connection_test_will_be_performed_after_saving_configuration')}}</li>
                                </ul>
                            </div>
                        </x-form>
                    </div>
                </div>
            </div>

            <!-- Test SMS Section -->
            <div class="col-xl-6 col-lg-6 mt-2">
                <div class="card">
                    <div class="card-header" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                        <h2 class="card-title m-0" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{__('Test_Msegat_SMS')}} 
                            <button class="btn btn-info btn-sm float-right" onclick="checkBalance()" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                <i class="fas fa-wallet"></i> {{__('Check_Balance')}}
                            </button>
                        </h2>
                    </div>

                    <div class="card-body" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                        <div class="alert alert-info" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                            <h6 dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}"><i class="fas fa-info-circle"></i> {{__('SMS_Test')}}</h6>
                            <p class="mb-0" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{__('Use_this_form_to_test_your_Msegat_SMS_configuration_Make_sure_you_have_configured_your_API_credentials_first')}}</p>
                        </div>

                        <form action="{{ route('sms-gateway.send-test') }}" method="POST" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                            @csrf
                            <div class="form-group mb-3" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                <label class="mb-1" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}"><b>{{__('Mobile_Number')}}</b> <span class="text-danger">*</span></label>
                                <input type="text" name="mobile" class="form-control" placeholder="{{__('966501234567_or_05xxxxxxxx')}}" required dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                <small class="form-text text-muted" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{__('Enter_mobile_number_with_country_code_eg_966501234567_or_Saudi_format_05xxxxxxxx')}}</small>
                            </div>
                            
                            <div class="form-group mb-3" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                <label class="mb-1" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}"><b>{{__('Test_Message')}}</b> <span class="text-danger">*</span></label>
                                <textarea name="message" class="form-control" rows="3" placeholder="{{__('Enter_test_message')}}" required dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{__('This_is_a_test_message_from')}} {{ config('app.name') }} {{__('SMS_system')}}</textarea>
                                <small class="form-text text-muted" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{__('Maximum_160_characters_recommended')}}</small>
                            </div>

                            <button type="submit" class="btn btn-primary w-100" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                <i class="fas fa-paper-plane"></i> {{__('Send_Test_SMS')}}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Balance Display Card -->
                <div class="card mt-4" id="balanceCard" style="display: none;" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                    <div class="card-header" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                        <h5 class="card-title m-0" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}"><i class="fas fa-wallet"></i> {{__('Account_Balance')}}</h5>
                    </div>
                    <div class="card-body" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                        <div id="balanceContent" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                            <!-- Balance info will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* RTL Support for SMS Gateway Page */
        @if(app()->getLocale() == 'ar')
        /* Global RTL for the page */
        html[dir="rtl"], 
        html[dir="rtl"] body,
        html[dir="rtl"] .sms-gateway-container {
            direction: rtl !important;
            text-align: right !important;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Card Components */
        html[dir="rtl"] .sms-gateway-container .card {
            direction: rtl !important;
            text-align: right !important;
        }

        html[dir="rtl"] .sms-gateway-container .card-header,
        html[dir="rtl"] .sms-gateway-container .card-body {
            direction: rtl !important;
            text-align: right !important;
        }

        html[dir="rtl"] .sms-gateway-container .card-header h2,
        html[dir="rtl"] .sms-gateway-container .card-title {
            text-align: right !important;
            direction: rtl !important;
        }

        /* Alert Components */
        html[dir="rtl"] .sms-gateway-container .alert {
            direction: rtl !important;
            text-align: right !important;
        }

        html[dir="rtl"] .sms-gateway-container .alert h5,
        html[dir="rtl"] .sms-gateway-container .alert h6,
        html[dir="rtl"] .sms-gateway-container .alert p {
            text-align: right !important;
            direction: rtl !important;
        }

        /* Form Elements */
        html[dir="rtl"] .sms-gateway-container .form-control,
        html[dir="rtl"] .sms-gateway-container input,
        html[dir="rtl"] .sms-gateway-container textarea {
            direction: rtl !important;
            text-align: right !important;
            unicode-bidi: plaintext;
        }

        html[dir="rtl"] .sms-gateway-container label,
        html[dir="rtl"] .sms-gateway-container .form-text,
        html[dir="rtl"] .sms-gateway-container small {
            direction: rtl !important;
            text-align: right !important;
            display: block;
        }

        /* Lists */
        html[dir="rtl"] .sms-gateway-container ul,
        html[dir="rtl"] .sms-gateway-container ol {
            direction: rtl !important;
            text-align: right !important;
            padding-right: 20px;
            padding-left: 0;
        }

        html[dir="rtl"] .sms-gateway-container li {
            direction: rtl !important;
            text-align: right !important;
        }

        /* Buttons */  
        html[dir="rtl"] .sms-gateway-container .btn {
            direction: rtl !important;
        }

        /* Float Elements */
        html[dir="rtl"] .sms-gateway-container .float-right {
            float: left !important;
        }

        html[dir="rtl"] .sms-gateway-container .float-left {
            float: right !important;
        }

        /* Grid System */
        html[dir="rtl"] .sms-gateway-container .row,
        html[dir="rtl"] .sms-gateway-container .col-xl-6,
        html[dir="rtl"] .sms-gateway-container .col-lg-6,
        html[dir="rtl"] .sms-gateway-container .col-lg-12 {
            direction: rtl !important;
        }

        /* Typography */
        html[dir="rtl"] .sms-gateway-container h1,
        html[dir="rtl"] .sms-gateway-container h2,
        html[dir="rtl"] .sms-gateway-container h3,
        html[dir="rtl"] .sms-gateway-container h4,
        html[dir="rtl"] .sms-gateway-container h5,
        html[dir="rtl"] .sms-gateway-container h6 {
            direction: rtl !important;
            text-align: right !important;
            font-weight: 600;
            line-height: 1.4;
        }

        html[dir="rtl"] .sms-gateway-container p,
        html[dir="rtl"] .sms-gateway-container div,
        html[dir="rtl"] .sms-gateway-container span {
            direction: rtl !important;
            text-align: right !important;
            line-height: 1.6;
        }

        /* Form Groups */
        html[dir="rtl"] .sms-gateway-container .form-group,
        html[dir="rtl"] .sms-gateway-container .mb-3 {
            direction: rtl !important;
            text-align: right !important;
        }

        /* Icons with text */
        html[dir="rtl"] .sms-gateway-container .fas,
        html[dir="rtl"] .sms-gateway-container .fa {
            margin-left: 8px;
            margin-right: 0;
        }
        @endif
    </style>

    <script>
        function checkBalance() {
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> {{__("Checking")}}...';
            btn.disabled = true;

            fetch('{{ route("sms-gateway.balance") }}')
                .then(response => response.json())
                .then(data => {
                    const balanceCard = document.getElementById('balanceCard');
                    const balanceContent = document.getElementById('balanceContent');
                    
                    if (data.success) {
                        balanceContent.innerHTML = `
                            <div class="alert alert-success">
                                <h6>{{__("Balance_Information")}}:</h6>
                                <pre>${JSON.stringify(data.balance, null, 2)}</pre>
                            </div>
                        `;
                    } else {
                        balanceContent.innerHTML = `
                            <div class="alert alert-danger">
                                <strong>{{__("Error")}}:</strong> ${data.message}
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
                            <strong>{{__("Error")}}:</strong> {{__("Failed_to_check_balance")}}. ${error.message}
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
