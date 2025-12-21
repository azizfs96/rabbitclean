@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4 products-container" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <div class="row">
        <div class="col-lg-12">
            <div class="card" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                <div class="card-header" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                    <div class="row align-items-center" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                        <div class="col-md-4" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                            <h2 class="card-title" style="{{ app()->getLocale() == 'ar' ? 'float: right' : 'float: left' }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('All').' '.__('Products') }}</h2>
                        </div>

                        <div class="col-md-8" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                            <form action="{{ route('product.index') }}" method="GET" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                <ul class="nav nav-pills" style="{{ app()->getLocale() == 'ar' ? 'justify-content: flex-start' : 'justify-content: flex-end' }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                    <li class="nav-item" style="{{ app()->getLocale() == 'ar' ? 'margin-right: 0.5rem; margin-left: 0.5rem' : 'margin-left: 0.5rem; margin-right: 0' }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                        <x-input type="text" name='search' placeholder="{{__('Search')}}" value="{{ request('search') }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}" />
                                    </li>
                                    <li class="nav-item" style="{{ app()->getLocale() == 'ar' ? 'margin-right: 0.5rem; margin-left: 0.5rem' : 'margin-left: 0.5rem; margin-right: 0' }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                    <button type="submit" class="btn btn-info" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                        <i class="fa fa-search"></i>
                                    </button>
                                    </li>
                                    @can('product.create')
                                    <li class="nav-item" style="{{ app()->getLocale() == 'ar' ? 'margin-right: 0.5rem; margin-left: 0.5rem' : 'margin-left: 0.5rem; margin-right: 0' }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                        <a href="{{ route('product.create') }}" class="btn btn-primary" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                            {{__('Add_New').' '.__('Product')}}
                                        </a>
                                    </li>
                                    @endcan
                                </ul>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                    <div class="table-responsive" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                        <table class="table table-bordered table-striped {{ session()->get('local') }}" id="myTable" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                            <thead dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                <tr dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                    <th scope="col" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Name').' '.__('of'). ' '. __('English') }}</th>
                                    <th scope="col" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Name').' '.__('of'). ' '. __('Arabic') }}</th>
                                    <th scope="col" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Thumbnail') }}</th>
                                    <th scope="col" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Variant') }}</th>
                                    <th scope="col" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Discount').' '.__('Price') }}</th>
                                    <th scope="col" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Price') }}</th>
                                    <th scope="col" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Description') }}</th>
                                    @can('product.status.toggle')
                                    <th scope="col" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Status') }}</th>
                                    @endcan
                                    @can('product.edit')
                                    <th scope="col" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Action') }}</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                @foreach ($products as $product)
                                <tr dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                    <td dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ $product->name }}</td>
                                    <td dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ $product->name_bn ?? 'N\A' }}</td>
                                    <td dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                        <img width="100" src="{{ $product->thumbnailPath }}" alt="">
                                    </td>
                                    <td dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{  session()->get('local') == 'ar' ? $product->variant->name_bn ??$product->variant->name : $product->variant->name }}</td>
                                    <td dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                        @if ($product->discount_price)
                                        {{ currencyPosition($product->discount_price) }}
                                        @else
                                        <del>{{ currencyPosition('00') }}</del>
                                        @endif
                                    </td>
                                    <td dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                        @if ($product->discount_price)
                                        <del>{{ currencyPosition($product->price ? $product->price : '00')  }}</del>
                                        @else
                                            {{ currencyPosition($product->price ? $product->price : '00')  }}
                                        @endif
                                    </td>
                                    <td dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                        {{$product->description}}
                                    </td>
                                    @can('product.status.toggle')
                                    <td dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                        <label class="switch" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                            <a href="{{ route('product.status.toggle', $product->id) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                                <input type="checkbox" {{ $product->is_active ? 'checked' : '' }} dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                                <span class="slider round" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}"></span>
                                            </a>
                                        </label>
                                    </td>
                                    @endcan
                                    @can('product.edit')
                                    <td dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                        <a href="{{ route('product.edit', $product->id) }}" class="btn btn-sm btn-primary" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                            <i class="far fa-edit"></i>
                                        </a>

                                        <a href="{{ route('product.subproduct.index', $product->id) }}" class="btn btn-sm btn-primary" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                           {{__('Sub Products')}}
                                        </a>

                                        <a href="{{ route('product.delete', $product->id) }}" class="btn btn-sm btn-danger delete-confirm" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}"><i class="fas fa-trash"></i></a>
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

