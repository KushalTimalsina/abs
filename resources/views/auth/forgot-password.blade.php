<x-guest-layout>
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
        Forgot your password?
    </h2>
    
    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
        {{ __('No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </p>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-6">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Your email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus placeholder="name@company.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <x-primary-button class="w-full">
            {{ __('Email password reset link') }}
        </x-primary-button>

        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
            <a href="{{ route('login') }}" class="text-blue-700 hover:underline dark:text-blue-500">{{ __('Back to sign in') }}</a>
        </div>
    </form>
</x-guest-layout>
