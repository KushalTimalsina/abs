# Organization Slug-Based URLs

## Overview

Organization URLs now use **slugs** instead of numeric IDs for better security and SEO.

## URL Changes

### Before (Numeric IDs)
```
❌ http://localhost:8000/organization/4/services
❌ http://localhost:8000/organization/4/team
❌ http://localhost:8000/organization/4/settings
```

### After (Slugs)
```
✅ http://localhost:8000/organization/janes-salon/services
✅ http://localhost:8000/organization/janes-salon/team
✅ http://localhost:8000/organization/janes-salon/settings
```

## Benefits

### 1. Security
- **IDs Hidden**: Numeric IDs are no longer exposed in URLs
- **Harder to Enumerate**: Can't easily guess other organization URLs
- **No Sequential Access**: Attackers can't increment IDs to access other orgs

### 2. SEO Friendly
- **Readable URLs**: "janes-salon" is more meaningful than "4"
- **Better for Search Engines**: Descriptive URLs rank better
- **Shareable**: Easier to remember and share

### 3. User Experience
- **Professional**: Looks more polished
- **Brandable**: Organization name in URL
- **Memorable**: Easier to type and remember

## How It Works

### Slug Generation

Slugs are automatically generated from organization names:

```php
// Organization: "Jane's Salon & Spa"
// Slug: "janes-salon-spa"

// Organization: "Tech Solutions Inc."
// Slug: "tech-solutions-inc"
```

**Rules:**
- Lowercase
- Spaces → hyphens
- Special characters removed
- Unique (enforced by database)

### Route Model Binding

The `Organization` model uses slug-based routing:

```php
// In Organization.php
public function getRouteKeyName()
{
    return 'slug';
}
```

This tells Laravel to use the `slug` column instead of `id` for route binding.

### Automatic Slug Creation

When creating an organization:

```php
$organization = Organization::create([
    'name' => "Jane's Salon",
    // slug is auto-generated: "janes-salon"
]);
```

The `boot()` method automatically generates the slug if not provided.

## Implementation Details

### Model Changes

**File**: `app/Models/Organization.php`

```php
protected static function boot()
{
    parent::boot();
    
    static::creating(function ($organization) {
        if (empty($organization->slug)) {
            $organization->slug = Str::slug($organization->name);
        }
    });
}

public function getRouteKeyName()
{
    return 'slug';
}
```

### Database

The `organizations` table already has:
- `slug` column (string, unique)
- Index on slug for fast lookups

### Routes

No changes needed! Routes automatically use slugs:

```php
// This route now expects slug instead of ID
Route::get('/organization/{organization}/services', ...)
```

## Updating Existing Organizations

If you have existing organizations without slugs, run:

```bash
php artisan tinker
```

Then:

```php
App\Models\Organization::whereNull('slug')
    ->orWhere('slug', '')
    ->get()
    ->each(function($org) {
        $org->slug = Str::slug($org->name);
        $org->save();
    });
```

## Handling Duplicate Names

If two organizations have the same name:

```php
// Organization 1: "Tech Solutions"
// Slug: "tech-solutions"

// Organization 2: "Tech Solutions"
// Slug: "tech-solutions-2" (auto-incremented)
```

The system should handle this automatically, but you may want to add custom logic:

```php
protected static function boot()
{
    parent::boot();
    
    static::creating(function ($organization) {
        if (empty($organization->slug)) {
            $slug = Str::slug($organization->name);
            $count = 1;
            
            while (Organization::where('slug', $slug)->exists()) {
                $slug = Str::slug($organization->name) . '-' . $count;
                $count++;
            }
            
            $organization->slug = $slug;
        }
    });
}
```

## Link Generation

### In Blade Templates

```blade
{{-- Old way (still works but uses slug) --}}
<a href="{{ route('organization.services.index', $organization) }}">
    Services
</a>

{{-- Explicit slug --}}
<a href="{{ route('organization.services.index', $organization->slug) }}">
    Services
</a>

{{-- URL helper --}}
<a href="{{ url('/organization/' . $organization->slug . '/services') }}">
    Services
</a>
```

### In Controllers

```php
// Redirect using slug
return redirect()->route('organization.services.index', $organization);

// Or explicitly
return redirect()->route('organization.services.index', $organization->slug);
```

## Testing

### Test URL Access

1. **Get your organization slug:**
   ```bash
   php artisan tinker
   ```
   ```php
   $org = App\Models\Organization::first();
   echo $org->slug; // e.g., "janes-salon"
   ```

2. **Access via slug:**
   ```
   http://localhost:8000/organization/janes-salon/services
   ```

3. **Try numeric ID (should fail):**
   ```
   http://localhost:8000/organization/4/services
   ❌ 404 Not Found
   ```

### Test Slug Generation

```php
$org = Organization::create([
    'name' => "Test Salon & Spa",
    'email' => 'test@example.com',
]);

echo $org->slug; // "test-salon-spa"
```

## Sidebar/Navigation Updates

If your sidebar shows organization links, they'll automatically use slugs:

```blade
{{-- In sidebar.blade.php --}}
<a href="{{ route('organization.services.index', $currentOrganization) }}">
    Services
</a>
```

No changes needed - Laravel handles it automatically!

## API Considerations

If you have an API, you may want to keep numeric IDs for API endpoints:

```php
// Web routes (use slug)
Route::get('/organization/{organization}/services', ...)

// API routes (use ID)
Route::get('/api/organizations/{organization:id}/services', ...)
```

The `{organization:id}` syntax forces ID-based binding for API routes.

## Security Notes

### What This Fixes
- ✅ Prevents ID enumeration attacks
- ✅ Hides database structure
- ✅ Makes URLs less predictable

### What This Doesn't Fix
- ❌ Authorization still required (check user has access)
- ❌ Slugs can still be guessed (common names)
- ❌ Need proper middleware for access control

**Always verify authorization:**

```php
public function index(Organization $organization)
{
    // Check if user has access
    $this->authorize('view', $organization);
    
    // ... rest of code
}
```

## Troubleshooting

### 404 Errors After Implementation

**Problem**: Old numeric URLs no longer work

**Solution**: 
1. Clear route cache: `php artisan route:clear`
2. Clear config cache: `php artisan config:clear`
3. Ensure all organizations have slugs

### Duplicate Slug Errors

**Problem**: Two organizations with same name

**Solution**: Implement auto-incrementing slugs (see "Handling Duplicate Names" above)

### Links Still Using IDs

**Problem**: Some links still show numeric IDs

**Solution**: 
1. Check if you're manually building URLs
2. Use `route()` helper instead of hardcoded URLs
3. Let Laravel handle route generation

## Migration Guide

If you're updating an existing application:

1. **Backup database**
2. **Ensure slug column exists** (it does in your case)
3. **Generate slugs for existing orgs** (see "Updating Existing Organizations")
4. **Add `getRouteKeyName()` to model**
5. **Clear caches**
6. **Test all organization routes**
7. **Update any hardcoded URLs**

## Performance

**Database Indexes:**
The `slug` column should be indexed (it is in your migration):

```php
$table->string('slug')->unique();
```

This ensures fast lookups by slug.

**Query Performance:**
```sql
-- Fast (uses index)
SELECT * FROM organizations WHERE slug = 'janes-salon';

-- Also fast (primary key)
SELECT * FROM organizations WHERE id = 4;
```

Both are equally performant!

## Future Enhancements

Consider adding:
- [ ] Slug history (track slug changes)
- [ ] Custom slug editing (let users choose)
- [ ] Slug validation (prevent reserved words)
- [ ] Redirect old IDs to slugs (for backwards compatibility)
