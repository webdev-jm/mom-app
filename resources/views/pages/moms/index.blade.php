@extends('layouts.app')

{{-- Customize layout sections --}}
@section('subtitle', __('adminlte::moms.mom_list'))
@section('content_header_title', __('adminlte::moms.mom'))
@section('content_header_subtitle', __('adminlte::moms.mom_list'))

{{-- Content body: main page content --}}
@section('content_body')
    <div class="card">
        <div class="card-header py-2">
            <div class="row">
                <div class="col-lg-6 align-middle">
                    <strong class="text-lg">{{__('adminlte::moms.mom_list')}}</strong>
                </div>
                <div class="col-lg-6 text-right">
                    @can('mom create')
                        <a href="{{route('mom.create')}}" class="btn btn-primary btn-xs" data-toggle="tooltip" title="{{__('adminlte::moms.new_mom')}}">
                            <i class="fa fa-file"></i>
                            <span class="d-none d-md-inline">{{__('adminlte::moms.new_mom')}}</span>
                        </a>
                    @endcan
                    @can('mom upload')
                        <a href="{{route('mom.upload')}}" class="btn btn-success btn-xs" data-toggle="tooltip" title="{{__('adminlte::moms.upload_mom')}}">
                            <i class="fa fa-upload"></i>
                            <span class="d-none d-md-inline">{{__('adminlte::moms.upload_mom')}}</span>
                        </a>
                    @endcan
                </div>
            </div>
        </div>
        <div class="card-body">

            {{ html()->form('GET', route('mom.index'))->open() }}
                <div class="row mb-1">
                    <div class="col-lg-4">
                        <div class="form-group">
                            {{ html()->label(__('adminlte::utilities.search'), 'search')->class('mb-0') }}
                            {{ html()->input('text', 'search', $search)->placeholder(__('adminlte::utilities.name'))->class(['form-control', 'form-control-sm'])}}
                        </div>
                    </div>
                </div>
            {{ html()->form()->close() }}
            
            <div class="row">
                <div class="col-12 table-responsive">
                    <table class="table table-sm table-striped table-hover mb-0 rounded">
                        <thead class="tex-center bg-dark">
                            <tr class="text-center">
                                <th>{{__('adminlte::moms.mom_number')}}</th>
                                <th>{{__('adminlte::types.type')}}</th>
                                <th style="max-width: 300px !important;">{{__('adminlte::moms.agenda')}}</th>
                                <th>{{__('adminlte::moms.meeting_date')}}</th>
                                <th>{{__('adminlte::utilities.status')}}</th>
                                <th>{{__('adminlte::users.user')}}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($moms as $mom)
                                <tr>
                                    <td class="align-middle text-center">
                                        {{$mom->mom_number}}
                                    </td>
                                    <td class="align-middle text-center">
                                        {{$mom->type->type ?? '-'}}
                                    </td>
                                    <td class="align-middle text-center" style="max-width: 300px !important;">
                                        {{$mom->agenda ?? '-'}}
                                    </td>
                                    <td class="align-middle text-center">
                                        {{$mom->meeting_date}}
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="badge badge-{{$status_arr[$mom->status]}} text-uppercase">
                                            {{$mom->status}}
                                        </span>
                                    </td>
                                    <td class="align-middle text-center">
                                        {{$mom->user->name}}
                                    </td>
                                    <td class="align-middle text-right p-0 pr-1">
                                        @can('mom edit')
                                            @if($mom->status == 'draft')
                                                <a href="{{route('mom.edit', encrypt($mom->id))}}" class="btn btn-success btn-xs mb-0 ml-0" data-toggle="tooltip" title="{{__('adminlte::utilities.edit')}}">
                                                    <i class="fa fa-pen-alt"></i>
                                                    <span class="d-none d-md-inline">{{__('adminlte::utilities.edit')}}</span>
                                                </a>
                                            @endif
                                        @endcan
                                        <a href="{{route('mom.show', encrypt($mom->id))}}" class="btn btn-info btn-xs mb-0 ml-0" data-toggle="tooltip" title="{{__('adminlte::utilities.view')}}">
                                            <i class="fa fa-list"></i>
                                            <span class="d-none d-md-inline">{{__('adminlte::utilities.view')}}</span>
                                        </a>
                                        @can('mom print')
                                            <a href="{{route('mom.printPDF', encrypt($mom->id))}}" class="btn btn-warning btn-xs mb-0 ml-0" target="_blank" data-toggle="tooltip" title="{{__('adminlte::utilities.print')}}">
                                                <i class="fa fa-file-pdf"></i>
                                                <span class="d-none d-md-inline">{{__('adminlte::utilities.print')}}</span>
                                            </a>
                                            <a href="{{route('mom.exportExcel', encrypt($mom->id))}}" class="btn btn-secondary btn-xs mb-0 ml-0" data-toggle="tooltip" title="{{__('adminlte::utilities.export')}}">
                                                <i class="fa fa-file-excel"></i>
                                                <span class="d-none d-md-inline">{{__('adminlte::utilities.export')}}</span>
                                            </a>
                                        @endcan
                                        @can('mom delete')
                                            @if(auth()->user()->hasRole('superadmin') || $mom->user->id == auth()->user()->id)
                                                <a href="" class="btn btn-danger btn-xs mb-0 ml-0 btn-delete" data-id="{{encrypt($mom->id)}}" data-toggle="tooltip" title="{{__('adminlte::utilities.delete')}}">
                                                    <i class="fa fa-trash-alt"></i>
                                                    <span class="d-none d-md-inline">{{__('adminlte::utilities.delete')}}</span>
                                                </a>
                                            @endif
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
            {{$moms->links()}}
        </div>
    </div>
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
            $('body').on('click', '.btn-delete', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                Livewire.dispatch('setDeleteModel', {type: 'Mom', model_id: id});
                $('#modal-delete').modal('show');
            });

            // Tooltips only below the md breakpoint, where button labels are hidden
            var iconOnlyView = window.matchMedia('(max-width: 767.98px)');

            function toggleButtonTooltips() {
                if (iconOnlyView.matches) {
                    $('[data-toggle="tooltip"]').tooltip({ trigger: 'hover focus' });
                } else {
                    $('[data-toggle="tooltip"]').tooltip('dispose');
                }
            }

            toggleButtonTooltips();
            iconOnlyView.addEventListener('change', toggleButtonTooltips);
        });
    </script>
@endpush