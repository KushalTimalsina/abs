# Widget Authentication Implementation Plan

## Current Status

-   ✅ UI exists for Login/Register buttons
-   ✅ Auth modal structure in place
-   ❌ No backend API endpoints for auth
-   ❌ No Alpine.js logic for login/register
-   ❌ No token storage/management
-   ❌ Form pre-filling not implemented

## Implementation Steps

### 1. Create API Endpoints

**File**: `routes/web.php`

-   `POST /api/widget/{organization}/auth/register` - Customer registration
-   `POST /api/widget/{organization}/auth/login` - Customer login
-   `POST /api/widget/{organization}/auth/logout` - Customer logout
-   `GET /api/widget/{organization}/auth/user` - Get current user

### 2. Create Auth Controller

**File**: `app/Http/Controllers/WidgetAuthController.php`

-   `register()` - Create customer account
-   `login()` - Authenticate and return token
-   `logout()` - Invalidate token
-   `user()` - Return authenticated user data

### 3. Update Widget Alpine.js

**File**: `resources/views/widget/iframe.blade.php`

Add to data():

```javascript
// Auth
isLoggedIn: false,
user: null,
authToken: null,
showAuthModal: false,
authMode: 'login', // 'login' or 'register'
authForm: {
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: ''
},
authErrors: {},
```

Add methods:

-   `init()` - Check for existing token
-   `login()` - Login user
-   `register()` - Register user
-   `logout()` - Logout user
-   `prefillForm()` - Auto-fill booking form if logged in

### 4. Token Storage

-   Use `localStorage` to store auth token
-   Include token in booking API requests
-   Auto-fill customer details if logged in

### 5. Customer Dashboard Link

-   Link to `/customer/bookings` for logged-in users
-   Show user's name and email

## Benefits

-   ✅ Returning customers don't re-enter details
-   ✅ Customers can track their bookings
-   ✅ Better user experience
-   ✅ Guest checkout still available

## Estimated Time: 1-2 hours
