# BlinkPay Laravel

A Laravel package for integrating BlinkPay payment gateway into your Laravel applications.

## Installation

You can install the package via composer: 

```bash
composer require blinkpay/laravel
```

## Configuration

Publish the configuration file: 

```bash
php artisan vendor:publish --provider="BlinkPay\Laravel\BlinkPayServiceProvider"
```

Add the following to your `.env` file:

```env
BLINK_PAYMENTS_USERNAME=your_username
BLINK_PAYMENTS_PASSWORD=your_password
BLINK_PAYMENTS_MERCHANT_ID=your_merchant_id
BLINK_PAYMENTS_MERCHANT_PASSWORD=your_merchant_password
BLINK_PAYMENTS_API_URL=your_api_url
BLINK_PAYMENTS_BANKING_API_URL=your_banking_api_url
BLINK_PAYMENTS_DEFAULT_EXCHANGE_RATE=3700
BLINK_PAYMENTS_EXCHANGE_RATE_KEY=your_exchange_rate_key
BLINK_PAYMENTS_CONVERT_TO_UGX=false
```

## Usage

### Mobile Money Payments

```php
use BlinkPay\Laravel\Facades\BlinkPay;

class PaymentController extends Controller
{    
    public function processMobileMoneyPayment(Request $request)
    {
        try {
            $result = BlinkPay::mobileMoney([
                'order_id' => $request->order_id,
                'amount' => $request->amount,
                'currency' => $request->currency,
                'phone_number' => $request->phone_number
            ]);
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
```

### Credit Card Payments

```php
use BlinkPay\Laravel\Facades\BlinkPay;

class PaymentController extends Controller
{    
    public function processCreditCardPayment(Request $request)
    {
        try {
            $result = BlinkPay::processCreditCardPayment([
                'order_id' => $request->order_id,
                'amount' => $request->amount,
                'currency' => $request->currency,
                'card_number' => $request->card_number,
                'expiry_month' => $request->expiry_month,
                'expiry_year' => $request->expiry_year,
                'cvv' => $request->cvv,
                'card_holder_name' => $request->card_holder_name,
                'billing_address' => $request->billing_address,
                'billing_city' => $request->billing_city,
                'billing_country' => $request->billing_country,
                'billing_postal_code' => $request->billing_postal_code
            ]);
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
```

## Features

### Mobile Money
- Supports various mobile money providers
- Automatic phone number validation and formatting
- Currency conversion to UGX (configurable)
- Minimum amount validation (500 UGX)
- Transaction status checking

### Credit Cards
- Supports major card types (Visa, Mastercard, Amex, Discover)
- Card number validation using Luhn algorithm
- Automatic card type detection
- Billing address support
- Currency conversion to UGX (configurable)

## Currency Conversion

The package supports automatic currency conversion to UGX. This can be enabled by setting `BLINK_PAYMENTS_CONVERT_TO_UGX=true` in your `.env` file.

When enabled:
- All amounts will be converted to UGX using the configured exchange rate
- Minimum amount validation (500 UGX) is automatically applied
- The exchange rate can be configured using `BLINK_PAYMENTS_DEFAULT_EXCHANGE_RATE`
- Custom exchange rates can be implemented using `BLINK_PAYMENTS_EXCHANGE_RATE_KEY`

## Response Format

All payment methods return a response in the following format:

```json
{
    "status": "SUCCESS|FAILED|PENDING",
    "message": {
        "reference_code": "transaction_reference",
        "status": "transaction_status",
        "details": "Additional transaction details"
    }
}
```

## Error Handling

The package throws exceptions for various error conditions:

- Invalid phone number format
- Invalid credit card number
- Minimum amount requirement not met (500 UGX)
- Currency conversion errors
- API communication errors
- Invalid or missing configuration

All exceptions include detailed error messages to help identify the issue.

## Testing

```bash
./vendor/bin/phpunit
```

## Security

If you discover any security related issues, please email security@blink.co.ug instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
```