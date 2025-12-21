@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4 banners-container" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <div class="row" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
        <div class="col-lg-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
            <div class="card" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                <div class="card-header" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                    <h2 class="card-title float-left" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('App_Banners') }}</h2>
                    @can('banner.store')
                    <div class="w-100 {{ app()->getLocale() == 'ar' ? 'text-left' : 'text-right' }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createModal" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                            {{ __('Add_New'). ' '.__('Banner') }}
                        </button>
                    </div>

                    <div class="modal fade" id="createModal" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                        <div class="modal-dialog" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                            <div class="modal-content" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                            <div class="modal-header" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                <h2 class="modal-title" id="exampleModalLabel" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Add_New'). ' '.__('Banner') }}</h2>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form @role('root|admin') action="{{ route('banner.store') }}" @endrole method="POST" enctype="multipart/form-data" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}"> @csrf
                                <div class="modal-body" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                    <label class="mb-1" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Banner'). ' '.__('Title') }}</label>
                                    <x-input name='title' type="text" placeholder="{{ __('Banner'). ' '.__('Title') }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}" />

                                    <label class="mb-1" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Banner'). ' '.__('Photo') }}</label>
                                    <x-input-file name="image" type="file" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}"/>

                                    <label class="mb-1" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Banner'). ' '.__('Description') }}</label>
                                    <x-textarea name="description" placeholder="{{ __('Banner'). ' '.__('Description') }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}" />

                                    <div class="form-group" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                        <label for="active" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                            <input type="radio" id="active" name="active" value="1" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}"> {{ __('Action') }}
                                        </label>
                                        <label for="in_active" class="ml-3" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                            <input type="radio" id="in_active" name="active" value="1" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}"> {{ __('Inactive') }}
                                        </label>
                                    </div>

                                    <div class="form-group" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                        <label for="banner" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                            <input type="checkbox" id="banner" class="form-control-checkbox" name="banner" value="1" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}"> {{ __('Web'). ' '.__('Banner') }}
                                        </label>
                                    </div>
                                </div>
                                <div class="modal-footer" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Close') }}</button>
                                    <button type="submit" class="btn btn-primary @role('visitor')visitorMessage @endrole" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Save_changes') }}</button>
                                </div>
                            </form>
                            </div>
                        </div>
                    </div> {{-- Modal End --}}
                    @endcan
                </div>
                <div class="card-body" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                    <div class="table-responsive" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                        <table class="table table-bordered table-striped verticle-middle table-responsive-sm {{ session()->get('local') }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                            <thead dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                <tr dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                    <th scope="col" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Title') }}</th>
                                    <th scope="col" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Description') }}</th>
                                    <th scope="col" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Image') }}</th>
                                    @can('banner.status.toggle')
                                    <th scope="col" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Status') }}</th>
                                    @endcan
                                    @canany(['banner.destroy', 'banner.edit'])
                                    <th scope="col" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ __('Action') }}</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                @foreach ($banners as $banner)
                                <tr dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                    <td dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">{{ $banner->title }}</td>
                                    <td dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                        {{ substr($banner->description, 0 ,30) }}
                                    </td>
                                    <td dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                        <img width="100" src="{{ asset($banner->thumbnailPath) }}" alt="" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                    </td>
                                    @can('banner.status.toggle')
                                    <td dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                        <label class="switch" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                            <a href="{{ route('banner.status.toggle', $banner->id) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                                <input type="checkbox" {{ $banner->is_active ? 'checked' : '' }} dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                                <span class="slider round"></span>
                                            </a>
                                        </label>
                                    </td>
                                    @endcan
                                    @canany(['banner.destroy', 'banner.edit'])
                                    <td dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                        @can('banner.edit')
                                        <a href="{{ route('banner.edit', $banner->id) }}" class="btn btn-sm btn-primary" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                            <i class="far fa-edit"></i>
                                        </a>
                                        @endcan
                                        @can('banner.destroy')
                                        <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteModal_{{ $banner->id }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>

                                        <div class="modal fade" id="deleteModal_{{ $banner->id }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                            <div class="modal-dialog" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                                <div class="modal-content" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                                <div class="modal-header" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                                    <h2 class="modal-title" id="exampleModalLabel" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">Delete a banner</h2>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                                    <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                    <div class="modal-body" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                                        <h3 class="text-warning" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">Are you sure?</h3>
                                                        <h5 dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">You want to permanently delete this banner.</h5>
                                                        <img width="30%" src="{{ $banner->thumbnailPath }}" alt="" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                                    </div>
                                                    <div class="modal-footer" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">Close</button>
                                                        @role('visitor')
                                                        <button type="button" class="btn btn-danger visitorMessage" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">Delete</button>
                                                        @else
                                                        <form action="{{ route('banner.destroy', $banner->id) }}" method="POST" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                                            @csrf @method('delete')
                                                            <button type="submit" class="btn btn-danger" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">Delete</button>
                                                        </form>
                                                        @endrole
                                                    </div>
                                                </div>
                                            </div>
                                        </div>  {{-- Modal End --}}
                                        @endcan
                                    </td>
                                    @endcanany
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
    /* RTL Support for Banners Page */
    @if(app()->getLocale() == 'ar')
    html[dir="rtl"] .banners-container,
    html[dir="rtl"] .banners-container * {
        direction: rtl !important;
        text-align: right !important;
    }

    html[dir="rtl"] .banners-container .card-title {
        float: right !important;
    }

    html[dir="rtl"] .banners-container .text-right {
        text-align: left !important;
    }

    html[dir="rtl"] .banners-container .ml-3,
    html[dir="rtl"] .banners-container .ml-4 {
        margin-right: 1rem !important;
        margin-left: 0 !important;
    }

    html[dir="rtl"] .banners-container .modal-footer {
        justify-content: flex-start !important;
    }

    html[dir="rtl"] .banners-container .table,
    html[dir="rtl"] .banners-container .table th,
    html[dir="rtl"] .banners-container .table td,
    html[dir="rtl"] .banners-container .table-responsive {
        direction: rtl !important;
        text-align: right !important;
    }

    html[dir="rtl"] .banners-container .switch {
        direction: rtl !important;
    }

    html[dir="rtl"] .banners-container .modal-header .close {
        margin-right: auto;
        margin-left: 0;
    }
    @endif
</style>
