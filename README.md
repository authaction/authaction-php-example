# authaction-php-example

A plain PHP application demonstrating API authorization using [AuthAction](https://app.authaction.com/) with the `authaction/authaction-php-sdk`. No framework required — runs with the PHP built-in server.

## Overview

This application shows how to configure and handle authorization using AuthAction's access tokens in a plain PHP API. It validates JSON Web Tokens (JWT) using the `authaction` SDK, which handles JWKS fetching and RS256 validation automatically.

## Prerequisites

- **PHP 8.2+** and **Composer**
- **AuthAction credentials**: `tenantDomain` and `apiIdentifier` from your AuthAction account.

## Installation

1. **Clone the repository**:

   ```bash
   git clone git@github.com:authaction/authaction-php-example.git
   cd authaction-php-example
   ```

2. **Install dependencies**:

   ```bash
   composer install
   ```

3. **Configure your AuthAction credentials**:

   ```bash
   cp .env.example .env
   ```

   Edit `.env` and replace the placeholders:

   ```env
   AUTHACTION_DOMAIN=your-authaction-tenant-domain
   AUTHACTION_AUDIENCE=your-authaction-api-identifier
   ```

## Usage

1. **Start the development server**:

   ```bash
   php -S localhost:8000 -t public
   ```

   The API will be available at `http://localhost:8000`.

2. **Obtain an access token** via client credentials:

   ```bash
   curl --request POST \
     --url https://your-authaction-tenant-domain/oauth2/m2m/token \
     --header 'content-type: application/json' \
     --data '{
       "client_id": "your-authaction-app-clientid",
       "client_secret": "your-authaction-app-client-secret",
       "audience": "your-authaction-api-identifier",
       "grant_type": "client_credentials"
     }'
   ```

3. **Call the public endpoint** (no token required):

   ```bash
   curl http://localhost:8000/public
   ```

   ```json
   { "message": "This is a public message!" }
   ```

4. **Call the protected endpoint** with the access token:

   ```bash
   curl --request GET \
     --url http://localhost:8000/protected \
     --header 'Authorization: Bearer YOUR_ACCESS_TOKEN'
   ```

   ```json
   { "message": "This is a protected message!", "sub": "client-id@clients" }
   ```

## Project Structure

```
authaction-php-example/
├── public/
│   └── index.php        # Entry point: simple router + route handlers
├── src/
│   └── JwtValidator.php # Thin wrapper around AuthAction\AuthAction SDK client
├── .env.example
├── composer.json
└── README.md
```

## Code Explanation

### `src/JwtValidator.php` — JWT Validation

Wraps a singleton `AuthAction\AuthAction` client (from `authaction/authaction-php-sdk`). The SDK is initialised with `AUTHACTION_DOMAIN` and `AUTHACTION_AUDIENCE` and handles JWKS fetching and RS256 JWT validation internally.

- **`verify(string $token)`** — Calls `AuthAction::verifyToken($token)`. Throws a `RuntimeException` on `TokenExpiredException` or `TokenInvalidException`.

### `public/index.php` — Router

- **`GET /public`** — Accessible without authentication.
- **`GET /protected`** — Extracts the `Bearer` token from the `Authorization`
  header, calls `JwtValidator::verify()`, and returns the decoded `sub`.
  Returns 401 on any validation failure.

## Common Issues

**Invalid token errors** — Verify that `AUTHACTION_DOMAIN` and
`AUTHACTION_AUDIENCE` match the values in your AuthAction dashboard exactly.

**Public key fetching errors** — Check that your application can reach
`https://{AUTHACTION_DOMAIN}/.well-known/jwks.json`. You may need to enable
`allow_url_fopen` in `php.ini`.

**Unauthorized access** — Ensure the `Authorization: Bearer <token>` header is
present and the token was issued for the correct audience.

## Contributing

Feel free to submit issues or pull requests if you encounter bugs or have suggestions for improvement!
