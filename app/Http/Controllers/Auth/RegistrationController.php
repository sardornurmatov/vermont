<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\VerifyRegistrationCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegistrationController extends Controller
{
    public function create() { return view('auth.index', ['showRegister' => true]); }

    public function store(Request $request)
    {
        $request->merge([
            'name' => preg_replace('/\s+/u', ' ', trim((string) $request->input('name'))),
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:120', function ($attribute, $value, $fail) {
                $normalized = Str::lower(trim((string) $value));
                if (User::query()->whereRaw('LOWER(TRIM(name)) = ?', [$normalized])->exists()) {
                    $fail(__('Bu ism band. Boshqa ism kiriting.'));
                }
            }],
            'email' => ['required', 'email:rfc', 'max:190', function ($attribute, $value, $fail) {
                if (User::query()->whereRaw('LOWER(email) = ?', [Str::lower(trim((string) $value))])->exists()) {
                    $fail(__('Bu email avval ro‘yxatdan o‘tgan.'));
                }
            }],
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Email kodini hozircha talab qilmaymiz; sozlamadan keyin tasdiqlashni yoqish mumkin.
        if (!config('services.registration.email_verification', false)) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'email_verified' => true,
                'email_verified_at' => now(),
            ]);

            Auth::login($user);
            $request->session()->regenerate();
            $message = __('Ro‘yxatdan muvaffaqiyatli o‘tdingiz.');
            $request->session()->flash('status', $message);

            if ($request->expectsJson()) {
                return response()->json(['message' => $message, 'redirect' => route('shop.index')]);
            }

            return redirect()->route('shop.index')->with('status', $message);
        }

        $code = (string) random_int(100000, 999999);
        $user = User::create(['name'=>$data['name'],'email'=>$data['email'],'password'=>$data['password'],'verification_code_hash'=>Hash::make($code),'verification_expires_at'=>now()->addMinutes(5)]);
        $request->session()->put('pending_verification_email', $user->email);
        try { $user->notify(new VerifyRegistrationCode($code)); }
        catch (\Throwable $e) {
            $user->delete();
            $message = __('Email yuborilmadi. Pochta sozlamalarini administrator tekshirishi kerak.');
            if ($request->expectsJson()) return response()->json(['message' => $message, 'errors' => ['email' => [$message]]], 500);
            return back()->withInput($request->except('password','password_confirmation'))->withErrors(['email' => $message]);
        }
        $message = __('Tasdiqlash kodi emailingizga yuborildi.');
        $request->session()->flash('status', $message);
        if ($request->expectsJson()) return response()->json(['message' => $message, 'redirect' => route('verification.notice')]);
        return redirect()->route('verification.notice')->with('status', $message);
    }

    public function checkAvailability(Request $request)
    {
        $data = $request->validate([
            'field' => 'required|in:name,email',
            'value' => 'required|string|max:190',
        ]);

        $field = $data['field'];
        $value = trim($data['value']);

        if ($field === 'name') {
            if (mb_strlen($value) < 2) {
                return response()->json([
                    'valid' => false,
                    'available' => false,
                    'message' => __('Ismni kamida 2 ta belgida kiriting.'),
                ]);
            }

            $exists = User::query()
                ->whereRaw('LOWER(TRIM(name)) = ?', [Str::lower($value)])
                ->exists();
        } else {
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                return response()->json([
                    'valid' => false,
                    'available' => false,
                    'message' => __('Email manzilini to‘liq va to‘g‘ri kiriting.'),
                ]);
            }

            $exists = User::query()
                ->whereRaw('LOWER(email) = ?', [Str::lower($value)])
                ->exists();
        }

        return response()->json([
            'valid' => true,
            'available' => !$exists,
            'message' => $exists
                ? ($field === 'name' ? __('Bu ism band.') : __('Bu email avval ro‘yxatdan o‘tgan.'))
                : ($field === 'name' ? __('Bu ism mavjud.') : __('Bu email bo‘sh.')),
        ]);
    }

    public function notice(Request $request)
    {
        abort_unless($request->session()->has('pending_verification_email'), 403);
        return view('auth.verify');
    }

    public function resend(Request $request)
    {
        $email=$request->session()->get('pending_verification_email');
        abort_unless($email,403);
        $user=User::where('email',$email)->firstOrFail();
        if ($user->email_verified) return redirect()->route('login');
        $code=(string) random_int(100000,999999);
        $user->forceFill(['verification_code_hash'=>Hash::make($code),'verification_expires_at'=>now()->addMinutes(5)])->save();
        $user->notify(new VerifyRegistrationCode($code));
        return back()->with('status',__('Yangi kod emailingizga yuborildi.'));
    }

    public function verify(Request $request)
    {
        $request->validate(['code'=>'required|digits:6']);
        $email = $request->session()->get('pending_verification_email');
        abort_unless($email, 403);
        $user = User::where('email', $email)->firstOrFail();
        if (!$user->verification_expires_at || $user->verification_expires_at->isPast() || !Hash::check($request->code, $user->verification_code_hash ?? '')) {
            return back()->withErrors(['code'=>__('Kod noto‘g‘ri yoki muddati tugagan.')]);
        }
        $user->forceFill(['email_verified'=>true,'email_verified_at'=>now(),'verification_code_hash'=>null,'verification_expires_at'=>null])->save();
        Auth::login($user);
        $request->session()->forget('pending_verification_email');
        $request->session()->regenerate();
        return redirect()->route('shop.index')->with('status',__('Email tasdiqlandi. Vermontshop’ga xush kelibsiz!'));
    }
}
