
@extends('layouts.app')


@section('content')
<section class="row">
    <div class="error-page container">
        <div class="col-md-8 col-12 offset-md-2">
            <div class="text-center">
                <img class="img-error" style="width:600px; height:600px;" src="{{ asset('template/mazer/assets/static/images/samples/error-403.svg') }}" alt="Forbidden">
                <h1 class="error-title">Forbidden</h1>
                <p class="fs-5 text-gray-600">Anda tidak memiliki hak akses untuk mengakses halaman ini.</p>
                <a href="{{ route('dashboard') }}" class="btn btn-lg btn-outline-primary mt-3">Go Home</a>
            </div>
        </div>
    </div>
</section>
@endsection
