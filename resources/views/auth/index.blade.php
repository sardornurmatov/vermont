@extends('layouts.app')

@section('title', ($showRegister ? __('Ro‘yxatdan o‘tish') : __('Kirish')).' — Vermontshop')

@section('content')
<section class="auth-wrap">
    <div class="auth-stage">
        <div class="auth-flipper {{ $showRegister ? 'is-flipped' : '' }}" id="auth-flipper">
            <section class="auth-face auth-front" aria-label="{{ __('Kirish') }}">
                <div class="auth-card">
                    <p class="eyebrow auth-brand">VERMONTSHOP</p>
                    <h1>{{ __('Kirish') }}</h1>
                    <p class="auth-subtitle">{{ __('Hisobingizga kiring') }}</p>

                    <div class="auth-feedback" data-feedback="login" role="alert" aria-live="polite"></div>
                    <form method="post" action="{{ route('login.store') }}" data-ajax-auth>
                        @csrf
                        <label class="sr-only" for="login-email">{{ __('Email') }}</label>
                        <div class="auth-field">
                            <svg class="auth-field-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="m3 7 9 6 9-6"></path></svg>
                            <input class="auth-input" id="login-email" type="email" name="email" placeholder="{{ __('Email') }}" required autocomplete="email">
                        </div>

                        <label class="sr-only" for="login-password">{{ __('Parol') }}</label>
                        <div class="auth-field">
                            <svg class="auth-field-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2"></rect><path d="M8 10V7a4 4 0 0 1 8 0v3"></path></svg>
                            <input class="auth-input" id="login-password" type="password" name="password" placeholder="{{ __('Parol') }}" required autocomplete="current-password">
                        </div>

                        <label class="remember"><input type="checkbox" name="remember"> {{ __('Eslab qolish') }}</label>
                        <button class="button button-dark full-button" type="submit">{{ __('Kirish') }}</button>
                    </form>
                    <p class="auth-bottom">{{ __('Hisobingiz yo‘qmi?') }}
                        <button class="auth-switch" type="button" data-auth-flip>{{ __('Ro‘yxatdan o‘ting') }}</button>
                    </p>
                </div>
            </section>

            <section class="auth-face auth-back" aria-label="{{ __('Ro‘yxatdan o‘tish') }}">
                <div class="auth-card">
                    <p class="eyebrow auth-brand">VERMONTSHOP</p>
                    <h1>{{ __('Ro‘yxatdan o‘tish') }}</h1>
                    <p class="auth-subtitle">{{ __('Yangi hisob yarating') }}</p>

                    <div class="auth-feedback" data-feedback="register" role="alert" aria-live="polite"></div>
                    <form method="post" action="{{ route('register.store') }}" data-ajax-auth data-availability-url="{{ route('register.availability') }}" data-checking-text="{{ __('Tekshirilmoqda…') }}" data-invalid-email-text="{{ __('Email manzilini to‘liq kiriting.') }}" data-network-error-text="{{ __('Server bilan bog‘lanib bo‘lmadi. Internet yoki serverni tekshiring.') }}" data-request-error-text="{{ __('So‘rov bajarilmadi. Qayta urinib ko‘ring.') }}">
                        @csrf
                        <label class="sr-only" for="register-name">{{ __('To‘liq ism') }}</label>
                        <div class="auth-field">
                            <svg class="auth-field-icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"></circle><path d="M4 21a8 8 0 0 1 16 0"></path></svg>
                            <input class="auth-input" id="register-name" name="name" placeholder="{{ __('To‘liq ism') }}" required autocomplete="name" minlength="2" maxlength="120" data-availability="name" aria-describedby="register-name-hint">
                        </div>
                        <small class="availability-hint" id="register-name-hint" data-hint-for="name" aria-live="polite"></small>

                        <label class="sr-only" for="register-email">{{ __('Gmail yoki email') }}</label>
                        <div class="auth-field">
                            <svg class="auth-field-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="m3 7 9 6 9-6"></path></svg>
                            <input class="auth-input" id="register-email" type="email" name="email" placeholder="{{ __('Gmail yoki email') }}" required autocomplete="email" data-availability="email" aria-describedby="register-email-hint">
                        </div>
                        <small class="availability-hint" id="register-email-hint" data-hint-for="email" aria-live="polite"></small>

                        <label class="sr-only" for="register-password">{{ __('Parol') }}</label>
                        <div class="auth-field">
                            <svg class="auth-field-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2"></rect><path d="M8 10V7a4 4 0 0 1 8 0v3"></path></svg>
                            <input class="auth-input" id="register-password" type="password" name="password" placeholder="{{ __('Parol') }}" required autocomplete="new-password" minlength="8">
                        </div>

                        <label class="sr-only" for="register-confirm">{{ __('Parolni qayta kiriting') }}</label>
                        <div class="auth-field">
                            <svg class="auth-field-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2"></rect><path d="M8 10V7a4 4 0 0 1 8 0v3"></path></svg>
                            <input class="auth-input" id="register-confirm" type="password" name="password_confirmation" placeholder="{{ __('Parolni tasdiqlang') }}" required autocomplete="new-password" minlength="8">
                        </div>

                        <button class="button button-dark full-button" type="submit">{{ __('Hisob yaratish') }}</button>
                    </form>
                    <p class="auth-bottom">{{ __('Hisobingiz bormi?') }}
                        <button class="auth-switch" type="button" data-auth-flip>{{ __('Kirish') }}</button>
                    </p>
                </div>
            </section>
        </div>
    </div>
</section>
@endsection
