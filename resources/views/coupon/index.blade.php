@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4 coupons-container" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <div class="row">
        <div class="col-lg-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
            <div class="card" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                <div class="card-header" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                   <div class="row" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                        <div class="col-6" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                            <h2 class="card-title" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Coupons') }}</h2>
                        </div>

                        @can('coupon.create')
                        <div class="col-6 position-relative" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}" >
                            <div class="position-absolute" style="{{ app()->getLocale() == 'ar' ? 'left: 1em' : 'right: 1em' }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                <a href="{{ route('coupon.create') }}" class="btn btn-primary" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Create'). ' '.__('Coupon') }}</a>
                            </div>
                        </div>
                        @endcan
                   </div>
                </div>
                <div class="card-body" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                    <div class="table-responsive" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                        <table class="table table-bordered table-striped verticle-middle table-responsive-sm {{ session()->get('local') }}" id="myTable" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                            <thead dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                <tr dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                    <th scope="col" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Code') }}</th>
                                    <th scope="col" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Discount_Type') }} </th>
                                    <th scope="col" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Discount') }}</th>
                                    <th scope="col" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Min_Amount') }}</th>
                                    <th scope="col" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Started_at') }}</th>
                                    <th scope="col" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Expired_at') }}</th>
                                    @can('coupon.edit')
                                    <th scope="col" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Action') }}</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                @foreach ($coupons as $coupon)
                                <tr dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                    <td dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ $coupon->code}}</td>
                                    <td dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __($coupon->discount_type) }}</td>
                                    <td dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{!! $coupon->discount_type == 'amount' ? currencyPosition($coupon->discount) : $coupon->discount.'%' !!}</td>
                                    <td dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ currencyPosition($coupon->min_amount) }}</td>
                                    <td dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ Carbon\Carbon::parse($coupon->started_at)->format('M d, Y h:i a') }}</td>
                                    <td dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ Carbon\Carbon::parse($coupon->expired_at)->format('M d, Y h:i a') }}</td>

                                    @can('coupon.edit')
                                    <td dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                        <a href="{{ route('coupon.edit', $coupon->id) }}" class="btn btn-primary" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                    </td>
                                    @endcan
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<style>
    /* RTL Support for Coupons Page */
    @if(app()->getLocale() == 'ar')
    html[dir="rtl"] .coupons-container,
    html[dir="rtl"] .coupons-container * {
        direction: rtl !important;
        text-align: right !important;
    }

    html[dir="rtl"] .coupons-container .card,
    html[dir="rtl"] .coupons-container .card-header,
    html[dir="rtl"] .coupons-container .card-body {
        direction: rtl !important;
        text-align: right !important;
    }

    html[dir="rtl"] .coupons-container .position-absolute {
        left: 1em !important;
        right: auto !important;
    }

    html[dir="rtl"] .coupons-container .table,
    html[dir="rtl"] .coupons-container .table th,
    html[dir="rtl"] .coupons-container .table td {
        direction: rtl !important;
        text-align: right !important;
    }

    html[dir="rtl"] .coupons-container .btn {
        direction: rtl !important;
    }

    html[dir="rtl"] .coupons-container .fa {
        margin-left: 8px;
        margin-right: 0;
    }

    html[dir="rtl"] .coupons-container .row,
    html[dir="rtl"] .coupons-container .col-6,
    html[dir="rtl"] .coupons-container .col-lg-12 {
        direction: rtl !important;
    }

    html[dir="rtl"] .coupons-container .table-responsive {
        direction: rtl !important;
    }
    @endif
</style>
