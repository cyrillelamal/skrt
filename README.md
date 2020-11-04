# Skrt
Messenger

## Installation
- Install php dependencies.
```
composer install
```
- Install nodejs dependencies.
```
npm install
```
- Build assets.
```
npm run prod
```
- Generate JWT keys.
```
mkdir -p config/jwt
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
```
- Set configs in `.env`. Especially:
1. `APP_SECURE` - either to use HTTPS;
2. `APP_SECRET`;
3. `CORS_ALLOW_ORIGIN` - the domain of the symfony application;
4. `MERCURE_SECRET_KEY`.
5. `JWT_PASSPHRASE` - the passphrase used while generation of JWT-keys.

Also consider all the other configs, such as `DATABASE_URL`, `MERCURE_PUBLISH_URL`, etc.

## Important security considerations
### CSRF
Technically the application is a single page application. 
All core requests point to `^/api` routes.

Here the `CsrfSubscriber` intercepts.
The subscriber verifies that dangerous request methods are sent with `Content-type: application/json` header.
So classic HTML-forms are rejected, while the security of JS-requests relies on CORS-policy.
### CORS
JS-clients send their credentials, so the server must implement the whitelist of origins.
### API authentication
JWT-tokens are stored in the `access_token` cookie.
 