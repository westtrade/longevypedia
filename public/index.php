<?php

declare(strict_types=1);

/**
 * Точка входа Longevypedia.
 *
 * Всё, что делает приложение, живёт в бандлах и конфиге: здесь только
 * автозагрузка и запуск ядра Bolt. Конфиг читается из `config.php` — его же
 * находит CLI (`vendor/bin/bolt`).
 */

require dirname(__DIR__) . '/vendor/autoload.php';

$app = Bolt\Core\Application::boot(dirname(__DIR__));

(new Bolt\Core\Http\Kernel($app))->run();
