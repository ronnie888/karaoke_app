<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\YouTube\GetPopularVideosAction;
use App\Actions\YouTube\GetVideoDetailsAction;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\VideoResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API Video Controller
 *
 * Handles API endpoints for individual video details and popular videos.
 * Returns JSON responses using ApiResponse helper and VideoResource.
 */
class VideoController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly GetVideoDetailsAction $videoDetailsAction,
        private readonly GetPopularVideosAction $popularVideosAction,
    ) {}

    /**
     * Get video details by ID.
     *
     * GET /api/v1/videos/{videoId}
     *
     * @OA\Get(
     *     path="/api/v1/videos/{videoId}",
     *     tags={"Videos"},
     *     summary="Get video details",
     *     @OA\Parameter(name="videoId", in="path", required=true, description="YouTube video ID"),
     *     @OA\Response(response=200, description="Video details"),
     *     @OA\Response(response=404, description="Video not found"),
     *     @OA\Response(response=429, description="Rate limit exceeded")
     * )
     */
    public function show(string $videoId): JsonResponse
    {
        try {
            // Validate video ID format
            if (! preg_match('/^[a-zA-Z0-9_-]{11}$/', $videoId)) {
                return ApiResponse::validationError(
                    errors: ['video_id' => ['Invalid video ID format. Expected 11 alphanumeric characters.']],
                    message: 'Invalid video ID format'
                );
            }

            // Get video details
            $video = $this->videoDetailsAction->execute($videoId);

            // Check if video exists
            if ($video === null) {
                return ApiResponse::error(
                    message: 'Video not found or unavailable.',
                    code: 404,
                    errors: ['video_id' => $videoId]
                );
            }

            // Return success response
            return ApiResponse::success(
                data: new VideoResource($video),
                message: 'Video details retrieved successfully.'
            );
        } catch (\Exception $e) {
            // Log error
            logger()->error('Failed to get video details', [
                'video_id' => $videoId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Return error response
            return ApiResponse::error(
                message: 'Failed to retrieve video details.',
                code: 500,
                errors: config('app.debug') ? [
                    'exception' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ] : null
            );
        }
    }

    /**
     * Get popular/trending videos.
     *
     * GET /api/v1/popular?maxResults=25&regionCode=US
     *
     * @OA\Get(
     *     path="/api/v1/popular",
     *     tags={"Videos"},
     *     summary="Get popular videos",
     *     @OA\Parameter(name="maxResults", in="query", description="Max results (1-50)"),
     *     @OA\Parameter(name="regionCode", in="query", description="Region code (e.g., US, GB)"),
     *     @OA\Response(response=200, description="Popular videos"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=429, description="Rate limit exceeded")
     * )
     */
    public function popular(Request $request): JsonResponse
    {
        try {
            // Validate input
            $validated = $request->validate([
                'maxResults' => 'sometimes|integer|min:1|max:50',
                'regionCode' => 'sometimes|string|size:2|regex:/^[A-Z]{2}$/',
            ]);

            // Get parameters with defaults
            $maxResults = (int) ($validated['maxResults'] ?? 25);
            $regionCode = isset($validated['regionCode'])
                ? strtoupper($validated['regionCode'])
                : null;

            // Execute action
            $results = $this->popularVideosAction->execute($maxResults, $regionCode);

            // Return collection response
            return ApiResponse::collection(
                collection: $results,
                resourceClass: VideoResource::class,
                message: sprintf(
                    'Retrieved %d popular video%s%s',
                    $results->count(),
                    $results->count() === 1 ? '' : 's',
                    $regionCode ? " for region {$regionCode}" : ''
                )
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError(
                message: 'Invalid request parameters.',
                errors: $e->errors()
            );
        } catch (\Exception $e) {
            // Log error
            logger()->error('Failed to get popular videos', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Return error response
            return ApiResponse::error(
                message: 'Failed to retrieve popular videos.',
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
