<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function create() { return view('auth.index', ['showRegister' => false]); }
    public function store(Request $request)
    {
        $request->merge(['email'=>strtolower(trim((string)$request->input('email')))]);
        $credentials = $request->validate(['email'=>'required|email','password'=>'required|string']);
        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            $message = __('Email yoki parol noto‘g‘ri.');
            if ($request->expectsJson()) return response()->json(['message' => $message, 'errors' => ['email' => [$message]]], 422);
            return back()->withErrors(['email' => $message])->onlyInput('email');
        }
        if (!Auth::user()->email_verified) {
            Auth::logout();
            $message = __('Avval email manzilingizni tasdiqlang.');
            if ($request->expectsJson()) return response()->json(['message' => $message, 'errors' => ['email' => [$message]]], 422);
            return back()->withErrors(['email' => $message]);
        }
        $request->session()->regenerate();
        $redirect = $request->session()->pull('url.intended', route('shop.index'));
        if ($request->expectsJson()) return response()->json(['message' => __('Tizimga muvaffaqiyatli kirdingiz.'), 'redirect' => $redirect]);
        return redirect()->to($redirect);
    }
    public function destroy(Request $request) { Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken(); return redirect()->route('shop.index'); }
}
