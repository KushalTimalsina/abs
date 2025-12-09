<?php

if (!function_exists('userCan')) {
    /**
     * Check if current user has a specific permission in current organization
     */
    function userCan($permission) {
        $user = auth()->user();
        $currentOrgId = session('current_organization_id');
        
        if (!$user || !$currentOrgId) {
            return false;
        }
        
        return $user->hasPermissionInOrganization($currentOrgId, $permission);
    }
}

if (!function_exists('isAdmin')) {
    /**
     * Check if current user is admin in current organization
     */
    function isAdmin() {
        $user = auth()->user();
        $currentOrgId = session('current_organization_id');
        
        if (!$user || !$currentOrgId) {
            return false;
        }
        
        $org = $user->organizations()
            ->wherePivot('organization_id', $currentOrgId)
            ->wherePivot('status', 'active')
            ->first();
            
        return $org && $org->pivot->role === 'admin';
    }
}
