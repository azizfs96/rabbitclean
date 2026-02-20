@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between bg-primary py-2 align-items-center">
                <h2 class="card-title m-0 text-white">{{ __('أحياء الخدمة') }}</h2>
                <button class="btn btn-white" data-toggle="modal" data-target="#addServiceAreaModal">{{ __('Add_New') }}</button>
            </div>

            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <table class="table table-bordered {{ session()->get('local') }}" id="myTable">
                    <thead class="bg-secondary">
                        <tr>
                            <th>{{ __('SL.') }}</th>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('مشمول بالخدمة') }}</th>
                            <th>{{ __('رسوم إضافية مسموحة') }}</th>
                            <th>{{ __('قيمة الرسوم') }}</th>
                            <th>{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($serviceAreas as $index => $area)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $area->name }}</td>
                                <td>
                                    <label class="switch">
                                        <a href="{{ route('service-areas.toggle', $area->id) }}">
                                            <input type="checkbox" {{ $area->is_served ? 'checked' : '' }}>
                                            <span class="slider round"></span>
                                        </a>
                                    </label>
                                </td>
                                <td>{{ $area->allow_with_extra_fee ? __('نعم') : __('لا') }}</td>
                                <td>{{ number_format($area->extra_delivery_fee ?? 0, 2) }}</td>
                                <td>
                                    <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#editServiceArea{{ $area->id }}"><i class="fas fa-edit"></i></button>
                                    <a href="{{ route('service-areas.delete', $area->id) }}" class="btn btn-sm btn-danger" onclick="return confirm('{{ __('هل أنت متأكد من الحذف؟') }}');"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <div class="modal fade" id="editServiceArea{{ $area->id }}">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header bg-primary py-2">
                                            <h4 class="modal-title text-white">{{ __('Edit') }}</h4>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        </div>
                                        <form action="{{ route('service-areas.update', $area->id) }}" method="POST">
                                            @csrf
                                            @method('put')
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label class="mb-0">{{ __('اسم الحي') }}</label>
                                                    <input type="text" name="name" value="{{ $area->name }}" class="form-control" required>
                                                </div>
                                                <div class="form-group">
                                                    <label class="mb-0">
                                                        <input type="hidden" name="is_served" value="0">
                                                        <input type="checkbox" name="is_served" value="1" {{ $area->is_served ? 'checked' : '' }}> {{ __('مشمول بالخدمة بالكامل') }}
                                                    </label>
                                                </div>
                                                <div class="form-group">
                                                    <label class="mb-0">
                                                        <input type="hidden" name="allow_with_extra_fee" value="0">
                                                        <input type="checkbox" name="allow_with_extra_fee" value="1" {{ $area->allow_with_extra_fee ? 'checked' : '' }}> {{ __('مسموح مع رسوم إضافية') }}
                                                    </label>
                                                </div>
                                                <div class="form-group">
                                                    <label class="mb-0">{{ __('قيمة الرسوم الإضافية') }}</label>
                                                    <input type="number" name="extra_delivery_fee" value="{{ $area->extra_delivery_fee }}" class="form-control" step="0.01" min="0">
                                                </div>
                                            </div>
                                            <div class="modal-footer py-2">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                                                <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addServiceAreaModal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary py-2">
                    <h4 class="modal-title text-white">{{ __('Add_New') }} {{ __('حي') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form action="{{ route('service-areas.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="mb-0">{{ __('اسم الحي') }}</label>
                            <input type="text" name="name" placeholder="{{ __('اسم الحي') }}" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="mb-0">
                                <input type="hidden" name="is_served" value="0">
                                <input type="checkbox" name="is_served" value="1" checked> {{ __('مشمول بالخدمة بالكامل') }}
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="mb-0">
                                <input type="hidden" name="allow_with_extra_fee" value="0">
                                <input type="checkbox" name="allow_with_extra_fee" value="1"> {{ __('مسموح مع رسوم إضافية') }}
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="mb-0">{{ __('قيمة الرسوم الإضافية') }}</label>
                            <input type="number" name="extra_delivery_fee" value="0" class="form-control" step="0.01" min="0">
                        </div>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
