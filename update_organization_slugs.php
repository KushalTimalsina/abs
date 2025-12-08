<?php

use App\Models\Organization;
use Illuminate\Support\Str;

// Update organizations without slugs
$orgs = Organization::whereNull('slug')->orWhere('slug', '')->get();

foreach ($orgs as $org) {
    $slug = Str::slug($org->name);
    $count = 1;
    
    // Ensure unique slug
    while (Organization::where('slug', $slug)->where('id', '!=', $org->id)->exists()) {
        $slug = Str::slug($org->name) . '-' . $count;
        $count++;
    }
    
    $org->slug = $slug;
    $org->save();
    
    echo "Updated: {$org->name} -> {$org->slug}\n";
}

echo "\nDone! All organizations now have slugs.\n";
