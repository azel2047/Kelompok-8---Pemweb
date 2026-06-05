<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Models\User;
use App\Models\Siswa;
use App\Models\Kelas;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            if (Auth::user()->isSiswa()) {
                return redirect()->route('portal.siswa');
            }
            return redirect('/admin');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();
            if ($user->isSiswa()) {
                return redirect()->intended(route('portal.siswa'));
            }

            // If Admin or Guru, they can be redirected to Filament admin dashboard
            return redirect('/admin');
        }

        return back()->withErrors([
            'email' => 'Kredensial yang diberikan tidak cocok dengan catatan kami.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('portal.siswa');
        }
        $kelas = Kelas::orderBy('nama_kelas')->get();
        return view('auth.register', compact('kelas'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(6)],
            'nisn'     => ['required', 'string', 'max:20', 'unique:siswa,nisn'],
            'kelas_id' => ['required', 'exists:kelas,id'],
        ], [
            'name.required'     => 'Nama lengkap wajib diisi.',
            'email.unique'      => 'Email ini sudah terdaftar.',
            'password.confirmed'=> 'Konfirmasi password tidak cocok.',
            'password.min'      => 'Password minimal 6 karakter.',
            'nisn.unique'       => 'NISN ini sudah terdaftar.',
            'kelas_id.required' => 'Pilih kelas Anda.',
            'kelas_id.exists'   => 'Kelas yang dipilih tidak valid.',
        ]);

        // Create the user account
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'Siswa',
        ]);

        // Create the student profile linked to user
        Siswa::create([
            'user_id'       => $user->id,
            'kelas_id'      => $request->kelas_id,
            'nisn'          => $request->nisn,
            'qr_code_token' => 'TOKEN_' . strtoupper(uniqid()) . '_' . $user->id,
        ]);

        // Auto-login after registration
        Auth::login($user);

        return redirect()->route('portal.siswa')->with('success', 'Pendaftaran berhasil! Selamat datang, ' . $user->name . '.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar.');
    }
}

