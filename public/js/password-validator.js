// Password Strength Validator
document.addEventListener('DOMContentLoaded', function() {
    // Find all password inputs that need validation
    const passwordInputs = document.querySelectorAll('[data-password-validate]');
    
    passwordInputs.forEach(input => {
        const strengthIndicator = input.closest('.password-field-wrapper')?.querySelector('.password-strength-indicator');
        const requirementsList = input.closest('.password-field-wrapper')?.querySelector('.password-requirements');
        
        if (!strengthIndicator || !requirementsList) return;
        
        input.addEventListener('input', function() {
            const password = this.value;
            const strength = calculatePasswordStrength(password);
            updateStrengthIndicator(strengthIndicator, strength);
            updateRequirements(requirementsList, password);
        });
    });
});

function calculatePasswordStrength(password) {
    let strength = 0;
    
    if (password.length === 0) return 0;
    
    // Length check
    if (password.length >= 8) strength += 20;
    if (password.length >= 12) strength += 10;
    
    // Contains lowercase
    if (/[a-z]/.test(password)) strength += 15;
    
    // Contains uppercase
    if (/[A-Z]/.test(password)) strength += 15;
    
    // Contains numbers
    if (/\d/.test(password)) strength += 20;
    
    // Contains symbols
    if (/[^A-Za-z0-9]/.test(password)) strength += 20;
    
    return Math.min(strength, 100);
}

function updateStrengthIndicator(indicator, strength) {
    const bar = indicator.querySelector('.strength-bar');
    const text = indicator.querySelector('.strength-text');
    
    bar.style.width = strength + '%';
    
    // Remove all strength classes
    bar.classList.remove('bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-green-500');
    
    if (strength === 0) {
        text.textContent = '';
        bar.style.width = '0%';
    } else if (strength < 40) {
        text.textContent = 'Weak';
        text.className = 'strength-text text-xs font-medium text-red-600 dark:text-red-400';
        bar.classList.add('bg-red-500');
    } else if (strength < 60) {
        text.textContent = 'Fair';
        text.className = 'strength-text text-xs font-medium text-orange-600 dark:text-orange-400';
        bar.classList.add('bg-orange-500');
    } else if (strength < 80) {
        text.textContent = 'Good';
        text.className = 'strength-text text-xs font-medium text-yellow-600 dark:text-yellow-400';
        bar.classList.add('bg-yellow-500');
    } else {
        text.textContent = 'Strong';
        text.className = 'strength-text text-xs font-medium text-green-600 dark:text-green-400';
        bar.classList.add('bg-green-500');
    }
}

function updateRequirements(requirementsList, password) {
    const requirements = [
        { id: 'length', test: password.length >= 8, text: 'At least 8 characters' },
        { id: 'lowercase', test: /[a-z]/.test(password), text: 'Contains lowercase letter' },
        { id: 'uppercase', test: /[A-Z]/.test(password), text: 'Contains uppercase letter' },
        { id: 'number', test: /\d/.test(password), text: 'Contains number' },
        { id: 'symbol', test: /[^A-Za-z0-9]/.test(password), text: 'Contains special character' }
    ];
    
    requirements.forEach(req => {
        const item = requirementsList.querySelector(`[data-requirement="${req.id}"]`);
        if (!item) return;
        
        const icon = item.querySelector('.requirement-icon');
        const text = item.querySelector('.requirement-text');
        
        if (req.test) {
            icon.innerHTML = `<svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
            </svg>`;
            text.classList.remove('text-gray-500', 'dark:text-gray-400');
            text.classList.add('text-green-600', 'dark:text-green-400');
        } else {
            icon.innerHTML = `<svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>`;
            text.classList.remove('text-green-600', 'dark:text-green-400');
            text.classList.add('text-gray-500', 'dark:text-gray-400');
        }
    });
}

// Password visibility toggle
document.addEventListener('DOMContentLoaded', function() {
    const toggleButtons = document.querySelectorAll('[data-toggle-password]');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-toggle-password');
            const input = document.getElementById(targetId);
            const showIcon = this.querySelector('.show-icon');
            const hideIcon = this.querySelector('.hide-icon');
            
            if (input.type === 'password') {
                input.type = 'text';
                showIcon.classList.add('hidden');
                hideIcon.classList.remove('hidden');
            } else {
                input.type = 'password';
                showIcon.classList.remove('hidden');
                hideIcon.classList.add('hidden');
            }
        });
    });
});
