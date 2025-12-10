# Widget Google Login Implementation Plan

## Current Situation

-   ✅ Google OAuth already working for main app
-   ✅ Guest booking already working in widget
-   ❌ No Google login option in widget

## What We Need

1. Add "Login with Google" button to widget
2. Handle Google OAuth callback for widget context
3. Return to widget after login with user session
4. Pre-fill booking form with user details
5. Keep guest booking working

## Implementation Steps

### 1. Add Widget-Specific Google Routes

```php
// Widget Google OAuth
Route::get('/widget/{organization}/auth/google', [WidgetAuthController::class, 'redirectToGoogle']);
Route::get('/widget/{organization}/auth/google/callback', [WidgetAuthController::class, 'handleGoogleCallback']);
```

### 2. Update WidgetAuthController

Add methods:

-   `redirectToGoogle()` - Redirect to Google with widget context
-   `handleGoogleCallback()` - Handle callback, create/login user, return to widget

### 3. Update Widget UI

-   Replace custom login/register with "Login with Google" button
-   Keep guest booking form
-   Show logged-in user info if authenticated

### 4. Session Handling

-   Use regular Laravel session (not API tokens)
-   Check `Auth::check()` in widget
-   Pre-fill form if logged in

## Benefits

✅ Uses existing Google OAuth
✅ No duplicate auth system
✅ Seamless integration
✅ Guest booking still works
