<?php

declare(strict_types=1);

namespace MuhammedSalama\Base\Tests\Unit;

use MuhammedSalama\Base\Helpers\ApiResponse;
use MuhammedSalama\Base\Tests\TestCase;

class ApiResponseTest extends TestCase
{
    public function test_success_returns_the_standard_envelope(): void
    {
        $response = ApiResponse::success(['id' => 1], 'OK');
        /** @var array<string, mixed> $payload */
        $payload = $response->getData(true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue((bool) $payload['status']);
        $this->assertSame('OK', $payload['message']);
        $this->assertSame(['id' => 1], $payload['data']);
    }

    public function test_created_uses_201(): void
    {
        $response = ApiResponse::created(['id' => 5]);

        $this->assertSame(201, $response->getStatusCode());
    }

    public function test_error_returns_a_failure_envelope(): void
    {
        $response = ApiResponse::error('Nope', 400, ['field' => 'bad']);
        /** @var array<string, mixed> $payload */
        $payload = $response->getData(true);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertFalse((bool) $payload['status']);
        $this->assertSame('Nope', $payload['message']);
        $this->assertSame(['field' => 'bad'], $payload['errors']);
    }
}
