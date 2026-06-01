<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Use this trait inside controllers to return a consistent JSON envelope:
 *   { "status": bool, "message": string, "data": mixed }
 *
 * This is a local, editable copy published from the laravel-base package.
 */
trait ApiResponseTrait
{
    protected function success($data = null, ?string $message = 'Success', int $code = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function created($data = null, ?string $message = 'Created successfully'): JsonResponse
    {
        return $this->success($data, $message, Response::HTTP_CREATED);
    }

    protected function error(?string $message = 'Something went wrong', int $code = Response::HTTP_BAD_REQUEST, $errors = null): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }

    protected function validationError($errors, ?string $message = 'Validation error'): JsonResponse
    {
        return $this->error($message, Response::HTTP_UNPROCESSABLE_ENTITY, $errors);
    }

    protected function notFound(?string $message = 'Resource not found'): JsonResponse
    {
        return $this->error($message, Response::HTTP_NOT_FOUND);
    }

    protected function unauthorized(?string $message = 'Unauthorized'): JsonResponse
    {
        return $this->error($message, Response::HTTP_UNAUTHORIZED);
    }

    protected function forbidden(?string $message = 'Forbidden'): JsonResponse
    {
        return $this->error($message, Response::HTTP_FORBIDDEN);
    }

    protected function paginated($paginator, ?string $message = 'Success'): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ], Response::HTTP_OK);
    }
}
