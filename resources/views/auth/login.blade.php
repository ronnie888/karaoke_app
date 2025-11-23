<x-guest-layout>
    <!-- Header -->
    <div class="text-center mb-8">
        <h2 class="text-3xl font-bold text-white">
            Welcome Back!
        </h2>
        <p class="mt-2 text-sm text-gray-400">
            Sign in to continue your karaoke journey
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" class="text-gray-300" />
            <x-text-input
                id="email"
                class="block mt-1 w-full bg-dark-800 border-dark-700 text-white placeholder-gray-500 focus:border-primary-500 focus:ring-primary-500"
                type="email"
                name="email"
                :value="old('email')"
                required
                autofocus
                autocomplete="username"
                placeholder="you@example.com"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" class="text-gray-300" />
            <x-text-input
                id="password"
                class="block mt-1 w-full bg-dark-800 border-dark-700 text-white placeholder-gray-500 focus:border-primary-500 focus:ring-primary-500"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                placeholder="••••••••"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" class="rounded bg-dark-800 border-dark-700 text-primary-600 shadow-sm focus:ring-primary-500 focus:ring-offset-dark-850 transition" name="remember">
                <span class="ms-2 text-sm font-medium text-gray-300 hover:text-white transition">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-between mt-6 gap-4">
            @if (Route::has('password.request'))
                <a class="text-sm font-medium text-primary-500 hover:text-primary-400 hover:underline rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="w-full sm:w-auto justify-center">
                {{ __('Log in') }}
            </x-primary-button>
        </div>

        <!-- Register Link -->
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-400">
                Don't have an account?
                <a href="{{ route('register') }}" class="font-medium text-primary-500 hover:text-primary-400 hover:underline transition">
                    Sign up
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
