<?php
require_once __DIR__ . '/../src/LiveSurfApi.php';
use Decpro\LiveSurfSdk\LiveSurfApi;

// Инициализация клиента
$api = new LiveSurfAPI('YOUR_API_KEY_HERE'); // Введите свой API ключ LiveSurf

try {
    // 1️⃣ Информация о пользователе
    $user = $api->getUser();
    echo "Баланс: {$user['credits']} / Режим: {$user['workmode']}\n\n";

    // 2️⃣ Получаем список категорий
    $categories = $api->getCategories();
    echo "Доступные категории:\n";
    foreach ($categories as $cat) {
        echo "- {$cat['id']}: {$cat['name']}\n";
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

    echo "\nСоздана группа ID: {$newGroup['id']}\n";

    // 4️⃣ Получаем список всех групп
    $groups = $api->getGroups();
    echo "Всего групп: " . count($groups) . "\n";

    // 5️⃣ Клонируем группу
    $clone = $api->cloneGroup($newGroup['id'], 'Копия тестовой группы');
    echo "Создан клон группы ID: {$clone['id']}\n";

    // 6️⃣ Добавляем кредиты в группу
    $api->addGroupCredits($newGroup['id'], 100);
    echo "Кредиты успешно зачислены.\n";

    // 7️⃣ Получаем статистику
    $stats = $api->getStats(['group_id' => $newGroup['id'], 'date' => date('Y-m-d')]);
    echo "\nСтатистика за сегодня:\n";
    print_r($stats);

    // 8️⃣ Удаляем группу
    $api->deleteGroup($newGroup['id']);
    echo "Группа успешно удалена.\n";

} catch (Exception $e) {
    echo "⚠️ Ошибка: " . $e->getMessage();
}
