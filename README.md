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
use BlinkPay\Laravel\BlinkPayGateway;

class PaymentController extends Controller
{
    protected $blinkPay;
    
    public function __construct(BlinkPayGateway $blinkPay)
    {
        $this->blinkPay = $blinkPay;
    }
    
    public function processMobileMoneyPayment(Request $request)
    {
        try {
            $result = $this->blinkPay->processPayment([
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
use BlinkPay\Laravel\BlinkPayGateway;

class PaymentController extends Controller
{
    protected $blinkPay;
    
    public function __construct(BlinkPayGateway $blinkPay)
    {
        $this->blinkPay = $blinkPay;
    }
    
    public function processCreditCardPayment(Request $request)
    {
        try {
            $result = $this->blinkPay->processCreditCardPayment([
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

## Supported Payment Methods

### Mobile Money
- Supports various mobile money providers
- Automatic phone number validation
- Currency conversion to UGX (optional)

### Credit Cards
- Supports major card types (Visa, Mastercard, Amex, Discover)
- Card number validation using Luhn algorithm
- Automatic card type detection
- Billing address support
- Currency conversion to UGX (optional)

## Response Format

Both payment methods return a response in the following format:

```json
{
    "status": "SUCCESS|FAILED|PENDING",
    "message": "Response message from the payment gateway"
}
```

## Error Handling

The package throws exceptions for various error conditions:

- Invalid phone number format
- Invalid credit card number
- Unsupported card type
- API communication errors

## Testing

Run the test suite:

```bash
./vendor/bin/phpunit
```

## Security

- All sensitive data is handled securely
- Credit card validation before processing
- Phone number validation and formatting
- Secure API communication

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email security@yourdomain.com instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
