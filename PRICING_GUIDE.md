# Subscription Plan Pricing Guide

## Price Display

The subscription plan prices are now stored and displayed correctly:

- **Storage**: Prices are stored as decimal values in NPR (Nepali Rupees)
- **Display**: Prices show the actual amount (e.g., NPR 50.00 for a 50 rupee plan)
- **Duration**: Each plan shows its duration in days (e.g., "/30 days")

## Currency Conversion for Stripe

When using Stripe for international payments, the system automatically converts NPR to USD:

### Exchange Rate
- **Current Rate**: 1 USD = 133 NPR (approximate)
- **Location**: Defined in `SubscriptionPlan` model

### Available Attributes

The `SubscriptionPlan` model now provides these helpful attributes:

1. **`$plan->price`** - Original price in NPR (e.g., 50.00)
2. **`$plan->price_in_usd`** - Converted price in USD (e.g., 0.38)
3. **`$plan->stripe_price`** - Price in cents for Stripe API (e.g., 38)
4. **`$plan->formatted_price`** - Formatted NPR price (e.g., "NPR 50.00")
5. **`$plan->formatted_usd_price`** - Formatted USD price (e.g., "$0.38")

### Usage Examples

```php
// Display NPR price
{{ $plan->formatted_price }}  // Output: NPR 50.00

// Display USD price for international customers
{{ $plan->formatted_usd_price }}  // Output: $0.38

// For Stripe payment processing
$stripe->checkout->sessions->create([
    'line_items' => [[
        'price_data' => [
            'currency' => 'usd',
            'unit_amount' => $plan->stripe_price,  // Amount in cents
            'product_data' => [
                'name' => $plan->name,
            ],
        ],
        'quantity' => 1,
    ]],
]);
```

## Updating Exchange Rate

To update the exchange rate, edit the `SubscriptionPlan` model:

**File**: `app/Models/SubscriptionPlan.php`

```php
public function getPriceInUsdAttribute(): float
{
    $exchangeRate = 133; // Change this value
    return round($this->price / $exchangeRate, 2);
}
```

## Example Plan Pricing

| Plan Name | NPR Price | USD Price | Stripe Amount (cents) |
|-----------|-----------|-----------|----------------------|
| Basic     | NPR 50.00 | $0.38     | 38                   |
| Standard  | NPR 500.00| $3.76     | 376                  |
| Premium   | NPR 1000.00| $7.52    | 752                  |

## Notes

- All prices are rounded to 2 decimal places
- Stripe requires amounts in cents (smallest currency unit)
- The exchange rate should be updated periodically for accuracy
- For local payments (eSewa, Khalti), use the NPR price directly
