<?php
require_once __DIR__ . '/../src/LiveSurfApi.php';
use Decpro\LiveSurfSdk\LiveSurfApi;

// Введите свой API ключ LiveSurf
$api = new LiveSurfApi('ВАШ_API_КЛЮЧ');

try {
    echo "Получение информации о пользователе...\n";
    $user = $api->getUser();
    print_r($user);

    echo "\nСоздание тестовой группы...\n";
    $group = $api->createGroup([
        'name' => 'Пример группы',
        'hour_limit' => 50,
        'day_limit' => 500,
        'timezone' => 'Europe/Moscow',
        'pages' => [
            ['url' => ['https://example.com'], 'showtime' => [15, 30]]
        ]
    ]);
    print_r($group);

    echo "\nСписок всех групп:\n";
    $groups = $api->getGroups();
    print_r($groups);

    echo "\nУдаление тестовой группы...\n";
    if (!empty($group['id'])) {
        $api->deleteGroup($group['id']);
        echo "Группа удалена.\n";
    }
} catch (Exception $e) {
    echo 'Ошибка: ' . $e->getMessage() . "\n";
}
