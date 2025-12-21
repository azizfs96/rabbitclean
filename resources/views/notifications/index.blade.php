@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4 notifications-container" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
        <div class="row">
            <div class="col-lg-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                <form @can('notification.send') action="{{ route('notification.send') }}" @endcan method="POST" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                    @csrf
                    <div class="card" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                        <div class="card-header bg-primary py-2" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                            <h3 class="card-title m-0 text-white" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Send_Notifications') }}</h3>
                        </div>
                        <div class="card-body" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                            <div class="form-group" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                <label class="mb-1" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Title') }}</label>
                                <input name="title" class="form-control" rows="4" placeholder="{{ __('Title'). ' '. __('Notification') }}..." dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}" />
                                @error('title')
                                    <small class="text-danger" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="form-group mb-2" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                <label class="mb-1" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Message') }}</label>
                                <textarea name="message" class="form-control" rows="4" placeholder="{{ __('Notification'). ' '. __('Message') }}..." dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}"></textarea>
                                @error('message')
                                    <small class="text-danger" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ $message }}</small>
                                @enderror
                            </div>
                            @can('notification.send')
                            <div class="d-flex justify-content-end mt-2" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                <button type="submit" class="btn btn-primary" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Send'). ' '. __('Message') }}</button>
                            </div>
                            @endcan
                            <hr class="my-3">
                            <div class="d-flex justify-content-end align-items-center" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                <span class="font-weight-600 mr-1" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Device_Type') }}:</span>
                                <div class="dropdown" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                    <button class="btn btn-secondary dropdown-toggle text-capitalize" type="button"
                                        id="triggerId" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                        style="width: 150px" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                        {{ __(request()->device_type) ?? __('All') }}
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="triggerId" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                        <a class="dropdown-item"
                                            href="{{ route('notification.index', 'device_type=all') }}">{{ __('All') }}</a>
                                        <a class="dropdown-item"
                                            href="{{ route('notification.index', 'device_type=android') }}">{{ __('Android') }}</a>
                                        <a class="dropdown-item"
                                            href="{{ route('notification.index', 'device_type=ios') }}">{{ __('Ios') }}</a>
                                    </div>
                                </div>
                            </div>
                            @error('customer')
                                <small class="text-danger" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ $message }}</small>
                            @enderror
                            <div class="table-responsive-md mt-2" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                <table class="table table-bordered table-striped table-hover notification_table" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                    <thead dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                        <tr dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                            <th class="px-0 text-center" style="width: 42px" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                                <input type="checkbox" onclick="toggle(this);" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}" />
                                            </th>
                                            <th dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Name') }}</th>
                                            <th dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Phone_number') }}</th>
                                            <th dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Email') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                        @can('notification.send')
                                            @foreach ($customers as $customer)
                                                <tr dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                                    <td class="py-2 px-0 text-center" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                                        <input type="checkbox" name="customer[]" value="{{ $customer->id }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                                    </td>
                                                    <td class="py-2" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ $customer->user->name }}</td>
                                                    <td class="py-2" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ $customer->user->mobile }}</td>
                                                    <td dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ $customer->user->email }}</td>
                                                </tr>
                                            @endforeach
                                        @endcan
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function toggle(source) {
            var checkboxes = document.querySelectorAll('input[type="checkbox"]');
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i] != source)
                    checkboxes[i].checked = source.checked;
            }
        }

        $('.notification_table tr').click(function(event) {
            if (event.target.type !== 'checkbox') {
                $(':checkbox', this).trigger('click');
            }
        });

        // function check() {
        //     var checkboxes = document.querySelectorAll('input[type="checkbox"]');
        //     for (var i = 0; i < checkboxes.length; i++) {
        //         checkboxes[i].checked = true;
        //     }
        // }
    </script>
@endpush
