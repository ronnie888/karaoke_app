<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

class ApiResponse
{
    /**
     * Return a success response.
     */
    public static function success(
        mixed $data = null,
        ?string $message = null,
        int $code = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Return an error response.
     */
    public static function error(
        string $message,
        int $code = 400,
        mixed $errors = null
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }

    /**
     * Return a validation error response.
     *
     * @param  array<string, array<int, string>>  $errors
     */
    public static function validationError(
        array $errors,
        string $message = 'Validation failed',
        int $code = 422
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }

    /**
     * Return a not found response.
     */
    public static function notFound(
        string $message = 'Resource not found',
        int $code = 404
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $code);
    }

    /**
     * Return an unauthorized response.
     */
    public static function unauthorized(
        string $message = 'Unauthorized',
        int $code = 401
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $code);
    }

    /**
     * Return a forbidden response.
     */
    public static function forbidden(
        string $message = 'Forbidden',
        int $code = 403
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $code);
    }

    /**
     * Return a paginated response.
     */
    public static function paginated(
        LengthAwarePaginator $paginator,
        string $resourceClass
    ): ResourceCollection {
        return $resourceClass::collection($paginator)
            ->additional([
                'meta' => [
                    'total' => $paginator->total(),
                    'per_page' => $paginator->perPage(),
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
                'links' => [
                    'first' => $paginator->url(1),
                    'last' => $paginator->url($paginator->lastPage()),
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                ],
            ]);
    }

    /**
     * Return a collection response with resource transformation.
     */
    public static function collection(
        iterable $collection,
        string $resourceClass,
        ?string $message = null
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $resourceClass::collection($collection),
        ]);
    }

    /**
     * Return a resource response.
     */
    public static function resource(
        mixed $resource,
        string $resourceClass,
        ?string $message = null,
        int $code = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => new $resourceClass($resource),
        ], $code);
    }

    /**
     * Return a created response.
     */
    public static function created(
        mixed $data = null,
        ?string $message = 'Resource created successfully',
        int $code = 201
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Return an updated response.
     */
    public static function updated(
        mixed $data = null,
        ?string $message = 'Resource updated successfully',
        int $code = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Return a deleted response.
     */
    public static function deleted(
        ?string $message = 'Resource deleted successfully',
        int $code = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
        ], $code);
    }

    /**
     * Return a no content response.
     */
    public static function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }
}
