# SaaS Payments PHP SDK

SaaSPayments is the easiest way to integrate payment processing into your software. Bolt NOT a payment processor but rather connects to  major processors in each territory. 

The ideal use case is working with a B2B SaaS platform that offers merchants the ability collect payments into their own merchant account (ie the SaaS platform does not try to get in the way of funds) - for example an ecommerce platform. In this scenario, merchants value the ability to choose their preferred payment processor rather than be prescribed a limited choice like only offering Stripe or Paypal. 

## Introduction

This library is designed to simplify your integration with Bolt for platforms written with PHP. We provide a number of technicques for integration:

* Popovers: We provide a hosted popover setup and payment process, designed to appear "integrated" with your platform. The use of a hosted payment process can shield you from PCI DSS requirements of handling card details. Note for popovers to work, you will need to include a reference to the following file to your webpage:
	
    `<script src="https://payments.withbolt.com/b/web/s/payments-1.0.6.min.js">`

* Redirect URLs: If you wish to use a hosted setup & payment process but prefer not to popover your existing platform, you can redirect instead, these pages will be hosted from Bolt's domain.
* Serverside: These are performed entirely by serverside API and do not have a user interface component. Note: The use of APIs which handle card details require your platform to be PCI DSS compiant, are only available in our white-labelled product. You can still however use the popover and redirect techniques. 

## Setup / Onboarding / Requirements

