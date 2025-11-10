<?php
declare(strict_types=1);

namespace Decpro\LiveSurfSdk;

use Exception;

/**
 * LiveSurf API PHP SDK
 * =====================
 * Русская версия клиента для работы с API https://api.livesurf.ru/
 *
 * Возможности:
 *  - Авторизация по API ключу (заголовок Authorization)
 *  - Поддержка всех HTTP методов (GET, POST, PATCH, DELETE)
 *  - Контроль лимита скорости (10 запросов в секунду)
 *  - Повтор запросов при ошибках 429/5xx с экспоненциальной задержкой
 *  - Подробные исключения и удобный формат ответов
 *
 * Пример использования см. в файле examples/example.php
 */
class LiveSurfApi
{
    private string $apiKey;
    private string $baseUrl = 'https://api.livesurf.ru/';
    private int $timeout = 15;

    // Контроль лимита запросов
    private array $requestTimestamps = [];
    private int $rateLimit = 10;

    // Настройки повторов (retry)
    private int $maxRetries = 3;
    private int $initialBackoffMs = 500;

    /**
     * Конструктор клиента
     *
     * @param string $apiKey API-ключ вашего аккаунта LiveSurf
     * @param array $options Дополнительные параметры (timeout, rateLimit, baseUrl и т.д.)
     */
    public function __construct(string $apiKey, array $options = [])
    {
        $this->apiKey = $apiKey;
        if (!empty($options['baseUrl'])) $this->baseUrl = rtrim($options['baseUrl'], '/') . '/';
        if (!empty($options['timeout'])) $this->timeout = (int)$options['timeout'];
        if (!empty($options['rateLimit'])) $this->rateLimit = (int)$options['rateLimit'];
        if (!empty($options['maxRetries'])) $this->maxRetries = (int)$options['maxRetries'];
        if (!empty($options['initialBackoffMs'])) $this->initialBackoffMs = (int)$options['initialBackoffMs'];
    }

    /**
     * Контроль лимита запросов (макс. 10/сек)
     */
    private function applyRateLimit(): void
    {
        $now = microtime(true);
        $this->requestTimestamps = array_filter($this->requestTimestamps, fn($t) => $t > ($now - 1));
        if (count($this->requestTimestamps) >= $this->rateLimit) {
            $earliest = min($this->requestTimestamps);
            $sleep = 1 - ($now - $earliest);
            if ($sleep > 0) usleep((int)($sleep * 1_000_000));
        }
        $this->requestTimestamps[] = microtime(true);
    }

    /**
     * Универсальный HTTP-запрос с повтором при ошибках
     */
    private function request(string $method, string $endpoint, array $data = []): mixed
    {
        $attempt = 0;
        $url = $this->baseUrl . ltrim($endpoint, '/');

        while (true) {
            $attempt++;
            $this->applyRateLimit();

            $ch = curl_init($url);
            $headers = [
                'Accept: application/json',
                'Authorization: ' . $this->apiKey,
                'Content-Type: application/json',
            ];

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $this->timeout,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_CUSTOMREQUEST => strtoupper($method),
            ]);

            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
            }

            $response = curl_exec($ch);
            $error = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($error) {
                if ($attempt <= $this->maxRetries) {
                    $this->sleepForRetry($attempt);
                    continue;
                }
                throw new Exception('Ошибка соединения: ' . $error);
            }

            $decoded = json_decode($response, true);
            if ($httpCode >= 200 && $httpCode < 300) return $decoded ?? $response;

            if (($httpCode == 429 || ($httpCode >= 500 && $httpCode < 600)) && $attempt <= $this->maxRetries) {
                $this->sleepForRetry($attempt);
                continue;
            }

            $msg = is_array($decoded) && isset($decoded['error']) ? $decoded['error'] : $response;
            throw new Exception("Ошибка API ({$httpCode}): {$msg}");
        }
    }

    /**
     * Задержка между повторами (экспоненциальный рост с джиттером)
     */
    private function sleepForRetry(int $attempt): void
    {
        $base = $this->initialBackoffMs * (2 ** ($attempt - 1));
        $jitter = (int)($base * 0.2);
        $delayMs = $base + rand(-$jitter, $jitter);
        usleep((int)($delayMs * 1000));
    }

    // Универсальные обёртки
    public function get(string $endpoint): mixed { return $this->request('GET', $endpoint); }
    public function post(string $endpoint, array $data = []): mixed { return $this->request('POST', $endpoint, $data); }
    public function patch(string $endpoint, array $data = []): mixed { return $this->request('PATCH', $endpoint, $data); }
    public function delete(string $endpoint): mixed { return $this->request('DELETE', $endpoint); }

    // Методы API
    public function getCategories(): mixed { return $this->get('categories/'); }
    public function getCountries(): mixed { return $this->get('countries/'); }
    public function getLanguages(): mixed { return $this->get('languages/'); }
    public function getSourcesAd(): mixed { return $this->get('sources/ad/'); }
    public function getSourcesMessengers(): mixed { return $this->get('sources/messengers/'); }
    public function getSourcesSearch(): mixed { return $this->get('sources/search/'); }
    public function getSourcesSocial(): mixed { return $this->get('sources/social/'); }
    public function getUser(): mixed { return $this->get('user/'); }
    public function setAutoMode(): mixed { return $this->post('user/automode/'); }
    public function setManualMode(): mixed { return $this->post('user/manualmode/'); }
    public function getGroups(): mixed { return $this->get('group/all/'); }
    public function getGroup(int $id): mixed { return $this->get("group/{$id}/"); }
    public function createGroup(array $data): mixed { return $this->post('group/create/', $data); }
    public function updateGroup(int $id, array $data): mixed { return $this->patch("group/{$id}/", $data); }
    public function deleteGroup(int $id): mixed { return $this->delete("group/{$id}/"); }
    public function cloneGroup(int $id, array $data = []): mixed { return $this->post("group/{$id}/clone/", $data); }
    public function addGroupCredits(int $id, int $credits): mixed { return $this->post("group/{$id}/add_credits/", ['credits' => $credits]); }
    public function getPage(int $id): mixed { return $this->get("page/{$id}/"); }
    public function createPage(array $data): mixed { return $this->post('page/create/', $data); }
    public function updatePage(int $id, array $data): mixed { return $this->patch("page/{$id}/", $data); }
    public function deletePage(int $id): mixed { return $this->delete("page/{$id}/"); }
    public function clonePage(int $id): mixed { return $this->post("page/{$id}/clone/"); }
    public function movePageUp(int $id): mixed { return $this->post("page/{$id}/up/"); }
    public function movePageDown(int $id): mixed { return $this->post("page/{$id}/down/"); }
    public function startPage(int $id): mixed { return $this->post("page/{$id}/start/"); }
    public function stopPage(int $id): mixed { return $this->post("page/{$id}/stop/"); }
    public function getStats(array $params): mixed { return $this->get('pages-compiled-stats/?' . http_build_query($params)); }
}