<style>
    /* RTL Support for Products Page */
    @if(app()->getLocale() == 'ar')
    /* Global RTL for the products page */
    html[dir="rtl"] .products-container,
    html[dir="rtl"] .products-container * {
        direction: rtl !important;
        text-align: right !important;
    }

    /* Card Components */
    html[dir="rtl"] .products-container .card {
        direction: rtl !important;
        text-align: right !important;
    }

    html[dir="rtl"] .products-container .card-header,
    html[dir="rtl"] .products-container .card-body {
        direction: rtl !important;
        text-align: right !important;
    }

    html[dir="rtl"] .products-container .card-title {
        text-align: right !important;
        direction: rtl !important;
        float: right !important;
    }

    /* Navigation and Search */
    html[dir="rtl"] .products-container .nav {
        direction: rtl !important;
        justify-content: flex-start !important;
    }

    html[dir="rtl"] .products-container .nav-pills {
        direction: rtl !important;
    }

    html[dir="rtl"] .products-container .nav-item {
        direction: rtl !important;
        margin-right: 0.5rem !important;
        margin-left: 0.5rem !important;
    }

    html[dir="rtl"] .products-container .form-control,
    html[dir="rtl"] .products-container input[type="text"] {
        direction: rtl !important;
        text-align: right !important;
        unicode-bidi: plaintext;
    }

    /* Table Components */
    html[dir="rtl"] .products-container .table {
        direction: rtl !important;
        text-align: right !important;
    }

    html[dir="rtl"] .products-container .table th,
    html[dir="rtl"] .products-container .table td {
        text-align: right !important;
        direction: rtl !important;
    }

    html[dir="rtl"] .products-container .table-responsive {
        direction: rtl !important;
    }

    /* Action Buttons */
    html[dir="rtl"] .products-container .btn {
        direction: rtl !important;
        margin-left: 0.25rem !important;
        margin-right: 0 !important;
    }

    html[dir="rtl"] .products-container .btn-sm {
        margin-left: 0.25rem !important;
        margin-right: 0 !important;
    }

    /* Switch Toggle */
    html[dir="rtl"] .products-container .switch {
        direction: rtl !important;
    }

    html[dir="rtl"] .products-container .slider {
        direction: rtl !important;
    }

    /* Grid System */
    html[dir="rtl"] .products-container .row {
        direction: rtl !important;
    }

    html[dir="rtl"] .products-container .col-md-4,
    html[dir="rtl"] .products-container .col-md-8,
    html[dir="rtl"] .products-container .col-lg-12 {
        direction: rtl !important;
    }

    /* Typography */
    html[dir="rtl"] .products-container h1,
    html[dir="rtl"] .products-container h2,
    html[dir="rtl"] .products-container h3,
    html[dir="rtl"] .products-container h4,
    html[dir="rtl"] .products-container h5,
    html[dir="rtl"] .products-container h6 {
        direction: rtl !important;
        text-align: right !important;
        font-weight: 600;
        line-height: 1.4;
    }

    html[dir="rtl"] .products-container p,
    html[dir="rtl"] .products-container div,
    html[dir="rtl"] .products-container span {
        direction: rtl !important;
        text-align: right !important;
        line-height: 1.6;
    }

    /* Icons with text */
    html[dir="rtl"] .products-container .fas,
    html[dir="rtl"] .products-container .fa,
    html[dir="rtl"] .products-container .far {
        margin-left: 8px;
        margin-right: 0;
    }

    /* Align items center for RTL */
    html[dir="rtl"] .products-container .align-items-center {
        text-align: right !important;
    }

    /* Float adjustments */
    html[dir="rtl"] .products-container .float-left {
        float: right !important;
    }

    html[dir="rtl"] .products-container .float-right {
        float: left !important;
    }

    /* Price and currency alignment */
    html[dir="rtl"] .products-container del {
        direction: rtl !important;
        text-align: right !important;
    }

    /* Image alignment */
    html[dir="rtl"] .products-container img {
        direction: ltr !important; /* Images should maintain normal direction */
    }
    @endif
</style>
@endsection