* PHP 7.0 or greater
* cUrl extension enabled
* Composer (https://getcomposer.org/download/)

### Installation


Use the following Composer command to install the [the Pay With Bolt PHP SDK vendor on Packagist](https://packagist.org/packages/pay-with-bolt/bolt-saas-php)

The recommended way to install Pay With Bolt PHP SDK is through
[Composer](http://getcomposer.org).

```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
```

Next, run the Composer command to install the latest stable version of PWB SDK:

~~~shell
 $ composer require pay-with-bolt/bolt-saas-php
 $ composer update
~~~

After installing, you need to require Composer's autoloader:

```php
require 'vendor/autoload.php';
```

You can also install composer for your specific project by running the following in the library folder.

~~~shell
 $ curl -sS https://getcomposer.org/installer | php
 $ php composer.phar install
 $ composer install
~~~

### Namespace

All the examples below assume the `SaasPayments\SaasPayments` class is imported
into the scope with the following namespace declaration:

~~~php
use SaasPayments\SaasPayments;
use SaasPayments\Setup;
use SaasPayments\Payment;
use SaasPayments\Refund;
~~~

### Hosted Setup

Adding a payments setup button to your software that launches a popup or redirect is easy, you simply need to include our JS library, and then add a "Setup Payments" link or button to your page. The button requires two HTML attributes `data-bolt-setup` and `data-bolt-signature` as such:

```
<button data-bolt-setup="setup_encoded_string" data-bolt-signature="signature_encoded_string">Setup Payments</button>
```   

Where the encoded strings are generated server side via our library;

~~~php
use SaasPayments\Setup;

$settings = [
    "shared_key" => "your shared key",
    "secret_key" => "your secret key"
];

$options = [
    "instance_key"=> "client_key",
    "company_name" => "Jim's Shoe Shop",
    "company_country" => "US",
    "contact_name" => "Jim Doe",
    "contact_phone" => "+1614123456",
    "contact_email" => "jim@email.com"
];

$setup = new Setup($settings);


$setup_encoded_string = $setup->setupButton($options);
$signature_encoded_string = $setup->setupSignature($options);

$html_button = '<button data-bolt-setup="' . $setup_encoded_string . '" data-bolt-signature="' . $signature_encoded_string . '">Setup Payments</button>';

// OR you can alternatively create a setup URL to redirect the user to

$setup_url = $setup->setupUrl($options);
~~~

Where options:

| Field | Description |
|---|---|
| instance_key | (required) A unique key to identify the merchant, this can be any string. |
| company_name | (optional) The name of the merchant |
| company_country | (optional) The country the merchant is from |
| contact_name | (optional) The name of the current user |
| contact_phone | (optional) The phone of the current user |
| contact_email | (optional) The email of the current user |

First time users will be required to supply all the above fields during account creation, any fields not passed in the "options" will be prompted to the user.

### Serverside Payment Readiness

You can check if an instance has been activated and configured for payments, by calling `getSetup`.

~~~php
use SaasPayments\Setup;

$setup = new Setup([
    "shared_key" => "your shared key",
    "secret_key" => "your secret key"
]);

$options = ['instance_key' => 'client_key'];

echo $setup->getSetup($options);
~~~

```
Result: 
{
	active: true,
	id: "1234",
	application: "1023_123",
	instance_key: "ABC1234",
	company_name: "Pay with Bolt",
	contact_name: "Phil Peters",
	contact_email: "phil@paywithbolt.com",
	contact_phone: "+447985918872",
	
	payments_ready: true,
	card_types: ["VISA", "MASTERCARD", "AMEX", "PAYPAL"],
	currencies: ["USD", "GBP"],
	gateways: [
		{
			id: "1234_5678",
			code: "G005678",
			type: "STRIPE",
			account_name: "Pay with Bolt",
			card_types: ["VISA", "MASTERCARD", "AMEX"],
			currencies: ["USD", "GBP"],
			features: ["AUTHORISE", "MULTI_CAPTURE", "SAVE_CARD"]
		},{
			id: "1234_5679",
			code: "G005679",
			type: "PAYPAL",
			account_name: "Pay with Bolt Ltd",
			card_types: ["PAYPAL"],
			currencies: ["USD"],
			features: []
		}
	]
}
```

## Payments and Refunds

### Hosted Payment

Adding a payments popup is also easy, you simply need to include our JS library, and then add a "Payment" link or button to your page. The button requires two attributes `data-bolt-payment` and `data-bolt-signature` as such

```
<button data-bolt-payment="payment_encoded_string" data-bolt-signature="signature_encoded_string">Pay</button>
```   

Where the encoded strings are generated serverside via our library, for example:

~~~php
use SaasPayments\Payment;

$payment = new Payment([
    "shared_key" => "your shared key",
    "secret_key" => "your secret key"
]);

$options = [
    "instance_key" => "1000",

    "currency" => "GBP",
    "amount" => 100, 

    "alt_key" => "1234",
    "description" => "About the payment",
    "source" => "moto",
    
    "account" => [
        "alt_key" => "yourCustomerId",
        "crm_key" => "ID1",
        "first_name" => "James",
        "last_name" => "Boer",
        "company" => "Jinky's Lollie Shop",
        "email" => "james@jinkies.com",
        "phone" => "0712345678",
        "address" => [  
            "line1" => "1 High St",
            "line4" => "London",
            "line5" => "W10 6RU",
            "country" => "GB"
        ]
    ],
    "success_url" => "https://jinkies.com/receipt"
];

$payment_encoded_string = $payment->paymentButton($options);
$signature_encoded_string = $payment->paymentSignature($options);

// OR you can alternatively create a payment URL to redirect the user to

$payment_url = $payment->paymentUrl($options);
~~~

Where options:

| Field | Description |
|---|---|
| `instance_key` | (required) A unique key to identify the merchant, this can be any string. |
| `currency` | The currency in in ISO format eg USD |
| `amount` | The amount of the payment as a string eg "10", the amount should not have commas and use a `.` as a decimal seperator |
| `default_amount` | The default amount of the payment as a string eg "10", the amount should not have commas and use a `.` as a decimal seperator. A default amount is be editable by the user. |
| `alt_key` | Your unique reference for the payment (eg order id) |
| `description` | Your descriptive text for the payment |
| `nonce` | A unique string that will be used by Bolt to ensure the same transactions is not submitted twice. A nonce can only be used once in a 24 hours period.  |
| `source` | can be any of "moto", "ecommerce" - default "ecommerce" |
| `account` | can be an `id` referencing an existing account or a fully formed account object which will be created or amended |
| `success_url` | an optional URL to send the browser upon successful payment, if you do not pass this be sure to catch the Javascript message |

Where `account`:

| Field | Description |
|---|---|
| `id` | The Bolt id of the account if referencing an existing account and you know the id |
| `alt_key` | Your reference for this person  |
| `first_name` | The person's first name / giving name |
| `last_name` | The person's last name / surname |
| `company` | The person's company |
| `email` | The person's email (must be a valid email if passed) |
| `phone` | The person's phone (must be a valid phone if passed) |
| `address` | The residential address for the card as 6 lines and country |


Where `address`:

| Field | Description |
|---|---|
| `line1` | the first line of the address  |
| `line2` | the second line of the address  |
| `line3` | the third line of the address  |
| `line4` | the city / town  |
| `line5` | the zip / postcode  |
| `line6` | the state /territory  |
| `country` | the country (2 digit ISO format) eg UK, GB |


### Serverside Payment

You can perform a payment request server side via `$payment->doPayment($options)`. A new payment will be attempted,  unless the payment is rejected prior to processing the API request will succeed and return a payment object in a SUCCESS or DECLINE status. If however the payment is rejected due to a validation rule (eg no card data), no payment will be created. For example:

~~~php
use SaasPayments\Payment;

$payment = new Payment([
    "shared_key" => "your shared key",
    "secret_key" => "your secret key"
]);

$options = [
    "instance_key" => "ABC123",

    "currency" => "GBP",
    "amount" => 100, 

    "alt_key" => "1234",
    "description" => "About the payment",
    "source" => "moto",
    
    "account" => [
        "alt_key" => "yourCustomerId",
        "first_name" => "James",
        "last_name" => "Boer",
        "company" => "Jinky's Lollie Shop",
        "email" => "james@jinkies.com",
        "phone" => "0712345678",
        "address" => [  
            "line1" => "1 High St",
            "line4" => "London",
            "line5" => "W10 6RU",
            "country" => "GB"
        ]
    ],
    "payment_method" => [
        'card_number' => "4444333322221111",
        'card_cvc' => "123",
        'card_expiry' => "1222",
        'save_card' => true
    ]
];

$payment->doPayment($options);
~~~

```
Result: 
{
    "payment": {
        "id": "pay_1000_31792",
        "alt_key": "1234",
        "description": "About the payment",
        "source": "moto",
        "action": "PAYMENT",
        "status": "SUCCESS",
        "gateway_status": "APPROVED",
        "reference": "BOLT-00031792",
        "amount": "100",
        "balance": "100",
        "currency": "GBP",
        "initiative": "in_1000_1661",
        "gateway": "gw_1000_1200",
        "account": "acc_1000_9292",
        "payment_method": "pm_1000_22961",
        "created": "2019-01-23T10:24:31.000Z",
        "processed": "2019-01-23T10:24:31.000Z"
    }
}
```

Where options:

| Field | Description |
|---|---|
| `instance_key` | (required) A unique key to identify the merchant, this can be any string. |
| `currency` | The currency in in ISO format eg USD |
| `amount` | The amount of the payment as a string eg "10", the amount should not have commas and use a `.` as a decimal seperator |
| `alt_key` | Your unique reference for the payment (eg order id) |
| `description` | Your descriptive text for the payment |
| `nonce` | A unique string that will be used by Bolt to ensure the same transactions is not submitted twice. A nonce can only be used once in a 24 hours period.  |
| `source` | can be any of "moto", "pos", "ecommerce" - default "ecommerce" |
| `payment_method` | can be an `id` referencing an existing payment_method or a fully formed payment_method object which will be created or amended. If an existing payment_method is to be used with a cvc, pass an object containing an `id` and `card_cvc` |
| `account` | can be an `id` referencing an existing account or a fully formed account object which will be created or amended |

Where `payment_method`:

| Field | Description |
|---|---|
| `id` | The id of the payment method, if referencing an existing payment method. |
| `card_number` | The full card PAN (typically 16 digits) without formatting |
| `card_cvc` | the last 3-4 characters of the cards security text |
| `card_expiry` | The expiry of the card in YYYY-MM format eg 2020-12 |
| `save_card` | A boolean `true` if you wish to save the card for use in the future |
| `description` | When saving the card how you would like the card to be labelled, the card will be assigned an automatic label if ommited |
| `billing` | The billing address for the card as 6 lines and country |

Where `account`:

| Field | Description |
|---|---|
| `id` | The id of the account if referencing an existing account |
| `alt_key` | Your reference for this person  |
| `first_name` | The person's first name / giving name |
| `last_name` | The person's last name / surname |
| `company` | The person's company |
| `email` | The person's email (must be a valid email if passed) |
| `phone` | The person's phone (must be a valid phone if passed) |
| `address` | The residential address for the card as 6 lines and country |


Where `billing` and `address`:

| Field | Description |
|---|---|
| `line1` | the first line of the address  |
| `line2` | the second line of the address  |
| `line3` | the third line of the address  |
| `line4` | the city / town  |
| `line5` | the zip / postcode  |
| `line6` | the state /territory  |
| `country` | the country (2 digit ISO format) eg UK, GB |


### Serverside Refund

You can refund part of all of a payment, via `$refund->doRefund($options)`, for example: 

~~~php
use SaasPayments\Refund;

$refund = new Refund([
    "shared_key" => "your shared key",
    "secret_key" => "your secret key"
]);

$options = [
	"instance_key" => "client_key",
	"payment" => "pay_1234_5678",
	"amount" => "100", // Optional - default to original transaction amount 
	"reason" => "Optional description why"
];

$refund->doRefund($options);
~~~    
```
Result: 
{
    "refund": {
        "id": "ref_1000_31792",
        "payment": "pay_1000_1234",
        "amount": "100",
        "reason": "Optional description why"
        "status": "SUCCESS",
        "gateway_status": "APPROVED",
        "reference": "BOLT-00031792",
        "created": "2019-01-23T10:24:31.000Z",
        "processed": "2019-01-23T10:24:31.000Z"
	}
}
```

Where options:

| Field | Description |
|---|---|
| instance_key | (required) A unique key to identify the merchant, this can be any string. |
| payment | (required) Pass in the payment key, or your alt_key. |
| amount | (optional) The amount to refund (up to the value of the original transaction), if not passed the entire transaction will be refunded. |
| reason | (required) The reason for the refund, if not passed 'no reason' will be used. |


### Handling Webhooks

Webhooks are sent to your server for various events, such as payments and refunds. When you receive a webhook, you can expand it via `$saasPayments->getWebhook($options)`, where:

~~~php
use SaasPayments\SaasPayments;

$saasPayments = new SaasPayments([
    "shared_key" => "your shared key",
    "secret_key" => "your secret key"
]);

$paymentWebhookOptions = [
    "action" => "PAYMENT.SUCCESS",
    "application" => "TEST",
    "payment" => null,
    "instance_key" => null,
];

$saasPayments->getWebhook($paymentWebhookOptions)

$refundWebhookOptions = [
    "action" => "REFUND.SUCCESS",
    "application" => "TEST",
    "instance_key" => null,
    'transaction' => null,
];

$saasPayments->getWebhook($refundWebhookOptions)

~~~
```
Result: 
{ 
    "application": "1084_2872", 
    "instance_key": "123456789",
    "key": "3174_1550246984417_274535e3ba4ed695", 
    "client": "3174", 
    "action": "PAYMENT.SUCCESS", 
    "payment": {
        "id": "pay_1000_31792",
        "alt_key": "1234",
        "description": "About the payment",
        "source": "moto",
        "action": "PAYMENT",
        "status": "SUCCESS",
        "gateway_status": "APPROVED",
        "reference": "BOLT-00031792",
        "amount": "100",
        "balance": "100",
        "currency": "GBP",
        "initiative": "in_1000_1661",
        "gateway": "gw_1000_1200",
        "account": "acc_1000_9292",
        "payment_method": "pm_1000_22961",
        "created": "2019-01-23T10:24:31.000Z",
        "processed": "2019-01-23T10:24:31.000Z"
    }, 
    "transaction": "3174_29387", 
    "contract": "3174_29387", 
    "created": "2018-01-01T23:59:59Z"
}
```
