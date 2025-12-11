<x-guest-layout>
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
        Verify your email
    </h2>
    
    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 p-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="mt-8 flex items-center justify-between gap-4">
        <form method="POST" action="{{ route('verification.send') }}" class="flex-1">
            @csrf
            <x-primary-button class="w-full">
                {{ __('Resend verification email') }}
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="flex-1">
            @csrf
            <x-secondary-button type="submit" class="w-full">
                {{ __('Log out') }}
            </x-secondary-button>
        </form>
    </div>
</x-guest-layout>
