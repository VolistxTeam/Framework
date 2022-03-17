# Volistx Framework
Volistx Framework For RESTful API Based on Laravel/Lumen

This is a framework skeleton for Volistx API platform using Lumen PHP Framework.

Let's make some awesome thing together!

### Requirements
- PHP 8.1
- MaxmindDB Extension
- All Extensions for 

### Installation
```
composer create-project --prefer-dist volistx/framework myproject
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
