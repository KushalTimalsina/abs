<x-guest-layout>
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
        Create account
    </h2>

    <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-6" x-data="{ userType: 'customer' }">
        @csrf

        <!-- User Type Selection -->
        <div>
            <x-input-label for="user_type" value="I want to register as" />
            <div class="grid grid-cols-2 gap-4 mt-2">
                <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all"
                       :class="userType === 'customer' ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-gray-400'">
                    <input type="radio" name="user_type" value="customer" x-model="userType" class="w-4 h-4 text-blue-600" checked>
                    <div class="ml-3">
                        <div class="font-medium text-gray-900 dark:text-white">Customer</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Book appointments</div>
                    </div>
                </label>
                <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all"
                       :class="userType === 'admin' ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-gray-400'">
                    <input type="radio" name="user_type" value="admin" x-model="userType" class="w-4 h-4 text-blue-600">
                    <div class="ml-3">
                        <div class="font-medium text-gray-900 dark:text-white">Business Owner</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Manage bookings</div>
                    </div>
                </label>
            </div>
            <x-input-error :messages="$errors->get('user_type')" class="mt-2" />
        </div>

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Your name')" />
            <x-text-input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="John Doe" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Your email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="name@company.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Phone -->
        <div>
            <x-input-label for="phone" value="Phone (optional)" />
            <x-text-input id="phone" type="tel" name="phone" :value="old('phone')" autocomplete="tel" placeholder="+977 9800000000" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <!-- Organization Name (shown only for admin) -->
        <div x-show="userType === 'admin'" x-transition>
            <x-input-label for="organization_name" value="Organization Name" />
            <x-text-input id="organization_name" type="text" name="organization_name" :value="old('organization_name')" placeholder="My Business" />
            <x-input-error :messages="$errors->get('organization_name')" class="mt-2" />
        </div>

        <!-- Subscription Plan Selection (shown only for admin) -->
        <div x-show="userType === 'admin'" x-transition x-data="{ selectedPlan: '{{ old('subscription_plan_id') }}' }">
            <x-input-label value="Choose Your Plan" />
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3">
                @foreach($plans as $plan)
                <label class="relative flex flex-col p-4 border-2 rounded-lg cursor-pointer transition-all hover:shadow-lg"
                       :class="selectedPlan == '{{ $plan->id }}' ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600'">
                    <input type="radio" name="subscription_plan_id" value="{{ $plan->id }}" x-model="selectedPlan" class="sr-only">
                    
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $plan->name }}</h3>
                        <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
                             :class="selectedPlan == '{{ $plan->id }}' ? 'border-blue-600' : 'border-gray-300'">
                            <div class="w-3 h-3 rounded-full bg-blue-600" x-show="selectedPlan == '{{ $plan->id }}'"></div>
                        </div>
                    </div>
                    
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                        NPR {{ number_format($plan->price, 2) }}
                        <span class="text-sm font-normal text-gray-500">/{{ $plan->duration_days }} days</span>
                    </div>
                    
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">{{ $plan->description }}</p>
                    
                    <ul class="space-y-2 text-sm">
                        <li class="flex items-center text-gray-700 dark:text-gray-300">
                            <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            {{ $plan->max_team_members }} team members
                        </li>
                        <li class="flex items-center text-gray-700 dark:text-gray-300">
                            <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            {{ $plan->slot_scheduling_days }} days scheduling
                        </li>
                        @if($plan->allowsPaymentMethod('online'))
                        <li class="flex items-center text-gray-700 dark:text-gray-300">
                            <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Online payments
                        </li>
                        @endif
                    </ul>
                </label>
                @endforeach
            </div>
            <x-input-error :messages="$errors->get('subscription_plan_id')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Your password')" />
            <x-password-input id="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm password')" />
            <x-password-input id="password_confirmation" name="password_confirmation" required autocomplete="new-password" :showStrength="false" :showRequirements="false" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <x-primary-button class="w-full">
            {{ __('Create an account') }}
        </x-primary-button>

        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
            {{ __('Already have an account?') }} <a href="{{ route('login') }}" class="text-blue-700 hover:underline dark:text-blue-500">{{ __('Sign in') }}</a>
        </div>
    </form>
</x-guest-layout>
