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
BLINKPAY_USERNAME=your_username
BLINKPAY_PASSWORD=your_password
BLINKPAY_API_URL=your_api_url
BLINKPAY_DOLLAR_RATE=default_exchange_rate
BLINKPAY_OPENEXCHANGE_KEY=your_openexchange_key
BLINKPAY_CONVERT_TO_UGX=false
```

## Usage

```php
use BlinkPay\Laravel\BlinkPayGateway;

class PaymentController extends Controller
{
    protected $blinkPay;
    
    public function __construct(BlinkPayGateway $blinkPay)
    {
        $this->blinkPay = $blinkPay;
    }
    
    public function processPayment(Request $request)
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

## License

The MIT License (MIT)

Copyright (c) 2024 BlinkPay

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
