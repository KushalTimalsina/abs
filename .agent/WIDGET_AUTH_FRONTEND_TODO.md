# Widget Auth - Frontend Implementation

## ✅ Backend Complete

-   ✅ Auth controller created
-   ✅ API routes added:
    -   `POST /api/widget/{org}/auth/register`
    -   `POST /api/widget/{org}/auth/login`
    -   `POST /api/widget/{org}/auth/logout`
    -   `GET /api/widget/{org}/auth/user`

## 🔄 Frontend TODO

### 1. Update Alpine.js Data (line ~484)

Add to `bookingWidget()` return object:

```javascript
// Auth
isLoggedIn: false,
user: null,
authToken: localStorage.getItem('widget_auth_token') || null,
showAuthModal: false,
authMode: 'login',
authForm: {
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: ''
},
authErrors: {},
authLoading: false,
```

### 2. Add init() Method

```javascript
async init() {
    // Check if user is logged in
    if (this.authToken) {
        await this.fetchUser();
    }
},
```

### 3. Add fetchUser() Method

```javascript
async fetchUser() {
    try {
        const response = await fetch(`${baseUrl}/api/widget/${orgSlug}/auth/user`, {
            headers: {
                'Authorization': `Bearer ${this.authToken}`
            }
        });
        const data = await response.json();
        if (data.success) {
            this.isLoggedIn = true;
            this.user = data.user;
            this.prefillForm();
        } else {
            this.logout();
        }
    } catch (error) {
        this.logout();
    }
},
```

### 4. Add login() Method

```javascript
async login() {
    this.authLoading = true;
    this.authErrors = {};
    try {
        const response = await fetch(`${baseUrl}/api/widget/${orgSlug}/auth/login`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                email: this.authForm.email,
                password: this.authForm.password
            })
        });
        const data = await response.json();
        if (data.success) {
            this.authToken = data.token;
            localStorage.setItem('widget_auth_token', data.token);
            this.isLoggedIn = true;
            this.user = data.user;
            this.showAuthModal = false;
            this.prefillForm();
        } else {
            this.authErrors = { general: data.message };
        }
    } catch (error) {
        this.authErrors = { general: 'Login failed' };
    } finally {
        this.authLoading = false;
    }
},
```

### 5. Add register() Method

Similar to login but with more fields

### 6. Add logout() Method

```javascript
logout() {
    localStorage.removeItem('widget_auth_token');
    this.authToken = null;
    this.isLoggedIn = false;
    this.user = null;
},
```

### 7. Add prefillForm() Method

```javascript
prefillForm() {
    if (this.user) {
        this.formData.customer_name = this.user.name;
        this.formData.customer_email = this.user.email;
        this.formData.customer_phone = this.user.phone;
    }
},
```

### 8. Update Auth Modal (already exists, just wire up methods)

-   Login form calls `login()`
-   Register form calls `register()`
-   Logout button calls `logout()`

## Estimated Time: 30-45 minutes

This will complete the widget authentication!
