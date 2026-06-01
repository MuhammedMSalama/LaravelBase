<?php

namespace MuhammedSalama\Base\Tests\Unit;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;
use MuhammedSalama\Base\Requests\BaseRequest;
use MuhammedSalama\Base\Tests\TestCase;

class BaseRequestTest extends TestCase
{
    public function test_authorize_returns_true_by_default(): void
    {
        $request = $this->makeRequest(['name' => 'required|string']);

        $this->assertTrue($request->authorize());
    }

    public function test_failed_validation_throws_http_response_exception_with_422(): void
    {
        $request = $this->makeRequest(['name' => 'required|string']);

        $validator = Validator::make([], ['name' => 'required|string']);

        $this->expectException(HttpResponseException::class);

        try {
            $request->callFailedValidation($validator);
        } catch (HttpResponseException $e) {
            $response = $e->getResponse();
            $this->assertSame(422, $response->getStatusCode());

            $payload = json_decode((string)$response->getContent(), true);
            $this->assertFalse($payload['status']);
            $this->assertSame('Validation error', $payload['message']);
            $this->assertArrayHasKey('errors', $payload);

            throw $e;
        }
    }

    public function test_failed_validation_errors_contain_field_messages(): void
    {
        $request = $this->makeRequest(['email' => 'required|email']);

        $validator = Validator::make(['email' => 'not-an-email'], ['email' => 'required|email']);

        $this->expectException(HttpResponseException::class);

        try {
            $request->callFailedValidation($validator);
        } catch (HttpResponseException $e) {
            $payload = json_decode((string)$e->getResponse()->getContent(), true);
            $this->assertArrayHasKey('email', $payload['errors']);
            throw $e;
        }
    }

    // ---------------------------------------------------------------------------

    /** @param array<string, string> $rules */
    private function makeRequest(array $rules): BaseRequest
    {
        return new class($rules) extends BaseRequest {
            /** @param array<string, string> $testRules */
            public function __construct(private array $testRules)
            {
                parent::__construct();
            }

            public function rules(): array
            {
                return $this->testRules;
            }

            public function callFailedValidation(\Illuminate\Contracts\Validation\Validator $validator): void
            {
                $this->failedValidation($validator);
            }
        };
    }
}
