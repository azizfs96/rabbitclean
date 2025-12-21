@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4 variants-container" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
        <div class="row">
            <div class="col-lg-12">
                <div class="card" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                    <div class="card-header" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                        <div class="row" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                            <div class="col-6" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                <h2 class="card-title" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('All') . ' ' . __('Variants') }}</h2>
                            </div>

                            @can('variant.store')
                                <div class="col-6 position-relative" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                    <button data-toggle="modal" data-target="#addNew" class="position-absolute btn btn-primary"
                                        style="{{ app()->getLocale() == 'ar' ? 'left: 1em' : 'right: 1em' }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                        {{ __('Add_New') . ' ' . __('Variant') }}
                                    </button>
                                </div>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                        <div class="table-responsive" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                            <table
                                class="table table-bordered table-striped verticle-middle table-responsive-sm {{ session()->get('local') }}"
                                id="myTable" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                <thead dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                    <tr dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                        <th scope="col" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Name') . ' ' . __('of') . ' ' . __('English') }}</th>
                                        <th scope="col" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Name') . ' ' . __('of') . ' ' . __('Arabic') }}</th>
                                        @canany(['variant.update', 'variant.products'])
                                            <th scope="col" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Action') }}</th>
                                        @endcanany
                                    </tr>
                                </thead>
                                <tbody dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                    @foreach ($variants as $variant)
                                        <tr dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                            <td dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ $variant->name }}</td>
                                            <td dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ $variant->name_bn }}</td>
                                            @canany(['variant.update', 'variant.products'])
                                                <td dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                                    @can('variant.update')
                                                        <button type="button" class="btn btn-primary" data-toggle="modal"
                                                            data-target="#update{{ $variant->id }}">
                                                            <i class="far fa-edit"></i>
                                                        </button>
                                                        <!-- Modal -->
                                                        <div class="modal fade" id="update{{ $variant->id }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                                            <div class="modal-dialog" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                                                <div class="modal-content" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                                                    <div class="modal-header" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                                                        <h2 class="modal-title" id="exampleModalLabel" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                                                            {{ __('Edit') . ' ' . __('Variant') }}</h2>
                                                                        <button type="button" class="close" data-dismiss="modal"
                                                                            aria-label="Close" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                                                            <span aria-hidden="true">&times;</span>
                                                                        </button>
                                                                    </div>
                                                                    <form
                                                                        @role('root|admin') action="{{ route('variant.update', $variant->id) }}" @endrole
                                                                        method="POST" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                                                        @csrf @method('put')
                                                                        <div class="modal-body" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                                                            <div class="mb-3" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                                                                <label dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Variant') . ' ' . __('Name') . ' ' . __('English') }}</label>
                                                                                <input type="text" name="name"
                                                                                    class="form-control"
                                                                                    value="{{ old('name') ?? $variant->name }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                                                            </div>

                                                                            <div class="mb-3" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                                                                <label dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Variant') . ' ' . __('Name') . ' ' . __('Arabic') }}</label>
                                                                                <input type="text" name="name_bn"
                                                                                    class="form-control"
                                                                                    value="{{ old('name_bn') ?? $variant->name_bn }}"
                                                                                    placeholder="اسم المتغير" dir="rtl">
                                                                            </div>

                                                                            <div class="mb-3" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                                                                <label dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Position') }}</label>
                                                                                <input type="text" name="position"
                                                                                    class="form-control"
                                                                                    value="{{ old('position') ?? $variant->position }}"
                                                                                    placeholder="{{ __('Position') }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                                                            </div>
                                                                        </div>
                                                                        <div class="modal-footer" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                                                            <button type="button" class="btn btn-secondary"
                                                                                data-dismiss="modal" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Close') }}</button>
                                                                            <button
                                                                                @role('visitor') type="button" class="btn btn-primary visitorMessage" @else type="submit" class="btn btn-primary" @endrole dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Save_changes') }}</button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endcan

                                                    @can('variant.products')
                                                        <a href="{{ route('variant.products', $variant->id) }}"
                                                            class="btn btn-info">{{ __('Products') }}</a>
                                                    @endcan
                                                </td>
                                            @endcanany
                                        </tr>
                                    @endforeach
                                </tbody>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @can('variant.store')
        <!-- Modal -->
        <div class="modal fade" id="addNew" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
            <div class="modal-dialog" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                <div class="modal-content" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                    <div class="modal-header" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                        <h2 class="modal-title" id="exampleModalLabel" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Add') . ' ' . __('Variant') }}</h2>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form @role('root|admin') action="{{ route('variant.store') }}" @endrole method="POST" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                        @csrf
                        <div class="modal-body" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                            <div class="mb-3" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                <label dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Variant') . ' ' . __('Name') . ' ' . __('English') }}</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required
                                    placeholder="{{ __('Variant') . ' ' . __('Name') . ' ' . __('English') }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                            </div>

                            <div class="mb-3" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                <label dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Variant') . ' ' . __('Name') . ' ' . __('Arabic') }}</label>
                                <input type="text" name="name_bn" class="form-control" value="{{ old('name_bn') }}"
                                    placeholder="{{ __('Variant') . ' ' . __('Name') . ' ' . __('Arabic') }}" dir="rtl">
                            </div>

                            <div class="mb-3" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                <label dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Position') }}</label>
                                <input type="text" name="position" class="form-control" value="{{ old('position') }}"
                                    placeholder="{{ __('Position') }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                            </div>
                        </div>
                        <div class="modal-footer" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Close') }}</button>
                            @role('visitor')
                                <button type="button" class="btn btn-primary visitorMessage" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Submit') }}</button>
                            @else
                                <button type="submit" class="btn btn-primary" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Submit') }}</button>
                            @endrole
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan

    <style>
        /* RTL Support for Variants Page */
        @if(app()->getLocale() == 'ar')
        /* Global RTL for the variants page */
        html[dir="rtl"] .variants-container,
        html[dir="rtl"] .variants-container * {
            direction: rtl !important;
            text-align: right !important;
        }

        /* Card Components */
        html[dir="rtl"] .variants-container .card {
            direction: rtl !important;
            text-align: right !important;
        }

        html[dir="rtl"] .variants-container .card-header,
        html[dir="rtl"] .variants-container .card-body {
            direction: rtl !important;
            text-align: right !important;
        }

        html[dir="rtl"] .variants-container .card-title {
            text-align: right !important;
            direction: rtl !important;
        }

        /* Table Components */
        html[dir="rtl"] .variants-container .table {
            direction: rtl !important;
            text-align: right !important;
        }

        html[dir="rtl"] .variants-container .table th,
        html[dir="rtl"] .variants-container .table td {
            text-align: right !important;
            direction: rtl !important;
        }

        html[dir="rtl"] .variants-container .table-responsive {
            direction: rtl !important;
        }

        /* Modal Components */
        html[dir="rtl"] .variants-container .modal {
            direction: rtl !important;
            text-align: right !important;
        }

        html[dir="rtl"] .variants-container .modal-dialog,
        html[dir="rtl"] .variants-container .modal-content {
            direction: rtl !important;
            text-align: right !important;
        }

        html[dir="rtl"] .variants-container .modal-header,
        html[dir="rtl"] .variants-container .modal-body,
        html[dir="rtl"] .variants-container .modal-footer {
            direction: rtl !important;
            text-align: right !important;
        }

        html[dir="rtl"] .variants-container .modal-title {
            text-align: right !important;
            direction: rtl !important;
        }

        /* Form Elements */
        html[dir="rtl"] .variants-container .form-control,
        html[dir="rtl"] .variants-container input,
        html[dir="rtl"] .variants-container textarea {
            direction: rtl !important;
            text-align: right !important;
            unicode-bidi: plaintext;
        }

        html[dir="rtl"] .variants-container label {
            direction: rtl !important;
            text-align: right !important;
            display: block;
        }

        /* Buttons */  
        html[dir="rtl"] .variants-container .btn {
            direction: rtl !important;
        }

        /* Position Adjustments */
        html[dir="rtl"] .variants-container .position-absolute {
            left: 1em !important;
            right: auto !important;
        }

        /* Grid System */
        html[dir="rtl"] .variants-container .row,
        html[dir="rtl"] .variants-container .col-6,
        html[dir="rtl"] .variants-container .col-lg-12 {
            direction: rtl !important;
        }

        /* Typography */
        html[dir="rtl"] .variants-container h1,
        html[dir="rtl"] .variants-container h2,
        html[dir="rtl"] .variants-container h3,
        html[dir="rtl"] .variants-container h4,
        html[dir="rtl"] .variants-container h5,
        html[dir="rtl"] .variants-container h6 {
            direction: rtl !important;
            text-align: right !important;
            font-weight: 600;
            line-height: 1.4;
        }

        html[dir="rtl"] .variants-container p,
        html[dir="rtl"] .variants-container div,
        html[dir="rtl"] .variants-container span {
            direction: rtl !important;
            text-align: right !important;
            line-height: 1.6;
        }

        /* Icons with text */
        html[dir="rtl"] .variants-container .fas,
        html[dir="rtl"] .variants-container .fa,
        html[dir="rtl"] .variants-container .far {
            margin-left: 8px;
            margin-right: 0;
        }

        /* Close button for modals */
        html[dir="rtl"] .variants-container .close {
            float: left !important;
        }

        /* Arabic input fields */
        html[dir="rtl"] .variants-container input[name="name_bn"] {
            direction: rtl !important;
            text-align: right !important;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        @endif
    </style>
@endsection
