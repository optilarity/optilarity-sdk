# Optilarity PHP SDK

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.0-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

Official PHP SDK for integrating with the **Optilarity** ecosystem. This SDK provides a fluent, high-level interface for License Management, Membership (OAuth2), and Template Catalog services.

---

## 🚀 Installation

Add the SDK to your project via Composer:

```bash
composer require optilarity/optilarity-sdk
```

---

## ⚙️ Quick Start

### 1. Initialization
The SDK uses a **Factory Pattern** for easy instantiation. In a WordPress environment, it automatically uses `wp_remote_request`. In others, it falls back to **cURL**.

```php
use Optilarity\Sdk\OptilaritySdk;

$sdk = OptilaritySdk::make('https://api.optilarity.top');
```

---

## 🔑 License Service
Manage activation and status of product licenses.

### Activate a License
```php
try {
    $result = $sdk->license()->activate('XXXX-XXXX-XXXX', 'user@example.com');
    // $result contains ['success' => true, 'token' => '...', 'licensed_products' => [...]]
} catch (\Optilarity\Sdk\Exceptions\ApiException $e) {
    echo "Activation failed: " . $e->getMessage();
}
```

### Heartbeat (Ping)
Verify if a previously activated license/token is still valid.
```php
$status = $sdk->license()->ping('YOUR_STORED_TOKEN');
```

---

## 👤 Membership Service (OAuth2)
Handles the full OAuth2 flow to connect users to the Optilarity Cloud.

### Step 1: Redirect to Authorization
```php
$authUrl = $sdk->membership()->authorizeUrl(
    'CLIENT_ID',
    'https://your-site.com/callback',
    ['membership:read'] // Scopes
);

header("Location: $authUrl");
exit;
```

### Step 2: Exchange Code for Token
```php
$tokenData = $sdk->membership()->exchangeCode(
    'CLIENT_ID',
    'CLIENT_SECRET',
    $_GET['code'],
    'https://your-site.com/callback'
);
$accessToken = $tokenData['access_token'];
```

### Step 3: Fetch Plan Details
```php
$plan = $sdk->membership()->plan($accessToken);
echo "Current Plan: " . $plan['name'];
```

---

## 📦 Template Service
Browse and download premium resources.

### List Templates
```php
$templates = $sdk->templates()->list('landing-page', 1, 20);
```

### Download Asset
```php
$asset = $sdk->templates()->download($accessToken, 'template-uuid');
// Returns signed download URL (e.g., from Backblaze B2)
```

---

## ⚠️ Error Handling
The SDK provides structured exceptions to catch specific failures:

- `AuthException`: Happens on 401/403 (Invalid token or expired credentials).
- `ApiException`: Generic API errors (400, 404, 500, etc.).

```php
use Optilarity\Sdk\Exceptions\AuthException;
use Optilarity\Sdk\Exceptions\ApiException;

try {
    $sdk->license()->ping($token);
} catch (AuthException $e) {
    // Redirect user to login or clear token
} catch (ApiException $e) {
    // Log the error: $e->getStatusCode() and $e->getResponse()
}
```

---

## 🧪 Development & Testing
If you are contributing to the SDK, run the unit test suite:

```bash
./vendor/bin/phpunit tests
```

---

## 🛠 Design Patterns Used
- **Factory/Singleton**: For flexible instantiation.
- **Fluent Interface**: For readable API service access.
- **Dependency Injection**: Services receive the HTTP client via constructor.
- **Adapter Pattern**: `HttpClient` adapts to WordPress or generic cURL environments.
