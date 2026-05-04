@extends('layouts.app')

{{-- Customize layout sections --}}
@section('subtitle', __('adminlte::users.new_user'))
@section('content_header_title', __('adminlte::users.users'))
@section('content_header_subtitle', __('adminlte::users.new_user'))

{{-- Content body: main page content --}}
@section('content_body')
    {{ html()->form('POST', route('user.store'))->open() }}
        <div class="card">
            <div class="card-header py-2">
                <div class="row">
                    <div class="col-lg-6 align-middle">
                        <strong class="text-lg">{{__('adminlte::users.new_user')}}</strong>
                    </div>
                    <div class="col-lg-6 text-right">
                        <a href="{{route('user.index')}}" class="btn btn-secondary btn-xs">
                            <i class="fa fa-caret-left"></i>
                            {{__('adminlte::utilities.back')}}
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">

                <div class="row">
                    <div class="col-lg-3">
                        <div class="form-group">
                            {{ html()->label(__('adminlte::utilities.name'), 'name')->class(['mb-0']) }}
                            {{ html()->input('text', 'name', '')->placeholder(__('adminlte::utilities.name'))->class(['form-control', 'form-control-sm', 'is-invalid' => $errors->has('name')]) }}
                            <small class="text-danger">{{$errors->first('name')}}</small>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            {{ html()->label(__('adminlte::utilities.email'), 'email')->class(['mb-0']) }}
                            {{ html()->input('email', 'email', '')->placeholder(__('adminlte::utilities.email'))->class(['form-control', 'form-control-sm', 'is-invalid' => $errors->has('email')]) }}
                            <small class="text-danger">{{$errors->first('email')}}</small>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            {{ html()->label(__('adminlte::companies.company'), 'company_id')->class(['mb-0']) }}
                            {{ html()->select('company_id', $companies,'')->class(['form-control', 'form-control-sm', 'is-invalid' => $errors->has('company_id')]) }}
                            <small class="text-danger">{{$errors->first('company_id')}}</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        {{ html()->label(__('adminlte::locations.locations'), 'location_ids')->class(['mb-0', 'text-danger' => $errors->has('location_ids')]) }}
                        @if($errors->has('location_ids'))
                            <span class="badge badge-danger pt-1">{{__('adminlte::utilities.required')}}</span>
                        @endif
                        <hr class="mt-0">
                        {{ html()->hidden('location_ids', '')->id('location_ids')}}
                    </div>

                    <div class="col-12">
                        @foreach($locations as $location)
                            <button class="btn btn-default btn-location" data-id="{{encrypt($location->id)}}">{{$location->location_name}}</button>
                        @endforeach
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        {{ html()->label(__('adminlte::roles.roles'), 'role_ids')->class(['mb-0', 'text-danger' => $errors->has('role_ids')]) }}
                        @if($errors->has('role_ids'))
                            <span class="badge badge-danger pt-1">{{__('adminlte::utilities.required')}}</span>
                        @endif
                        <hr class="mt-0">
                        {{ html()->hidden('role_ids', '')->id('role_ids')}}
                    </div>

                    <div class="col-12">
                        @foreach($roles as $role)
                            <button class="btn btn-default btn-role" data-id="{{$role->name}}">{{$role->name}}</button>
                        @endforeach
                    </div>
                </div>

            </div>
            <div class="card-footer text-right">
                {{ html()->submit('<i class="fa fa-save"></i> '.__('adminlte::users.save_user'))->class(['btn', 'btn-primary', 'btn-sm']) }}
            </div>
        </div>
    {{ html()->form()->close() }}
@stop

{{-- Push extra CSS --}}
@push('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@endpush

{{-- Push extra scripts --}}
@push('js')
    <script>
        $(function() {
            $('body').on('click', '.btn-role', function(e) {
                e.preventDefault();
                $(this).toggleClass('btn-success').toggleClass('btn-default');

                // get all selected
                var role_ids = [];
                $('body').find('.btn-role').each(function() {
                    var id = $(this).data('id');
                    if($(this).hasClass('btn-success')) {
                        role_ids.push(id);
                    }
                });

                var roles = role_ids.join(',');
                $('#role_ids').val(roles);
            });

            $('body').on('click', '.btn-location', function(e) {
                e.preventDefault();
                $(this).toggleClass('btn-success').toggleClass('btn-default');

                // get all selected
                var location_ids = [];
                $('body').find('.btn-location').each(function() {
                    var id = $(this).data('id');
                    if($(this).hasClass('btn-success')) {
                        location_ids.push(id);
                    }
                });

                var locations = location_ids.join(',');
                $('#location_ids').val(locations);
            });
        })
    </script>
@endpush
