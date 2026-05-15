<x-guest-layout>
    <div class="mb-8">
        <p class="font-mono text-xs uppercase tracking-[0.22em] text-[#bf5945]">Admin access</p>
        <h2 class="mt-3 font-serif text-4xl leading-none text-[#17130e]">Iniciar sesion</h2>
        <p class="mt-3 text-sm leading-6 text-[#877766]">Usa tu cuenta autorizada para entrar al panel local.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" class="font-mono text-xs uppercase tracking-[0.16em] text-[#3f362d]" />
            <x-text-input id="email" class="mt-2 block w-full rounded-none border-[#17130e]/20 bg-white/70 px-4 py-3 text-[#17130e] shadow-none focus:border-[#bf5945] focus:ring-[#bf5945]" type="email" name="email" :value="old('email', 'ingjosue.cardona@gmail.com')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" class="font-mono text-xs uppercase tracking-[0.16em] text-[#3f362d]" />

            <x-text-input id="password" class="mt-2 block w-full rounded-none border-[#17130e]/20 bg-white/70 px-4 py-3 text-[#17130e] shadow-none focus:border-[#bf5945] focus:ring-[#bf5945]"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="border-[#17130e]/25 bg-white/70 text-[#9d3f31] shadow-sm focus:ring-[#bf5945]" name="remember">
                <span class="ms-2 text-sm text-[#877766]">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex flex-col-reverse gap-4 pt-2 sm:flex-row sm:items-center sm:justify-between">
            @if (Route::has('password.request'))
                <a class="text-sm text-[#877766] underline decoration-[#bf5945]/40 underline-offset-4 transition hover:text-[#17130e] focus:outline-none focus:ring-2 focus:ring-[#bf5945] focus:ring-offset-2" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="justify-center rounded-none border border-[#9d3f31] bg-[#9d3f31] px-5 py-3 font-mono text-xs uppercase tracking-[0.16em] text-white shadow-none transition hover:bg-[#bf5945] focus:bg-[#bf5945] active:bg-[#7d3127] sm:min-w-32">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
