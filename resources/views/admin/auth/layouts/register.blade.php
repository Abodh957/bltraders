@extends('admin.auth.layouts.app')
@section('content')
<main class="auth-minimal-wrapper">
    <div class="auth-minimal-inner">
        <div class="minimal-card-wrapper">
            <div class="card mb-4 mt-5 mx-4 mx-sm-0 position-relative">
                <div
                    class="wd-50 bg-white p-2 rounded-circle shadow-lg position-absolute translate-middle top-0 start-50">
                    <img src="assets/images/logo-abbr.png" alt="" class="img-fluid">
                </div>
                <div class="card-body p-sm-5">
                    <h2 class="fs-20 fw-bolder mb-4">Register</h2>
                    <h4 class="fs-13 fw-bold mb-2">Manage all your Duralux crm</h4>
                    <p class="fs-12 fw-medium text-muted">Let's get you all setup, so you can verify your personal
                        account and begine setting up your profile.</p>
                    <form action="index.html" class="w-100 mt-4 pt-2">
                        <div class="mb-4">
                            <input type="text" class="form-control" placeholder="Full Name" required>
                        </div>
                        <div class="mb-4">
                            <input type="email" class="form-control" placeholder="Email" required>
                        </div>
                        <div class="mb-4">
                            <input type="tel" class="form-control" placeholder="Username" required>
                        </div>
                        <div class="mb-4 generate-pass">
                            <div class="input-group field">
                                <input type="password" class="form-control password" id="newPassword"
                                    placeholder="Password Confirm">
                                <div class="input-group-text c-pointer gen-pass" data-bs-toggle="tooltip"
                                    title="Generate Password"><i class="feather-hash"></i></div>
                                <div class="input-group-text border-start bg-gray-2 c-pointer show-pass"
                                    data-bs-toggle="tooltip" title="Show/Hide Password"><i></i></div>
                            </div>
                            <div class="progress-bar mt-2">
                                <div></div>
                                <div></div>
                                <div></div>
                                <div></div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <input type="password" class="form-control" placeholder="Password again" required>
                        </div>
                        <div class="mt-4">
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input" id="receiveMial" required>
                                <label class="custom-control-label c-pointer text-muted" for="receiveMial"
                                    style="font-weight: 400 !important">Yes, I wnat to receive Duralux community
                                    emails</label>
                            </div>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="termsCondition" required>
                                <label class="custom-control-label c-pointer text-muted" for="termsCondition"
                                    style="font-weight: 400 !important">I agree to all the <a href="">Terms &amp;
                                        Conditions</a> and <a href="">Fees</a>.</label>
                            </div>
                        </div>
                        <div class="mt-5">
                            <button type="submit" class="btn btn-lg btn-primary w-100">Create Account</button>
                        </div>
                    </form>
                    <div class="mt-5 text-muted">
                        <span>Already have an account?</span>
                        <a href="auth-login-minimal.html" class="fw-bold">Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection