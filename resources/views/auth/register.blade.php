<x-guest-layout>
    <!-- Header -->
    <div class="text-center mb-8">
        <h2 class="text-3xl font-bold text-white">
            Join Karaoke Tube
        </h2>
        <p class="mt-2 text-sm text-gray-400">
            Create your account and start singing!
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" class="text-gray-300" />
            <x-text-input
                id="name"
                class="block mt-1 w-full bg-dark-800 border-dark-700 text-white placeholder-gray-500 focus:border-primary-500 focus:ring-primary-500"
                type="text"
                name="name"
                :value="old('name')"
                required
                autofocus
                autocomplete="name"
                placeholder="Your full name"
            />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" class="text-gray-300" />
            <x-text-input
                id="email"
                class="block mt-1 w-full bg-dark-800 border-dark-700 text-white placeholder-gray-500 focus:border-primary-500 focus:ring-primary-500"
                type="email"
                name="email"
                :value="old('email')"
                required
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
                autocomplete="new-password"
                placeholder="••••••••"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-gray-300" />
            <x-text-input
                id="password_confirmation"
                class="block mt-1 w-full bg-dark-800 border-dark-700 text-white placeholder-gray-500 focus:border-primary-500 focus:ring-primary-500"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                placeholder="••••••••"
            />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex flex-col items-center justify-center mt-6 gap-4">
            <x-primary-button class="w-full justify-center">
                {{ __('Register') }}
            </x-primary-button>

            <p class="text-sm text-gray-400">
                Already registered?
                <a href="{{ route('login') }}" class="font-medium text-primary-500 hover:text-primary-400 hover:underline transition">
                    Sign in
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
