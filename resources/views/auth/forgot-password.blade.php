@extends('layouts.auth')

@section('content')
<div class="bg-slate-800/90 backdrop-blur rounded-2xl shadow-2xl border border-slate-700 p-6 max-w-md mx-auto">

    {{-- LOGO --}}
    <div class="text-center mb-6">


             <img src="{{ asset('images/logo-uniba.jpeg') }}"
     alt="UNIBA"
     class="mx-auto h-16 w-16 rounded-full
            bg-white/90 p-1.5
            ring-1 ring-white/20
            shadow-lg mb-3">


        <span class="inline-block text-xs px-3 py-1 rounded-full bg-red-500/10 text-red-400 mb-3">
            🔒 Kebijakan Keamanan Sistem
        </span>

        <h1 class="text-xl font-bold text-white mt-2">
            Lupa Password
        </h1>

        <p class="text-sm text-slate-400 mt-1">
            Sistem Absensi Digital UNIBA
        </p>
    </div>

    {{-- INFO CARD --}}
    <div class="bg-slate-900 border border-slate-700 rounded-xl p-5 text-slate-300 text-sm leading-relaxed">

        <div class="flex items-start gap-3 mb-4">
            <div class="text-lg">⚠️</div>
            <p>
                Demi menjaga <b>keamanan dan integritas data</b>, sistem ini
                <b>tidak menyediakan reset password otomatis</b>.
            </p>
        </div>

        <p class="mb-3">
            Jika Anda adalah <b>Dosen</b> dan mengalami lupa password, silakan
            menghubungi pihak resmi berikut:
        </p>

        <div class="bg-slate-800 border border-slate-700 rounded-lg p-3 mb-3">
            <ul class="space-y-2">
                <li>🏢 <b>IT Support Kampus</b></li>
                <li>👤 <b>Admin Sistem Absensi UNIBA</b></li>
            </ul>
        </div>

        <p class="text-xs text-slate-400">
            Reset password hanya dilakukan oleh admin setelah verifikasi identitas.
        </p>
    </div>

    {{-- ACTION --}}
    <div class="mt-6 flex flex-col gap-3 text-center">
        <a href="{{ route('login') }}"
           class="inline-flex justify-center items-center gap-2 bg-indigo-600 hover:bg-indigo-700 transition text-white py-2 rounded-lg font-semibold">
            ← Kembali ke Login
        </a>

        <p class="text-xs text-slate-500">
            Butuh bantuan lebih lanjut? Hubungi IT Support Kampus.
        </p>
    </div>

    <p class="text-center text-xs text-slate-500 mt-6">
        © {{ date('Y') }} Universitas Bina Bangsa
    </p>

</div>
@endsection
