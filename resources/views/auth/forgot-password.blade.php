<x-guest-layout>
    <!-- Header -->
    <div class="text-center mb-8">
        <h2 class="text-3xl font-bold text-white">
            Forgot Password?
        </h2>
        <p class="mt-4 text-sm text-gray-400">
            {{ __('No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
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
                placeholder="you@example.com"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex flex-col items-center gap-4 mt-6">
            <x-primary-button class="w-full justify-center">
                {{ __('Email Password Reset Link') }}
            </x-primary-button>

            <p class="text-sm text-gray-400">
                Remember your password?
                <a href="{{ route('login') }}" class="font-medium text-primary-500 hover:text-primary-400 hover:underline transition">
                    Back to login
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
