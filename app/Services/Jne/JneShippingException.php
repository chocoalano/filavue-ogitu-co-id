<?php

namespace App\Services\Jne;

use Illuminate\Http\Client\Response;
use RuntimeException;
use Throwable;

class JneShippingException extends RuntimeException
{
    public function __construct(
        string $message,
        private readonly ?array $context = null,
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public static function validation(string $message, array $context = []): self
    {
        return new self($message, $context, 422);
    }

    public static function requestFailed(Response $response, array $payload = []): self
    {
        return new self(
            message: 'Request JNE API gagal dengan status HTTP '.$response->status().'.',
            context: [
                'status' => $response->status(),
                'payload' => self::sanitizePayload($payload),
                'body' => $response->body(),
            ],
            code: $response->status(),
        );
    }

    public static function invalidJson(string $body, array $payload = []): self
    {
        return new self(
            message: 'Response JNE API bukan JSON valid.',
            context: [
                'payload' => self::sanitizePayload($payload),
                'body' => $body,
            ],
            code: 500,
        );
    }

    public static function connection(Throwable $throwable, array $payload = []): self
    {
        return new self(
            message: 'Tidak dapat terhubung ke JNE API: '.$throwable->getMessage(),
            context: [
                'payload' => self::sanitizePayload($payload),
            ],
            code: 500,
            previous: $throwable,
        );
    }

    public function context(): ?array
    {
        return $this->context;
    }

    private static function sanitizePayload(array $payload): array
    {
        if (array_key_exists('api_key', $payload)) {
            $payload['api_key'] = '******';
        }

        return $payload;
    }
}
