<?php

declare(strict_types=1);

/**
 * phpunit подключается к `vendor/autoload.php` напрямую; этот файл нужен
 * только чтобы задать окружение до загрузки приложения.
 */

putenv('APP_ENV=production');
$_ENV['APP_ENV'] = 'production';
$_SERVER['APP_ENV'] = 'production';

require dirname(__DIR__) . '/vendor/autoload.php';
