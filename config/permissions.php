<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Team Member Permissions
    |--------------------------------------------------------------------------
    |
    | Define all available permissions that can be assigned to team members
    |
    */

    'permissions' => [
        'bookings' => [
            'label' => 'Bookings',
            'permissions' => [
                'view_all_bookings' => 'View All Bookings',
                'view_own_bookings' => 'View Own Bookings Only',
                'create_booking' => 'Create New Booking',
                'edit_booking' => 'Edit Booking Details',
                'cancel_booking' => 'Cancel Bookings',
                'confirm_booking' => 'Confirm Bookings',
                'complete_booking' => 'Mark as Completed',
            ],
        ],
        'services' => [
            'label' => 'Services',
            'permissions' => [
                'view_services' => 'View Services',
                'create_service' => 'Create Services',
                'edit_service' => 'Edit Services',
                'delete_service' => 'Delete Services',
            ],
        ],
        'slots' => [
            'label' => 'Slots & Schedule',
            'permissions' => [
                'view_all_slots' => 'View All Slots',
                'view_own_slots' => 'View Own Slots Only',
                'create_slot' => 'Create Slots',
                'edit_slot' => 'Edit Slots',
                'delete_slot' => 'Delete Slots',
            ],
        ],
        'customers' => [
            'label' => 'Customers',
            'permissions' => [
                'view_customers' => 'View Customer List',
                'view_customer_details' => 'View Customer Details',
                'edit_customer' => 'Edit Customer Information',
            ],
        ],
        'team' => [
            'label' => 'Team Management',
            'permissions' => [
                'view_team' => 'View Team Members',
                'invite_team' => 'Invite Team Members',
                'edit_team' => 'Edit Team Members',
                'remove_team' => 'Remove Team Members',
                'manage_permissions' => 'Manage Team Permissions',
            ],
        ],
        'payments' => [
            'label' => 'Payments',
            'permissions' => [
                'view_payments' => 'View Payment History',
                'record_payment' => 'Record Cash Payment',
                'view_payment_gateways' => 'View Payment Gateway Settings',
                'manage_payment_gateways' => 'Manage Payment Gateways',
            ],
        ],
        'invoices' => [
            'label' => 'Invoices',
            'permissions' => [
                'view_invoices' => 'View Invoices',
                'create_invoice' => 'Create Invoices',
                'send_invoice' => 'Send Invoices',
            ],
        ],
        'reports' => [
            'label' => 'Reports & Analytics',
            'permissions' => [
                'view_dashboard' => 'View Dashboard Statistics',
                'view_reports' => 'View Reports',
                'export_data' => 'Export Data',
            ],
        ],
        'settings' => [
            'label' => 'Settings',
            'permissions' => [
                'view_organization_settings' => 'View Organization Settings',
                'edit_organization_settings' => 'Edit Organization Settings',
                'manage_widget' => 'Manage Booking Widget',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Permissions
    |--------------------------------------------------------------------------
    |
    | Default permissions for new team members
    |
    */

    'defaults' => [
        'team_member' => [
            'view_own_bookings',
            'view_own_slots',
            'confirm_booking',
            'complete_booking',
            'view_dashboard',
        ],
        'frontdesk' => [
            'view_all_bookings',
            'create_booking',
            'edit_booking',
            'confirm_booking',
            'complete_booking',
            'view_services',
            'view_customers',
            'view_customer_details',
            'record_payment',
            'view_dashboard',
        ],
    ],
];
