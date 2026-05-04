@extends('layouts.app')

{{-- Customize layout sections --}}
@section('subtitle', __('adminlte::users.user_list'))
@section('content_header_title', __('adminlte::users.users'))
@section('content_header_subtitle', __('adminlte::users.user_list'))

{{-- Content body: main page content --}}
@section('content_body')
    <div class="card">
        <div class="card-header py-2">
            <div class="row">
                <div class="col-lg-6 align-middle">
                    <strong class="text-lg">{{__('adminlte::users.user_list')}}</strong>
                </div>
                <div class="col-lg-6 text-right">
                    @can('user create')
                        <a href="{{route('user.create')}}" class="btn btn-primary btn-xs">
                            <i class="fa fa-file"></i>
                            {{__('adminlte::users.new_user')}}
                        </a>
                    @endcan
                </div>
            </div>
        </div>
        <div class="card-body">

            {{ html()->form('GET', route('user.index'))->open() }}
                <div class="row mb-1">
                    <div class="col-lg-4">
                        <div class="form-group">
                            {{ html()->label(__('adminlte::utilities.search'), 'search')->class('mb-0') }}
                            {{ html()->input('text', 'search', $search)->placeholder(__('adminlte::utilities.search'))->class(['form-control', 'form-control-sm'])}}
                        </div>
                    </div>
                </div>
            {{ html()->form()->close() }}
            
            <div class="row">
                <div class="col-12 table-responsive">
                    <table class="table table-sm table-striped table-hover mb-0 rounded">
                        <thead class="text-center bg-dark">
                            <tr class="text-center">
                                <th></th>
                                <th class="text-left">{{__('adminlte::utilities.name')}}</th>
                                <th>{{__('adminlte::utilities.email')}}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <td class="text-center align-middle">
                                        @if($user->isOnline())
                                            <i class="fa fa-circle text-success"></i>
                                        @else
                                            <i class="fa fa-circle text-secondary"></i>
                                        @endif
                                    </td>
                                    <td class="align-middle text-left p-0">
                                        <img src="{{$user->adminlte_image()}}" alt="{{$user->name}}" class="img-fluid img-circle user-img m-1">
                                        {{$user->name}}
                                    </td>
                                    <td class="align-middle text-center p-0">
                                        {{$user->email}}
                                    </td>
                                    <td class="align-middle text-right p-0 pr-1">
                                        <a href="{{route('user.show', encrypt($user->id))}}" class="btn btn-info btn-xs mb-0 ml-0">
                                            <i class="fa fa-list"></i>
                                            {{__('adminlte::utilities.view')}}
                                        </a>
                                        @can('user edit')
                                            <a href="{{route('user.edit', encrypt($user->id))}}" class="btn btn-success btn-xs mb-0 ml-0">
                                                <i class="fa fa-pen-alt"></i>
                                                {{__('adminlte::utilities.edit')}}
                                            </a>
                                        @endcan
                                        @can('user delete')
                                            <a href="#" class="btn btn-danger btn-xs mb-0 ml-0 btn-delete" data-id="{{encrypt($user->id)}}">
                                                <i class="fa fa-trash-alt"></i>
                                                {{__('adminlte::utilities.delete')}}
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
        <div class="card-footer">
            {{$users->links()}}
        </div>
    </div>
@stop

{{-- Push extra CSS --}}
@push('css')
    {{-- Add here extra stylesheets --}}
    <style>
        .user-img {
            height: 30px;
        }
    </style>
@endpush

{{-- Push extra scripts --}}
@push('js')
    <script>
        $(function() {
            $('body').on('click', '.btn-delete', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                Livewire.dispatch('setDeleteModel', {type: 'User', model_id: id});
                $('#modal-delete').modal('show');
            });
        });
    </script>
@endpush