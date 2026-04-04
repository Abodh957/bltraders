@extends('admin.auth.layouts.app')
@section('content')
<main class="auth-minimal-wrapper">
    <div class="auth-minimal-inner">
        <div class="minimal-card-wrapper">
            <div class="card mb-4 mt-5 mx-4 mx-sm-0 position-relative">
                <div
                    class="wd-50 bg-white p-2 rounded-circle shadow-lg position-absolute translate-middle top-0 start-50">
                    <img src="{{config('custom.public_path') . '/adminAssets/assets/images/logo-abbr.png'}}" alt="" class="img-fluid">
                </div>
                <div class="card-body p-sm-5">
                    <h2 class="fs-20 fw-bolder mb-4">Reset your password</h2>
                    <h4 class="fs-13 fw-bold mb-2">Enter new password</h4>
                    <form action="{{route('password.store')}}" method="post" class="w-100 mt-4 pt-2">
                        @csrf
                        <input class="form-control" name="token" hidden placeholder="Enter token"
                            value="{{ $token ?? '' }}">
                        <div class="mb-4">
                            <input class="form-control  @error('password') is-invalid @enderror"  type="password"
                                name="password" placeholder="Enter new password">
                        </div>
                        <div class="mb-4">
                            <input class="form-control @error('password_confirmation') is-invalid @enderror" type="password"
                                name="password_confirmation" placeholder="Enter confirm password">
                        </div>
                        <div class="mt-5">
                            <button type="submit" class="btn btn-lg btn-primary w-100">Reset Now</button>
                        </div>
                    </form>
                    <div class="mt-5 text-muted">
                        <span> Don't want to reset?</span>
                        <a href="{{route('login')}}" class="fw-bold">Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
