# decpro/livesurf-sdk

PHP SDK для LiveSurf API (https://api.livesurf.ru/).

## Возможности
- PSR-4 класс `Decpro\LiveSurfSdk\LiveSurfApi`
- Полная поддержка всех методов API
- Контроль лимита (10 запросов/сек)
- Автоматические повторы при ошибках 429/5xx
- Простая интеграция и читаемые исключения

## Установка

Через Composer (если опубликовано в Packagist):
```bash
composer require decpro/livesurf-sdk
```

Или вручную (локально):
```php
require_once __DIR__ . '/src/LiveSurfApi.php';
use Decpro\LiveSurfSdk\LiveSurfApi;
```

## Пример использования

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';
use Decpro\LiveSurfSdk\LiveSurfApi;

$api = new LiveSurfApi('ВАШ_API_КЛЮЧ');

// Получаем информацию о пользователе
$user = $api->getUser();
print_r($user);

// Создаём группу
$newGroup = $api->createGroup([
    'name' => 'Тестовая группа PHP',
    'hour_limit' => 50,
    'day_limit' => 1000,
    'timezone' => 'Europe/Moscow',
    'pages' => [
        [
            'url' => ['https://example.com'],
            'showtime' => [15, 30]
        ]
    ]
]);
print_r($newGroup);
```

## Лицензия

MIT License — свободное использование, копирование и модификация.
