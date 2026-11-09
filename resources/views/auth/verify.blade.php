@extends('layouts.app')
@section('title', __('Emailni tasdiqlash').' — Vermontshop')
@section('content')
<section class="auth-wrap"><div class="auth-orb"><div class="auth-card"><p class="eyebrow">{{ __('YANA BIR QADAM') }}</p><h1>{{ __('Emailni tasdiqlang') }}</h1><p class="auth-subtitle">{{ __('Emailingizga yuborilgan 6 xonali kodni kiriting. Kod 5 daqiqa amal qiladi.') }}</p><form method="post" action="{{ route('verification.verify') }}">@csrf<label>{{ __('Tasdiqlash kodi') }}</label><input class="auth-input code-input" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="000000" required><button class="button button-dark full-button">{{ __('Emailni tasdiqlash') }}</button></form><form method="post" action="{{ route('verification.resend') }}">@csrf<button class="text-button full-button">{{ __('Yangi kod yuborish') }}</button></form></div></div></section>
@endsection
