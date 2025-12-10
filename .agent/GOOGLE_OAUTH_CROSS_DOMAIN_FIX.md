# Google OAuth 403 Error - Cross-Domain Widget Fix

## Problem

-   Main site: `final.nmgdevelopment.xyz`
-   Widget embedded on: `subash.com` and `hsrt.nmgdevelopment.xyz`
-   Error: **403 - No Access** when trying to login with Google

## Root Cause

Google OAuth requires all redirect URIs to be explicitly whitelisted in Google Cloud Console.

---

## Solution: Update Google Cloud Console

### Step 1: Go to Google Cloud Console

1. Visit: https://console.cloud.google.com/
2. Select your project
3. Go to **APIs & Services** → **Credentials**

### Step 2: Find Your OAuth 2.0 Client ID

1. Click on your OAuth 2.0 Client ID (the one you're using)
2. Scroll to **Authorized redirect URIs**

### Step 3: Add All Redirect URIs

Add these URIs to the **Authorized redirect URIs** list:

```
https://final.nmgdevelopment.xyz/widget/auth/google/callback
https://subash.com/widget/auth/google/callback
https://hsrt.nmgdevelopment.xyz/widget/auth/google/callback
```

**Important:**

-   Use `https://` (not `http://`)
-   No trailing slash
-   Exact match required

### Step 4: Add Authorized JavaScript Origins

Also add these to **Authorized JavaScript origins**:

```
https://final.nmgdevelopment.xyz
https://subash.com
https://hsrt.nmgdevelopment.xyz
```

### Step 5: Save Changes

Click **Save** at the bottom

---

## Alternative: Dynamic Redirect URI (Advanced)

If you have many client domains, you can use a single redirect URI on your main domain:

### Update Widget Auth Controller

**File:** `app/Http/Controllers/WidgetAuthController.php`

```php
public function redirectToGoogle(Organization $organization)
{
    // Store the referrer domain in session
    session(['oauth_referrer' => request()->headers->get('referer')]);

    return Socialite::driver('google')
        ->with(['state' => base64_encode(json_encode([
            'organization_slug' => $organization->slug,
            'referrer' => request()->headers->get('referer')
        ]))])
        ->redirect();
}

public function handleGoogleCallback(Request $request)
{
    try {
        $state = json_decode(base64_decode($request->state), true);
        $organizationSlug = $state['organization_slug'] ?? null;
        $referrer = $state['referrer'] ?? session('oauth_referrer');

        // ... rest of authentication logic ...

        // Redirect back to widget on client's domain
        if ($referrer) {
            return redirect($referrer . '?auth=success');
        }

        return redirect()->route('widget.show', $organizationSlug);
    } catch (\Exception $e) {
        // Handle error
    }
}
```

---

## Quick Fix: Use Popup Authentication

Instead of redirect, use popup window:

**Update Widget:** `resources/views/widget/iframe.blade.php`

```html
<a href="#" onclick="openGoogleAuth(event)" class="..."> Login with Google </a>

<script>
    function openGoogleAuth(e) {
        e.preventDefault();
        const width = 500;
        const height = 600;
        const left = (screen.width - width) / 2;
        const top = (screen.height - height) / 2;

        const popup = window.open(
            '{{ route("widget.auth.google", $organization->slug) }}',
            "Google Login",
            `width=${width},height=${height},left=${left},top=${top}`
        );

        // Listen for auth completion
        window.addEventListener("message", function (event) {
            if (event.data.type === "auth-success") {
                window.location.reload();
            }
        });
    }
</script>
```

---

## Testing

### Test on Each Domain:

1. **Main Site:** https://final.nmgdevelopment.xyz/widget/your-org-slug
2. **Client Site 1:** https://subash.com (with embedded widget)
3. **Client Site 2:** https://hsrt.nmgdevelopment.xyz (with embedded widget)

### Expected Behavior:

✅ Click "Login with Google"
✅ Google login popup/redirect
✅ Successful authentication
✅ Redirect back to widget
✅ User name displayed

---

## Common Issues

### Issue 1: Still Getting 403

**Solution:** Clear browser cache and cookies, wait 5-10 minutes for Google to propagate changes

### Issue 2: Redirect URI Mismatch

**Solution:** Check the exact URI in the error message and add it to Google Console

### Issue 3: CORS Errors

**Solution:** Add CORS headers in `config/cors.php`:

```php
'paths' => ['api/*', 'widget/*', 'sanctum/csrf-cookie'],
'allowed_origins' => [
    'https://final.nmgdevelopment.xyz',
    'https://subash.com',
    'https://hsrt.nmgdevelopment.xyz',
],
'supports_credentials' => true,
```

### Issue 4: Session Not Persisting

**Solution:** Update `.env`:

```env
SESSION_DOMAIN=.nmgdevelopment.xyz
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=none
```

---

## Recommended Approach

**For Production:** Use the Google Cloud Console method (Step 1-5)

**Pros:**

-   ✅ Simple and reliable
-   ✅ No code changes needed
-   ✅ Works immediately

**Cons:**

-   ❌ Need to add each client domain manually
-   ❌ Requires Google Console access

---

## Security Considerations

1. **Only add trusted domains** to authorized URIs
2. **Use HTTPS** for all domains
3. **Validate organization** in callback
4. **Check referrer** to prevent abuse

---

## Quick Checklist

-   [ ] Add redirect URIs to Google Console
-   [ ] Add JavaScript origins to Google Console
-   [ ] Save changes in Google Console
-   [ ] Wait 5-10 minutes
-   [ ] Clear browser cache
-   [ ] Test on each domain
-   [ ] Verify user can login
-   [ ] Check session persists

---

## Need Help?

If still not working, check:

1. Browser console for errors
2. Laravel logs: `storage/logs/laravel.log`
3. Network tab for failed requests
4. Google Console error messages

**The most common fix is adding the redirect URIs to Google Cloud Console!**
