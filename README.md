# Volistx Skeleton
Reusable Framework For Volistx API

This is a pre-made skeleton for Volistx API platform using Lumen PHP Framework.

Let's make some awesome thing together!

### Installation
```
composer create-project --prefer-dist cryental/volistx-skeleton blog
```

### Usage
You have to register to MaxMind, get an API key and put it inside .env file.
After that, run following commands:

```
composer install
php artisan migrate
php artisan geoip:update
```
