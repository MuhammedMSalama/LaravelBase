<?php

namespace MuhammedSalama\Base\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use MuhammedSalama\Base\Helpers\ApiResponse;

/**
 * Base Form Request.
 *
 * Extend this class in your own request classes to keep validation
 * outside the controller and have failed validation automatically
 * return the standard ApiResponse JSON envelope.
 */
abstract class BaseRequest extends FormRequest
{
    /**
     * By default the request is authorized. Override in child classes
     * when you need policy/gate checks.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Define your validation rules in the child request.
     *
     * @return array
     */
    abstract public function rules(): array;

    /**
     * Return a consistent JSON envelope on validation failure
     * instead of Laravel's default redirect/response.
     *
     * @param Validator $validator
     * @return void
     *
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::validation($validator->errors())
        );
    }
}
