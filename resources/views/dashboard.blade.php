<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    @if(Auth::user()->user_type === 'admin')
        @php
            $currentOrg = Auth::user()->organizations()->first();
            $subscription = $currentOrg?->subscription;
        @endphp

        <!-- Subscription Status Widget -->
        <div class="mb-6">
            <div class="p-6 bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 rounded-lg shadow-lg text-white">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold mb-2">Your Subscription</h3>
                        
                        @if($subscription && $subscription->is_active)
                            <div class="flex items-center space-x-4">
                                <div>
                                    <p class="text-2xl font-bold">{{ $subscription->plan->name }}</p>
                                    <p class="text-blue-100 text-sm">
                                        Active until {{ $subscription->end_date->format('M d, Y') }}
                                        <span class="ml-2">({{ $subscription->end_date->diffForHumans() }})</span>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="bg-white/10 rounded-lg p-3">
                                    <p class="text-blue-100 text-xs">Team Members</p>
                                    <p class="text-lg font-semibold">{{ $subscription->plan->max_team_members ?? '∞' }}</p>
                                </div>
                                <div class="bg-white/10 rounded-lg p-3">
                                    <p class="text-blue-100 text-xs">Services</p>
                                    <p class="text-lg font-semibold">{{ $subscription->plan->max_services ?? '∞' }}</p>
                                </div>
                                <div class="bg-white/10 rounded-lg p-3">
                                    <p class="text-blue-100 text-xs">Price</p>
                                    <p class="text-lg font-semibold">NPR {{ number_format($subscription->plan->price, 2) }}</p>
                                </div>
                            </div>
                        @else
                            <div class="bg-yellow-500/20 border border-yellow-300 rounded-lg p-4 mt-2">
                                <p class="font-semibold">⚠️ No Active Subscription</p>
                                <p class="text-sm text-yellow-100 mt-1">Please subscribe to a plan to activate your account and start using all features.</p>
                            </div>
                        @endif
                    </div>
                    
                    <div class="ml-6">
                        <button onclick="document.getElementById('upgrade-modal').classList.remove('hidden')" 
                                class="px-6 py-3 bg-white text-blue-600 rounded-lg font-semibold hover:bg-blue-50 transition-colors shadow-lg">
                            @if($subscription && $subscription->is_active)
                                Upgrade Plan
                            @else
                                Choose Plan
                            @endif
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upgrade Modal -->
        <div id="upgrade-modal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-5xl shadow-lg rounded-md bg-white dark:bg-gray-800">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Choose Your Plan</h3>
                    <button onclick="document.getElementById('upgrade-modal').classList.add('hidden')" 
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach(\App\Models\SubscriptionPlan::where('is_active', true)->orderBy('price')->get() as $plan)
                        <div class="border-2 {{ $subscription && $subscription->plan_id == $plan->id ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-700' }} rounded-lg p-6 relative">
                            @if($subscription && $subscription->plan_id == $plan->id)
                                <span class="absolute top-0 right-0 bg-blue-500 text-white text-xs px-3 py-1 rounded-bl-lg rounded-tr-lg">
                                    Current Plan
                                </span>
                            @endif
                            
                            <h4 class="text-xl font-bold text-gray-900 dark:text-white mb-2">{{ $plan->name }}</h4>
                            <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">{{ $plan->description }}</p>
                            
                            <div class="mb-4">
                                <span class="text-3xl font-bold text-gray-900 dark:text-white">NPR {{ number_format($plan->price, 0) }}</span>
                                <span class="text-gray-500 dark:text-gray-400 text-sm">/{{ $plan->duration_days }} days</span>
                            </div>
                            
                            <ul class="space-y-2 mb-6">
                                <li class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                                    <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    {{ $plan->max_team_members ?? 'Unlimited' }} Team Members
                                </li>
                                <li class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                                    <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    {{ $plan->max_services ?? 'Unlimited' }} Services
                                </li>
                                <li class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                                    <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    {{ $plan->slot_scheduling_days ?? 365 }} Days Scheduling
                                </li>
                            </ul>
                            
                            @if($subscription && $subscription->plan_id == $plan->id)
                                <button disabled class="w-full px-4 py-2 bg-gray-300 text-gray-500 rounded-lg font-semibold cursor-not-allowed">
                                    Current Plan
                                </button>
                            @else
                                <form action="{{ route('subscription.upgrade') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                    <button type="submit" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition-colors">
                                        @if($subscription)
                                            {{ $plan->price > $subscription->plan->price ? 'Upgrade' : 'Downgrade' }}
                                        @else
                                            Subscribe
                                        @endif
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 gap-4 mb-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Stat Card 1 -->
        <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 sm:p-6 dark:bg-gray-800">
            <div class="w-full">
                <h3 class="text-base font-normal text-gray-500 dark:text-gray-400">New users</h3>
                <span class="text-2xl font-bold leading-none text-gray-900 sm:text-3xl dark:text-white">2,340</span>
                <p class="flex items-center text-base font-normal text-gray-500 dark:text-gray-400">
                    <span class="flex items-center mr-1.5 text-sm text-green-500 dark:text-green-400">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L6.707 7.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        12.5%
                    </span>
                    Since last month
                </p>
            </div>
        </div>

        <!-- Stat Card 2 -->
        <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 sm:p-6 dark:bg-gray-800">
            <div class="w-full">
                <h3 class="text-base font-normal text-gray-500 dark:text-gray-400">Revenue</h3>
                <span class="text-2xl font-bold leading-none text-gray-900 sm:text-3xl dark:text-white">$45,385</span>
                <p class="flex items-center text-base font-normal text-gray-500 dark:text-gray-400">
                    <span class="flex items-center mr-1.5 text-sm text-green-500 dark:text-green-400">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L6.707 7.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        18.2%
                    </span>
                    Since last month
                </p>
            </div>
        </div>

        <!-- Stat Card 3 -->
        <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 sm:p-6 dark:bg-gray-800">
            <div class="w-full">
                <h3 class="text-base font-normal text-gray-500 dark:text-gray-400">Orders</h3>
                <span class="text-2xl font-bold leading-none text-gray-900 sm:text-3xl dark:text-white">385</span>
                <p class="flex items-center text-base font-normal text-gray-500 dark:text-gray-400">
                    <span class="flex items-center mr-1.5 text-sm text-red-600 dark:text-red-500">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M14.707 12.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 14.586V3a1 1 0 012 0v11.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        4.5%
                    </span>
                    Since last month
                </p>
            </div>
        </div>

        <!-- Stat Card 4 -->
        <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 sm:p-6 dark:bg-gray-800">
            <div class="w-full">
                <h3 class="text-base font-normal text-gray-500 dark:text-gray-400">Visitors</h3>
                <span class="text-2xl font-bold leading-none text-gray-900 sm:text-3xl dark:text-white">12,483</span>
                <p class="flex items-center text-base font-normal text-gray-500 dark:text-gray-400">
                    <span class="flex items-center mr-1.5 text-sm text-green-500 dark:text-green-400">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L6.707 7.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        7.1%
                    </span>
                    Since last month
                </p>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 gap-4 mb-4 xl:grid-cols-2">
        <!-- Latest Transactions -->
        <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 sm:p-6 dark:bg-gray-800">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Latest Transactions</h3>
                <a href="#" class="inline-flex items-center p-2 text-sm font-medium rounded-lg text-blue-600 hover:bg-gray-100 dark:text-blue-500 dark:hover:bg-gray-700">
                    View all
                </a>
            </div>
            <div class="flow-root">
                <ul role="list" class="divide-y divide-gray-200 dark:divide-gray-700">
                    <li class="py-3 sm:py-4">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-300" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate dark:text-white">
                                    Payment from Bonnie Green
                                </p>
                                <p class="text-sm text-gray-500 truncate dark:text-gray-400">
                                    Apr 23, 2023
                                </p>
                            </div>
                            <div class="inline-flex items-center text-base font-semibold text-gray-900 dark:text-white">
                                $2,300
                            </div>
                        </div>
                    </li>
                    <li class="py-3 sm:py-4">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-600 dark:text-green-300" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate dark:text-white">
                                    Payment from Jese Leos
                                </p>
                                <p class="text-sm text-gray-500 truncate dark:text-gray-400">
                                    Apr 23, 2023
                                </p>
                            </div>
                            <div class="inline-flex items-center text-base font-semibold text-gray-900 dark:text-white">
                                $5,467
                            </div>
                        </div>
                    </li>
                    <li class="py-3 sm:py-4">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full bg-purple-100 dark:bg-purple-900 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-300" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate dark:text-white">
                                    Payment from Thomas Lean
                                </p>
                                <p class="text-sm text-gray-500 truncate dark:text-gray-400">
                                    Apr 18, 2023
                                </p>
                            </div>
                            <div class="inline-flex items-center text-base font-semibold text-gray-900 dark:text-white">
                                $2,235
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Latest Customers -->
        <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 sm:p-6 dark:bg-gray-800">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Latest Customers</h3>
                <a href="#" class="inline-flex items-center p-2 text-sm font-medium rounded-lg text-blue-600 hover:bg-gray-100 dark:text-blue-500 dark:hover:bg-gray-700">
                    View all
                </a>
            </div>
            <div class="flow-root">
                <ul role="list" class="divide-y divide-gray-200 dark:divide-gray-700">
                    <li class="py-3 sm:py-4">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-gray-600 dark:text-gray-300 font-semibold">
                                    NF
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate dark:text-white">
                                    Neil Sims
                                </p>
                                <p class="text-sm text-gray-500 truncate dark:text-gray-400">
                                    email@windster.com
                                </p>
                            </div>
                            <div class="inline-flex items-center text-base font-semibold text-gray-900 dark:text-white">
                                $320
                            </div>
                        </div>
                    </li>
                    <li class="py-3 sm:py-4">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-gray-600 dark:text-gray-300 font-semibold">
                                    BG
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate dark:text-white">
                                    Bonnie Green
                                </p>
                                <p class="text-sm text-gray-500 truncate dark:text-gray-400">
                                    email@windster.com
                                </p>
                            </div>
                            <div class="inline-flex items-center text-base font-semibold text-gray-900 dark:text-white">
                                $3467
                            </div>
                        </div>
                    </li>
                    <li class="py-3 sm:py-4">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-gray-600 dark:text-gray-300 font-semibold">
                                    MG
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate dark:text-white">
                                    Michael Gough
                                </p>
                                <p class="text-sm text-gray-500 truncate dark:text-gray-400">
                                    email@windster.com
                                </p>
                            </div>
                            <div class="inline-flex items-center text-base font-semibold text-gray-900 dark:text-white">
                                $67
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Activity Feed -->
    <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 sm:p-6 dark:bg-gray-800">
        <h3 class="flex items-center mb-4 text-lg font-semibold text-gray-900 dark:text-white">
            Activity Feed
        </h3>
        <ol class="relative border-l border-gray-200 dark:border-gray-700">
            <li class="mb-10 ml-6">
                <span class="absolute flex items-center justify-center w-8 h-8 bg-blue-100 rounded-full -left-4 ring-4 ring-white dark:ring-gray-800 dark:bg-blue-900">
                    <svg class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                    </svg>
                </span>
                <h4 class="flex items-start mb-1 text-base font-semibold text-gray-900 dark:text-white">Application UI code in Tailwind CSS</h4>
                <time class="block mb-3 text-sm font-normal leading-none text-gray-500 dark:text-gray-400">Released on December 2nd, 2021</time>
                <p class="text-sm font-normal text-gray-500 dark:text-gray-400">Get started with dozens of web components and interactive elements.</p>
            </li>
            <li class="mb-10 ml-6">
                <span class="absolute flex items-center justify-center w-8 h-8 bg-green-100 rounded-full -left-4 ring-4 ring-white dark:ring-gray-800 dark:bg-green-900">
                    <svg class="w-3.5 h-3.5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </span>
                <h4 class="mb-1 text-base font-semibold text-gray-900 dark:text-white">Marketing UI design in Figma</h4>
                <time class="block mb-3 text-sm font-normal leading-none text-gray-500 dark:text-gray-400">Released on December 7th, 2021</time>
                <p class="text-sm font-normal text-gray-500 dark:text-gray-400">All of the pages and components are first designed in Figma.</p>
            </li>
            <li class="ml-6">
                <span class="absolute flex items-center justify-center w-8 h-8 bg-purple-100 rounded-full -left-4 ring-4 ring-white dark:ring-gray-800 dark:bg-purple-900">
                    <svg class="w-3.5 h-3.5 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                    </svg>
                </span>
                <h4 class="mb-1 text-base font-semibold text-gray-900 dark:text-white">E-Commerce UI code in Tailwind CSS</h4>
                <time class="block mb-3 text-sm font-normal leading-none text-gray-500 dark:text-gray-400">Released on December 12th, 2021</time>
                <p class="text-sm font-normal text-gray-500 dark:text-gray-400">Get started with dozens of web components and interactive elements.</p>
            </li>
        </ol>
    </div>

    @if(Auth::user()->user_type === 'admin')
    <script>
        // Auto-open subscription modal if URL has #subscription
        document.addEventListener('DOMContentLoaded', function() {
            if (window.location.hash === '#subscription') {
                const modal = document.getElementById('upgrade-modal');
                if (modal) {
                    modal.classList.remove('hidden');
                }
            }
        });
    </script>
    @endif
</x-app-layout>
