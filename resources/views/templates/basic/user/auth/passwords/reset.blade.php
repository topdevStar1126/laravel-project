@extends($activeTemplate . 'layouts.frontend')
@section('content')
    <section class="py-120">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-7 col-xl-5">
                    <div class="card custom--card">
                        <div class="card-header">
                            <h5 class="card-title">@lang('Reset Password')</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <p>@lang('Your account is verified successfully. Now you can change your password. Please enter a strong password and don\'t share it with anyone.')</p>
                            </div>
                            <form method="POST" action="{{ route('user.password.update') }}">
                                @csrf
                                <input type="hidden" name="email" value="{{ $email }}">
                                <input type="hidden" name="token" value="{{ $token }}">
                                <div class="form-group">
                                    <label class="form--label">@lang('Password')</label>
                                    <input type="password" class="form-control form--control form--control--sm @if ($general->secure_password) secure-password @endif" name="password" required>
                                </div>
                                <div class="form-group">
                                    <label class="form--label">@lang('Confirm Password')</label>
                                    <input type="password" class="form-control form--control form--control--sm" name="password_confirmation" required>
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn--sm btn--base w-100"> @lang('Submit')</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('style')
    <style>
        .hover-input-popup .input-popup {
            bottom: 70%;
        }
    </style>
@endpush

@if ($general->secure_password)
    @push('script-lib')
        <script src="{{ asset('assets/global/js/secure_password.js') }}"></script>
    @endpush
@endif
