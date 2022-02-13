# Volistx Skeleton
Reusable Framework For Volistx API

This is a pre-made skeleton for Volistx API platform using Lumen PHP Framework.

Let's make some awesome thing together!

### Requirements
- PHP 8.1
- MaxmindDB Extension
- All Extensions for Lumen

### Optional Requirements
- Swoole Extension

### Installation
```
composer create-project --prefer-dist cryental/volistx-skeleton myproject ^5.0
```

For Simple Subscription Management:
```
composer create-project --prefer-dist cryental/volistx-skeleton myproject ^4.0
```

### Usage
First, copy `.env.example` to `.env`.

You have to register to MaxMind, get an API key and put it inside `.env` file.
After that, run following commands:

```
composer install
php artisan key:generate
php artisan migrate
php artisan cloudflare:reload
php artisan optimize
```

Do not forget to set a cronjob for production:
```
* * * * * php /path/to/artisan schedule:run
```

Generate an admin access key using this command:
```
php artisan access-key:generate
```

Run Laravel/Lumen Swoole using this package:
```
php artisan swoole:http start
```

If you want the Swoole server to run after reboot, add the following line to your crontab:
```
@reboot php /path/to/artisan swoole:http start
```

For more information, please go to https://docs.volistx.io/vskeleton/introduction
