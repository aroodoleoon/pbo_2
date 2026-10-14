<?php
declare(strict_types=1);

namespace PosClient;

class ApiClient
{
    private string $baseUrl;
    private int $timeout;

    public function __construct(string $baseUrl = Config::API_BASE_URL, int $timeout = 8)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->timeout = $timeout;
    }

    public function get(string $endpoint, array $queryParams = []): ApiResponse
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }
        return $this->request('GET', $url);
    }

    public function post(string $endpoint, array $data = []): ApiResponse
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        return $this->request('POST', $url, $data);
    }

    public function patch(string $endpoint, array $data = []): ApiResponse
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        return $this->request('PATCH', $url, $data);
    }

    public function delete(string $endpoint): ApiResponse
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        return $this->request('DELETE', $url);
    }

    private function request(string $method, string $url, ?array $body = null): ApiResponse
    {
        $ch = curl_init();

        $headers = [
            'Accept: application/json',
        ];

        if ($body !== null) {
            $headers[] = 'Content-Type: application/json';
            $payload = json_encode($body);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        }

        $token = Session::getToken();
        if ($token) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        $user = Session::getAuthUser();
        if ($user) {
            if (isset($user['id'])) {
                $headers[] = 'X-User-Id: ' . $user['id'];
            }
            if (isset($user['role'])) {
                $headers[] = 'X-User-Role: ' . $user['role'];
            }
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $rawBody = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($rawBody === false) {
            return new ApiResponse(0, false, null, "Koneksi ke backend Ktor gagal: " . ($curlError ?: 'Server offline'));
        }

        $decoded = json_decode((string)$rawBody, true);

        $isSuccess = ($httpCode >= 200 && $httpCode < 300);
        $errorMessage = null;

        if (!$isSuccess) {
            if (is_array($decoded) && isset($decoded['error']['message'])) {
                $errorMessage = $decoded['error']['message'];
            } elseif (is_array($decoded) && isset($decoded['message'])) {
                $errorMessage = $decoded['message'];
            } else {
                $errorMessage = "HTTP Error {$httpCode}";
            }
        }

        return new ApiResponse($httpCode, $isSuccess, $decoded, $errorMessage);
    }
}

class ApiResponse
{
    private int $statusCode;
    private bool $success;
    private mixed $data;
    private ?string $errorMessage;

    public function __construct(int $statusCode, bool $success, mixed $data, ?string $errorMessage = null)
    {
        $this->statusCode = $statusCode;
        $this->success = $success;
        $this->data = $data;
        $this->errorMessage = $errorMessage;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }
}
