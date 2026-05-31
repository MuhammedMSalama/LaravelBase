<?php

namespace MuhammedSalama\Base\Helpers;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse
{
    /**
     * Success response.
     *
     * @param mixed $data
     * @param string|null $message
     * @param int $code
     * @return JsonResponse
     */
    public static function success($data = null, ?string $message = 'Success', int $code = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    /**
     * Error response.
     *
     * @param string|null $message
     * @param int $code
     * @param mixed $errors
     * @return JsonResponse
     */
    public static function error(?string $message = 'Something went wrong', int $code = Response::HTTP_BAD_REQUEST, $errors = null): JsonResponse
    {
        return response()->json([
            'status'  => false,
            'message' => $message,
            'errors'  => $errors,
        ], $code);
    }

    /**
     * Validation error response (422).
     *
     * @param mixed $errors
     * @param string|null $message
     * @return JsonResponse
     */
    public static function validation($errors, ?string $message = 'Validation error'): JsonResponse
    {
        return self::error($message, Response::HTTP_UNPROCESSABLE_ENTITY, $errors);
    }

    /**
     * Not found response (404).
     *
     * @param string|null $message
     * @return JsonResponse
     */
    public static function notFound(?string $message = 'Resource not found'): JsonResponse
    {
        return self::error($message, Response::HTTP_NOT_FOUND);
    }

    /**
     * Unauthorized response (401).
     *
     * @param string|null $message
     * @return JsonResponse
     */
    public static function unauthorized(?string $message = 'Unauthorized'): JsonResponse
    {
        return self::error($message, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Forbidden response (403).
     *
     * @param string|null $message
     * @return JsonResponse
     */
    public static function forbidden(?string $message = 'Forbidden'): JsonResponse
    {
        return self::error($message, Response::HTTP_FORBIDDEN);
    }

    /**
     * Created response (201).
     *
     * @param mixed $data
     * @param string|null $message
     * @return JsonResponse
     */
    public static function created($data = null, ?string $message = 'Created successfully'): JsonResponse
    {
        return self::success($data, $message, Response::HTTP_CREATED);
    }

    /**
     * No content response (204).
     *
     * @return JsonResponse
     */
    public static function noContent(): JsonResponse
    {
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Paginated response (keeps Laravel pagination meta).
     *
     * @param \Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator
     * @param string|null $message
     * @return JsonResponse
     */
    public static function paginated($paginator, ?string $message = 'Success'): JsonResponse
    {
        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => $paginator->items(),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ], Response::HTTP_OK);
    }
}
