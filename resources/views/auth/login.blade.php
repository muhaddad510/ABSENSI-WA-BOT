@extends('layouts.auth')

@section('content')
<div class="w-full max-w-md mx-auto">

    <div class="bg-slate-800/90 backdrop-blur
                rounded-2xl shadow-2xl
                border border-slate-700
                px-8 py-7">

        {{-- LOGO & TITLE --}}
        <div class="text-center mb-6">
            <img src="{{ asset('images/logo-uniba.jpeg') }}"
                 alt="UNIBA"
                 class="mx-auto h-16 w-16 rounded-full mb-3 shadow">

            <h1 class="text-lg font-semibold text-white tracking-wide">
                Smart Login UNIBA
            </h1>

            <p class="text-sm text-slate-400">
                Sistem Absensi Digital
            </p>
        </div>

        {{-- SESSION STATUS --}}
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            {{-- EMAIL --}}
            <div>
                <x-input-label for="email"
                               value="Email"
                               class="text-slate-300 text-sm" />

                <x-text-input id="email"
                              name="email"
                              type="email"
                              required
                              autofocus
                              autocomplete="username"
                              class="mt-1 block w-full
                                     bg-slate-900 border-slate-700
                                     text-white placeholder-slate-500
                                     focus:border-indigo-500
                                     focus:ring-indigo-500" />

                <x-input-error :messages="$errors->get('email')" class="mt-1" />
            </div>

            {{-- PASSWORD --}}
            <div>
                <x-input-label for="password"
                               value="Password"
                               class="text-slate-300 text-sm" />

                <x-text-input id="password"
                              name="password"
                              type="password"
                              required
                              autocomplete="current-password"
                              class="mt-1 block w-full
                                     bg-slate-900 border-slate-700
                                     text-white placeholder-slate-500
                                     focus:border-indigo-500
                                     focus:ring-indigo-500" />

                <x-input-error :messages="$errors->get('password')" class="mt-1" />
            </div>

            {{-- REMEMBER & FORGOT --}}
            <div class="flex items-center justify-between text-sm text-slate-400">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox"
                           name="remember"
                           class="rounded border-slate-600 bg-slate-900
                                  text-indigo-600 focus:ring-indigo-500">
                    Remember me
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                       class="hover:text-white transition">
                        Lupa password?
                    </a>
                @endif
            </div>

            {{-- BUTTON --}}
            <button type="submit"
                    class="w-full mt-2
                           bg-indigo-600 hover:bg-indigo-700
                           active:bg-indigo-800
                           transition-all duration-200
                           text-white py-2.5 rounded-lg
                           font-semibold tracking-wide">
                Log In
            </button>
        </form>

        {{-- FOOTER --}}
        <p class="text-center text-xs text-slate-500 mt-6">
            © {{ date('Y') }} Universitas Bina Bangsa
        </p>

    </div>
</div>
@endsection
