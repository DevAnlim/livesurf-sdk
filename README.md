# livesurf-sdk

PHP SDK для LiveSurf API (https://api.livesurf.ru/).

## Возможности
- PSR-4 класс `Decpro\LiveSurfSdk\LiveSurfApi`
- Полная поддержка всех методов API
- Контроль лимита (10 запросов/сек)
- Автоматические повторы при ошибках 429/5xx
- Простая интеграция и читаемые исключения

## Установка

Через Composer
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

// Инициализация клиента
$api = new LiveSurfApi('ВАШ_API_КЛЮЧ');

try {
    // 1️⃣ Информация о пользователе
    $user = $api->getUser();
    echo "Баланс: {$user['credits']} / Режим: {$user['workmode']}";

    // 2️⃣ Получаем список категорий
    $categories = $api->getCategories();
    echo "Доступные категории:";

    foreach ($categories as $cat) {
        echo "- {$cat['id']}: {$cat['name']}";
    }

    // 3️⃣ Создаём новую группу
    $newGroup = $api->createGroup([
        'name' => 'Тестовая группа PHP SDK',
        'hour_limit' => 50,
        'day_limit' => 1000,
        'category' => 1,
        'language' => 1,
        'timezone' => 'Europe/Moscow',
        'use_profiles' => true,
        'geo' => [1, 2],
        'pages' => [
            [
                'url' => ['https://example.com'],
                'showtime' => [15, 30]
            ]
        ]
    ]);

    echo "Создана группа ID: {$newGroup['id']}";

    // 4️⃣ Получаем список всех групп
    $groups = $api->getGroups();
    echo "Всего групп: " . count($groups) . "";

    // 5️⃣ Клонируем группу
    $clone = $api->cloneGroup($newGroup['id'], 'Копия тестовой группы');
    echo "Создан клон группы ID: {$clone['id']}";

    // 6️⃣ Добавляем кредиты в группу
    $api->addGroupCredits($newGroup['id'], 100);
    echo "Кредиты успешно зачислены.";

    // 7️⃣ Получаем статистику
    $stats = $api->getStats(['group_id' => $newGroup['id'], 'date' => date('Y-m-d')]);
    echo "Статистика за сегодня:";
    print_r($stats);

    // 8️⃣ Удаляем группу
    $api->deleteGroup($newGroup['id']);
    echo "Группа успешно удалена.";

} catch (Exception $e) {
    echo "⚠️ Ошибка: " . $e->getMessage();
}
```

## Доступные методы API

### Пользователь
| Метод | Описание |
|-------|----------|
| `getUser()` | Получение информации о пользователе |
| `setAutoMode()` | Включение автоматического режима (АРК) |
| `setManualMode()` | Включение ручного режима работы |

### Категории, страны, языки, источники
| Метод | Описание |
|-------|----------|
| `getCategories()` | Список возможных категорий |
| `getCountries()` | Список возможных стран |
| `getLanguages()` | Список доступных языков |
| `getSourcesAd()` | Список рекламных площадок |
| `getSourcesMessengers()` | Список мессенджеров |
| `getSourcesSearch()` | Список поисковых систем |
| `getSourcesSocial()` | Список социальных сетей |

### Группы
| Метод | Описание |
|-------|----------|
| `getGroups()` | Информация о всех добавленных группах |
| `getGroup(int $groupId)` | Информация о конкретной группе |
| `updateGroup(int $groupId, array $data)` | Изменение настроек группы |
| `deleteGroup(int $groupId)` | Удаление группы |
| `createGroup(array $data)` | Создание новой группы |
| `cloneGroup(int $groupId, string $name)` | Клонирование группы |
| `addGroupCredits(int $groupId, int $credits)` | Зачисление кредитов группы |

### Страницы
| Метод | Описание |
|-------|----------|
| `getPage(int $pageId)` | Информация о конкретной странице |
| `updatePage(int $pageId, array $data)` | Изменение настроек страницы |
| `deletePage(int $pageId)` | Удаление страницы |
| `createPage(array $data)` | Создание новой страницы |
| `clonePage(int $pageId)` | Клонирование страницы |
| `movePageUp(int $pageId)` | Перемещение страницы вверх |
| `movePageDown(int $pageId)` | Перемещение страницы вниз |
| `startPage(int $pageId)` | Запуск страницы в работу |
| `stopPage(int $pageId)` | Остановка работы страницы |
| `getStats(array $params)` | Статистика показа страницы |

## Лицензия

MIT License — свободное использование, копирование и модификация.
