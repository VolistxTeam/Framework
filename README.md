# Volistx Framework
Volistx Framework For RESTful API Based on Laravel 9.x (Slimmed Version)

This is a framework skeleton for Volistx API platform using Laravel PHP Framework.

Let's make some awesome thing together!

### Requirements
- PHP 8.1.2 or Above
- All Required Extensions for Laravel 9.x
- Redis PHP Extension
- Swoole or OpenSwoole Extension

### Installation
```
composer create-project --prefer-dist volistx/framework myproject
```

### Usage
1. Copy `.env.example` to `.env`.
2. Get GeoPoint API key and put it to `.env` file. (Optional, only if you want to use country filter)
3. Run following commands:

```
composer install
php artisan key:generate
php artisan migrate
```

Do not forget to set a cronjob for production (This is not required if you're using Swoole):
```
* * * * * php /path/to/artisan schedule:run
```

Generate an admin access key using this command:
```
php artisan access-key:generate
```

### Swoole Setup
It uses Laravel Octane. You can use Swoole or OpenSwoole. You can find the installation guide here: https://laravel.com/docs/9.x/octane

Run Swoole using this command:
```
php artisan octane:start
```

If you want the Swoole server to run after reboot, add the following line to your crontab:
```
@reboot php artisan octane:start
```

For Supervisor, check following configuration:
```
[program:volistx-swoole-worker]
directory=/path/to/
command=php artisan octane:start
numprocs=1
autostart=true
autorestart=true
startretries=3
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/supervisor/%(program_name)s.log
```