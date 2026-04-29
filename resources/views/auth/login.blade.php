@extends('adminlte::auth.login')

@section('auth_footer')
    @parent

    <p class="my-0">
        <a href="{{ route('hub.login') }}" class="btn btn-block btn-outline-secondary mt-2">
            <span class="fas fa-sign-in-alt"></span>
            Login with Hub
        </a>
    </p>
@stop
