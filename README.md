# Volistx Framework
Volistx Framework For RESTful API Based on Laravel/Lumen

This is a framework skeleton for Volistx API platform using Lumen PHP Framework.

Let's make some awesome thing together!

### Requirements
- PHP 8.1
- MaxmindDB Extension
- All Extensions for Lumen

### Optional Requirements
- Swoole Extension

### Installation
```
composer create-project --prefer-dist volistx/framework myproject ^5.0
```

### Usage
First, copy `.env.example` to `.env`.

After that, run following commands:

```
composer install
php artisan key:generate
php artisan migrate
php artisan cloudflare:reload
php artisan stackpath:reload
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

You can pre-compile application for OPcache using this command. You should enable dups_fix before this operation.

```
php artisan opcache:compile
```

For more information, please go to https://docs.volistx.io/vskeleton/introduction
