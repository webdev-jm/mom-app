@extends('layouts.app')

{{-- Customize layout sections --}}
@section('subtitle', __('adminlte::moms.mom_details'))
@section('content_header_title', __('adminlte::moms.mom'))
@section('content_header_subtitle', __('adminlte::moms.mom_details'))

{{-- Content body: main page content --}}
@section('content_body')

    <div class="row">

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-6 align-middle">
                            <strong class="text-lg">{{__('adminlte::moms.mom_details')}}</strong>
                        </div>
                        <div class="col-lg-6 text-right">
                            <a href="{{route('mom.index')}}" class="btn btn-secondary btn-sm">
                                <i class="fa fa-caret-left mr-1"></i>
                                {{__('adminlte::utilities.back')}}
                            </a>
                            @can('mom print')
                                <a href="{{route('mom.printPDF', encrypt($mom->id))}}" class="btn btn-warning btn-sm mb-0 ml-0" target="_blank">
                                    <i class="fa fa-file-pdf"></i>
                                    {{__('adminlte::utilities.print')}}
                                </a>
                                <a href="{{route('mom.exportExcel', encrypt($mom->id))}}" class="btn btn-secondary btn-sm mb-0 ml-0">
                                    <i class="fa fa-file-excel"></i>
                                    {{__('adminlte::utilities.export')}}
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>
                <div class="card-body text-lg">
                    <h5>
                        <b>{{__('adminlte::moms.mom_number')}}</b>: <strong class="badge badge-success text-lg">{{$mom->mom_number}}</strong>
                    </h5>
                    <b>{{__('adminlte::moms.agenda')}}:</b> <p class="ml-3 mb-0 font-weight-bold text-danger">{{$mom->agenda}}</p>  

                    <b>{{__('adminlte::moms.meeting_date')}}:</b> {{$mom->meeting_date}}
                    <br>
                    <b>{{__('adminlte::utilities.status')}}:</b> <span class="badge badge-{{$status_arr[$mom->status]}}">{{$mom->status}}</span>
                    <br>
                    <b>{{__('adminlte::utilities.created_by')}}:</b> {{$mom->user->name}}
                    <br>
                    <b>{{__('adminlte::utilities.created_at')}}:</b> {{$mom->created_at}}
                    <br>
                    <b>{{__('adminlte::utilities.updated_at')}}:</b> {{$mom->updated_at}}
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="col-lg-6 align-middle">
                        <strong class="text-lg">{{__('adminlte::moms.attendees')}}</strong>
                    </div>
                    <div class="col-lg-6 text-right">
                        
                    </div>
                </div>
                <div class="card-header">
                    <ul class="users-list clearfix">
                        @foreach($mom->participants as $participant)
                            <li>
                                <img src="{{$participant->adminlte_image()}}" alt="{{$participant->name}}" class="user-img">
                                @can('user access')
                                    <a href="{{route('user.show', encrypt($participant->id))}}" class="users-list-name">{{$participant->name}}</a>
                                @else
                                    <a href="#" class="users-list-name">{{$participant->name}}</a>
                                @endcan
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>


            <livewire:moms.remarks :mom="$mom"/>
        </div>

        <div class="col-md-6">
            <livewire:moms.history :mom="$mom"/>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="col-lg-6 align-middle">
                        <strong class="text-lg">{{__('adminlte::moms.topics_to_solve')}}</strong>
                    </div>
                    <div class="col-lg-6 text-right">
                        
                    </div>
                </div>
                <div class="card-body">
                    @foreach($mom->details as $detail)
                        <livewire:moms.topics.item :detail="$detail" :responsibles="$mom->participants" type="show" :key="$detail->id"/>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    
@stop

{{-- Push extra CSS --}}
@push('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
    <style>
        .user-img {
            width: 50px !important;
            height: 50px !important;
        }
        
        .users-list-name {
            white-space: pre-wrap;
            font-weight: 800;
        }
        .dark-mode pre{
            color: white;
        }
    </style>
@endpush

{{-- Push extra scripts --}}
@push('js')
    <script>
        $(function() {
        });
    </script>
@endpush