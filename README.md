# Volistx Framework
Volistx Framework For RESTful API Based on Laravel/Lumen 9.x

This is a framework skeleton for Volistx API platform using Lumen PHP Framework.

Let's make some awesome thing together!

### Requirements
- PHP 8.1.0+
- All Required Extensions for Laravel/Lumen
- Redis and Redis PHP Extension 

### Optional Requirements
- Swoole Extension

### Installation
```
composer create-project --prefer-dist volistx/framework myproject
```

### Usage
1. Copy `.env.example` to `.env`.
2. Get GeoPoint token and put it to `.env` file.
3. Get StackPath API client id and secret from [StackPath](https://control.stackpath.com/account/api-management) and put it to `.env` file.
4. Run following commands:

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

### Swoole Setup
Run Laravel/Lumen Swoole using this package:
```
php bin/laravels start -i
```

If you want the Swoole server to run after reboot, add the following line to your crontab:
```
@reboot php /path/to/bin/laravels start -i
```

For supervisor, check following configuration:
```
[program:volistx-swoole-worker]
directory=/path/to/
command=php81 /path/to/bin/laravels start -i
numprocs=1
autostart=true
autorestart=true
startretries=3
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/supervisor/%(program_name)s.log
```

Check more information about it at [hhxsv5/laravel-s](https://github.com/hhxsv5/laravel-s)