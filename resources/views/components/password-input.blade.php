@props(['id', 'name', 'required' => false, 'autocomplete' => 'new-password', 'showStrength' => true, 'showRequirements' => true])

<div class="password-field-wrapper">
    <div class="relative">
        <x-text-input 
            :id="$id" 
            :name="$name" 
            type="password" 
            class="block mt-1 w-full pr-10" 
            :required="$required"
            :autocomplete="$autocomplete"
            placeholder="••••••••"
            data-password-validate
            {{ $attributes }}
        />
        
        <!-- Toggle Password Visibility -->
        <button 
            type="button" 
            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
            data-toggle-password="{{ $id }}"
        >
            <span class="show-icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
            </span>
            <span class="hide-icon hidden">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                </svg>
            </span>
        </button>
    </div>
    
    @if($showStrength)
    <!-- Password Strength Indicator -->
    <div class="password-strength-indicator mt-2">
        <div class="flex items-center justify-between mb-1">
            <span class="text-xs text-gray-600 dark:text-gray-400">Password strength:</span>
            <span class="strength-text text-xs font-medium"></span>
        </div>
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
            <div class="strength-bar h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
        </div>
    </div>
    @endif
    
    @if($showRequirements)
    <!-- Password Requirements -->
    <div class="password-requirements mt-3 space-y-1.5">
        <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">Password must contain:</p>
        
        <div class="flex items-center space-x-2" data-requirement="length">
            <span class="requirement-icon">
                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
            </span>
            <span class="requirement-text text-xs text-gray-500 dark:text-gray-400">At least 8 characters</span>
        </div>
        
        <div class="flex items-center space-x-2" data-requirement="lowercase">
            <span class="requirement-icon">
                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
            </span>
            <span class="requirement-text text-xs text-gray-500 dark:text-gray-400">Contains lowercase letter (a-z)</span>
        </div>
        
        <div class="flex items-center space-x-2" data-requirement="uppercase">
            <span class="requirement-icon">
                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
            </span>
            <span class="requirement-text text-xs text-gray-500 dark:text-gray-400">Contains uppercase letter (A-Z)</span>
        </div>
        
        <div class="flex items-center space-x-2" data-requirement="number">
            <span class="requirement-icon">
                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
            </span>
            <span class="requirement-text text-xs text-gray-500 dark:text-gray-400">Contains number (0-9)</span>
        </div>
        
        <div class="flex items-center space-x-2" data-requirement="symbol">
            <span class="requirement-icon">
                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
            </span>
            <span class="requirement-text text-xs text-gray-500 dark:text-gray-400">Contains special character (!@#$%^&*)</span>
        </div>
    </div>
    @endif
</div>
