# Google OAuth for Multi-Client Widget - ONE-TIME SETUP

## ✅ **GOOD NEWS: You Only Need to Configure Google OAuth ONCE!**

---

## **How It Works:**

### **Current Setup (Correct):**

```
1. Client embeds widget on their domain (e.g., subash.com)
2. User clicks "Login with Google" in widget
3. Redirects to: https://final.nmgdevelopment.xyz/widget/{org}/auth/google
4. Google authenticates user
5. Google redirects back to: https://final.nmgdevelopment.xyz/widget/auth/google/callback
6. Your server logs in the user
7. Redirects back to: https://final.nmgdevelopment.xyz/widget/{org-slug}
8. Widget shows logged-in user
```

### **Key Point:**

✅ **ALL redirects go through YOUR domain** (`final.nmgdevelopment.xyz`)
✅ **Client domains are NOT involved in OAuth flow**
✅ **No need to add client domains to Google Console**

---

## **Google Cloud Console Setup (ONE TIME ONLY):**

### **Required Redirect URI:**

```
https://final.nmgdevelopment.xyz/widget/auth/google/callback
```

### **Required JavaScript Origin:**

```
https://final.nmgdevelopment.xyz
```

**That's it!** This works for ALL organizations, regardless of where they embed the widget.

---

## **Why You're Getting 403 Error:**

The 403 error is likely because:

### **Option 1: Missing Redirect URI**

You haven't added the callback URL to Google Console yet.

**Fix:** Add `https://final.nmgdevelopment.xyz/widget/auth/google/callback` to Google Console

### **Option 2: HTTP vs HTTPS**

You're using `http://` instead of `https://`

**Fix:** Ensure your site uses HTTPS and the redirect URI in Google Console matches exactly

### **Option 3: Localhost Testing**

You're testing on localhost but Google Console has production URL

**Fix:** Add localhost callback for testing:

```
http://localhost:8000/widget/auth/google/callback
```

---

## **Complete Google Console Setup:**

### **Step 1: Go to Google Cloud Console**

https://console.cloud.google.com/

### **Step 2: APIs & Services → Credentials**

### **Step 3: Edit OAuth 2.0 Client ID**

### **Step 4: Add Authorized Redirect URIs:**

**For Production:**

```
https://final.nmgdevelopment.xyz/widget/auth/google/callback
```

**For Local Testing (optional):**

```
http://localhost:8000/widget/auth/google/callback
```

### **Step 5: Add Authorized JavaScript Origins:**

**For Production:**

```
https://final.nmgdevelopment.xyz
```

**For Local Testing (optional):**

```
http://localhost:8000
```

### **Step 6: Save**

---

## **Testing:**

### **Test 1: Direct Widget Access**

Visit: `https://final.nmgdevelopment.xyz/widget/your-org-slug`

-   Click "Login with Google"
-   Should work ✅

### **Test 2: Embedded Widget**

Embed widget on `subash.com`:

```html
<iframe
    src="https://final.nmgdevelopment.xyz/widget/your-org-slug"
    ...
></iframe>
```

-   Click "Login with Google"
-   Should work ✅

### **Test 3: Another Client Domain**

Embed widget on `hsrt.nmgdevelopment.xyz`:

```html
<iframe
    src="https://final.nmgdevelopment.xyz/widget/your-org-slug"
    ...
></iframe>
```

-   Click "Login with Google"
-   Should work ✅

---

## **Common Mistakes:**

### ❌ **WRONG: Adding Client Domains**

```
https://subash.com/widget/auth/google/callback  ← DON'T ADD THIS
https://hsrt.nmgdevelopment.xyz/widget/auth/google/callback  ← DON'T ADD THIS
```

### ✅ **CORRECT: Only Your Domain**

```
https://final.nmgdevelopment.xyz/widget/auth/google/callback  ← ONLY THIS
```

---

## **Why This Works:**

1. **Widget iframe** loads from YOUR domain (`final.nmgdevelopment.xyz`)
2. **OAuth flow** happens on YOUR domain
3. **Callback** returns to YOUR domain
4. **Session** is created on YOUR domain
5. **Widget** (still on YOUR domain) shows logged-in state

**Client domain is just the container!** The widget itself always runs on YOUR domain.

---

## **For New Organizations:**

### **Do you need to update Google Console?**

**NO!** ❌

### **What happens automatically?**

1. New organization subscribes
2. They get a unique slug (e.g., `new-org`)
3. Widget URL: `https://final.nmgdevelopment.xyz/widget/new-org`
4. They embed it on their site
5. Google OAuth works automatically ✅

**The redirect URI is the same for all organizations!**

---

## **Summary:**

✅ **Configure Google OAuth ONCE**
✅ **Works for ALL organizations**
✅ **Works on ANY client domain**
✅ **No per-organization setup needed**

**Just make sure you have HTTPS enabled on `final.nmgdevelopment.xyz`!**

---

## **If Still Getting 403:**

1. **Check Google Console** - Is the redirect URI added?
2. **Check HTTPS** - Is your site using HTTPS?
3. **Check .env** - Is `GOOGLE_CLIENT_ID` and `GOOGLE_CLIENT_SECRET` correct?
4. **Wait 5-10 minutes** - Google needs time to propagate changes
5. **Clear browser cache** - Old OAuth data might be cached

---

## **Your Current Setup Should Be:**

**Google Console - Authorized Redirect URIs:**

```
https://final.nmgdevelopment.xyz/widget/auth/google/callback
```

**Google Console - Authorized JavaScript Origins:**

```
https://final.nmgdevelopment.xyz
```

**That's ALL you need!** 🎉
