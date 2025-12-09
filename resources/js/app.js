// import './bootstrap';
import "flowbite";

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function () {
    // Mobile sidebar toggle
    const sidebar = document.getElementById('sidebar');
    const toggleSidebarMobile = document.getElementById('toggleSidebarMobile');
    const toggleSidebarMobileHamburger = document.getElementById('toggleSidebarMobileHamburger');
    const toggleSidebarMobileClose = document.getElementById('toggleSidebarMobileClose');

    toggleSidebarMobile?.addEventListener('click', function () {
        sidebar.classList.toggle('hidden');
        toggleSidebarMobileHamburger.classList.toggle('hidden');
        toggleSidebarMobileClose.classList.toggle('hidden');
    });

    // Desktop sidebar toggle
    const toggleSidebarDesktop = document.getElementById('toggleSidebarDesktop');
    const mainContent = document.getElementById('main-content');

    toggleSidebarDesktop?.addEventListener('click', function () {
        sidebar.classList.toggle('hidden');
        if (sidebar.classList.contains('hidden')) {
            mainContent.classList.remove('lg:ml-64');
        } else {
            mainContent.classList.add('lg:ml-64');
        }
    });

    // Theme toggle with Light, Dark, and System options
    const themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
    const themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');
    const themeToggleSystemIcon = document.getElementById('theme-toggle-system-icon');
    const themeOptions = document.querySelectorAll('.theme-option');

    // Function to update theme
    function updateTheme(theme) {
        // Hide all icons first
        themeToggleDarkIcon?.classList.add('hidden');
        themeToggleLightIcon?.classList.add('hidden');
        themeToggleSystemIcon?.classList.add('hidden');

        // Hide all checkmarks
        document.querySelectorAll('.theme-option .checkmark').forEach(checkmark => {
            checkmark.classList.add('hidden');
        });

        if (theme === 'system') {
            // Show system icon in button
            themeToggleSystemIcon?.classList.remove('hidden');

            // Apply system preference
            if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }

            // Show checkmark for system option
            document.querySelector('[data-theme="system"] .checkmark')?.classList.remove('hidden');
        } else if (theme === 'dark') {
            // Show moon icon in button
            themeToggleDarkIcon?.classList.remove('hidden');
            document.documentElement.classList.add('dark');

            // Show checkmark for dark option
            document.querySelector('[data-theme="dark"] .checkmark')?.classList.remove('hidden');
        } else {
            // Show sun icon in button
            themeToggleLightIcon?.classList.remove('hidden');
            document.documentElement.classList.remove('dark');

            // Show checkmark for light option
            document.querySelector('[data-theme="light"] .checkmark')?.classList.remove('hidden');
        }

        // Save to localStorage
        localStorage.setItem('color-theme', theme);
    }

    // Initialize theme on page load
    const savedTheme = localStorage.getItem('color-theme') || 'system';
    updateTheme(savedTheme);

    // Add click handlers to theme options
    themeOptions.forEach(option => {
        option.addEventListener('click', function () {
            const theme = this.getAttribute('data-theme');
            updateTheme(theme);
        });
    });

    // Listen for system theme changes when in system mode
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
        if (localStorage.getItem('color-theme') === 'system') {
            if (e.matches) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        }
    });
});
