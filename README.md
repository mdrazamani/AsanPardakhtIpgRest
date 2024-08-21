# AsanPardakht IPG REST Laravel Package

This Laravel package provides a seamless integration with AsanPardakht's IPG REST API, allowing you to handle online payments, transaction verifications, and settlements effortlessly.

## Features

- **Token Generation:** Easily generate payment tokens.
- **Transaction Verification:** Verify the status of transactions.
- **Settlement Processing:** Manage post-transaction settlements.
- **Seamless Redirection:** Redirect users to the AsanPardakht payment gateway with ease.

## Installation

To get started with this package, follow these simple steps:

### 1. Install via Composer

Run the following command in your Laravel project:

```bash
composer require mdrazamani/asanpardakht-ipg-rest
```

### 2. Publish Configuration

Publish the package configuration file using the following Artisan command:

```bash
php artisan vendor:publish --provider="mdrazamani\AsanPardakhtIpgRest\AsanPardakhtIpgRestServiceProvider"
```

### 3. Update Environment Variables

Add the following variables to your .env file to configure the package:

```env
ASANPARDAKHT_USERNAME=your_username
ASANPARDAKHT_PASSWORD=your_password
ASANPARDAKHT_MERCHANT_CONFIG_ID=your_merchant_config_id
ASANPARDAKHT_CALLBACK_URL=your_callback_url
```

## Usage

### 1. Generate Token and Redirect

Initiate a payment by generating a token and redirecting the user to AsanPardakht's payment gateway:

```php
$gateway = app('asanpardakht')->init($invoiceId, $amount);
$response = $gateway->token();
$gateway->redirect($response['token']);
```

### 2. Verify Transaction

After the user returns to your site, verify the transaction:

```php
$verifyResponse = app('asanpardakht')->verify($transactionId);
```

### 3. Process Settlement

Once the transaction is verified, you can process the settlement:

```php
$settlementResponse = app('asanpardakht')->settlement($transactionId);
```

## Methods Overview

. init($invoiceId, $amount): Initializes the payment with the given invoice ID and amount.
. token(): Generates a payment token.
. verify($transactionId): Verifies the transaction.
. settlement($transactionId): Processes the settlement for the given transaction.
. redirect($token, $mobile = null): Redirects the user to the payment gateway.
