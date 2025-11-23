<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\YouTube\SearchVideosAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\VideoResource;
use Illuminate\Http\JsonResponse;

/**
 * API Search Controller
 *
 * Handles API endpoints for YouTube video search.
 * Returns JSON responses using ApiResponse helper and VideoResource.
 */
class SearchController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly SearchVideosAction $searchAction,
    ) {}

    /**
     * Search for YouTube videos.
     *
     * GET /api/v1/search?q=karaoke&maxResults=25
     *
     * @OA\Get(
     *     path="/api/v1/search",
     *     tags={"Search"},
     *     summary="Search YouTube videos",
     *     @OA\Parameter(name="q", in="query", required=true, description="Search query"),
     *     @OA\Parameter(name="maxResults", in="query", description="Max results (1-50)"),
     *     @OA\Parameter(name="order", in="query", description="Sort order"),
     *     @OA\Response(response=200, description="Search results"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=429, description="Rate limit exceeded")
     * )
     */
    public function search(SearchRequest $request): JsonResponse
    {
        try {
            // Get validated search parameters
            $params = $request->getSearchParams();

            // Execute search action
            $results = $this->searchAction->executeFromArray($params);

            // Return collection response
            return ApiResponse::collection(
                collection: $results,
                resourceClass: VideoResource::class,
                message: sprintf(
                    'Found %d result%s for "%s"',
                    $results->count(),
                    $results->count() === 1 ? '' : 's',
                    $params['query']
                )
            );
        } catch (\InvalidArgumentException $e) {
            // Validation error (from DTO)
            return ApiResponse::validationError(
                errors: ['params' => [$e->getMessage()]],
                message: 'Invalid search parameters'
            );
        } catch (\Exception $e) {
            // Log error
            logger()->error('API search failed', [
                'query' => $request->input('q'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Return error response
            return ApiResponse::error(
                message: 'Search failed. Please try again later.',
                code: 500,
                errors: config('app.debug') ? [
                    'exception' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ] : null
            );
        }
    }
}
