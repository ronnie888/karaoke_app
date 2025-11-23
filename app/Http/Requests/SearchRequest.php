<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Search Request Validation
 *
 * Validates YouTube video search parameters for both web and API endpoints.
 * Ensures input safety, enforces limits, and provides user-friendly error messages.
 */
class SearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Search is publicly accessible (no authentication required)
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string|\Illuminate\Contracts\Validation\ValidationRule>>
     */
    public function rules(): array
    {
        return [
            // Search query (required)
            'q' => [
                'required',
                'string',
                'min:2',
                'max:100',
            ],

            // Maximum results per page
            'maxResults' => [
                'sometimes',
                'integer',
                'min:1',
                'max:50', // YouTube API max is 50
            ],

            // Sort order
            'order' => [
                'sometimes',
                'string',
                Rule::in(['relevance', 'date', 'rating', 'viewCount', 'title']),
            ],

            // Region code (ISO 3166-1 alpha-2)
            'regionCode' => [
                'sometimes',
                'string',
                'size:2',
                'regex:/^[A-Z]{2}$/', // US, GB, PH, etc.
            ],

            // Safe search filter
            'safeSearch' => [
                'sometimes',
                'string',
                Rule::in(['none', 'moderate', 'strict']),
            ],

            // Video category ID
            'videoCategoryId' => [
                'sometimes',
                'string',
                'regex:/^\d+$/', // Numeric string
            ],

            // Video definition (quality)
            'videoDefinition' => [
                'sometimes',
                'string',
                Rule::in(['any', 'high', 'standard']),
            ],

            // Pagination token
            'pageToken' => [
                'sometimes',
                'string',
                'max:255',
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'q' => 'search query',
            'maxResults' => 'maximum results',
            'regionCode' => 'region code',
            'safeSearch' => 'safe search filter',
            'videoCategoryId' => 'video category',
            'videoDefinition' => 'video quality',
            'pageToken' => 'page token',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'q.required' => 'Please enter a search query.',
            'q.min' => 'Search query must be at least 2 characters.',
            'q.max' => 'Search query cannot exceed 100 characters.',
            'q.regex' => 'Search query contains invalid characters.',
            'maxResults.integer' => 'Maximum results must be a number.',
            'maxResults.min' => 'Maximum results must be at least 1.',
            'maxResults.max' => 'Maximum results cannot exceed 50.',
            'order.in' => 'Invalid sort order. Choose: relevance, date, rating, viewCount, or title.',
            'regionCode.size' => 'Region code must be 2 characters (e.g., US, GB, PH).',
            'regionCode.regex' => 'Region code must be uppercase letters only.',
            'safeSearch.in' => 'Safe search must be: none, moderate, or strict.',
            'videoDefinition.in' => 'Video quality must be: any, high, or standard.',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * Sanitize input before validation runs.
     */
    protected function prepareForValidation(): void
    {
        // Trim search query
        if ($this->has('q')) {
            $this->merge([
                'q' => trim((string) $this->input('q')),
            ]);
        }

        // Set defaults
        $this->merge([
            'maxResults' => $this->input('maxResults', 25),
            'order' => $this->input('order', 'relevance'),
            'safeSearch' => $this->input('safeSearch', 'moderate'),
        ]);

        // Uppercase region code if provided
        if ($this->has('regionCode')) {
            $this->merge([
                'regionCode' => strtoupper((string) $this->input('regionCode')),
            ]);
        }
    }

    /**
     * Get validated data as an array suitable for VideoSearchDTO.
     *
     * @return array<string, mixed>
     */
    public function getSearchParams(): array
    {
        $validated = $this->validated();

        return [
            'query' => $validated['q'],
            'maxResults' => $validated['maxResults'] ?? 25,
            'order' => $validated['order'] ?? 'relevance',
            'regionCode' => $validated['regionCode'] ?? null,
            'safeSearch' => $validated['safeSearch'] ?? 'moderate',
            'videoCategoryId' => $validated['videoCategoryId'] ?? null,
            'videoDefinition' => $validated['videoDefinition'] ?? null,
            'pageToken' => $validated['pageToken'] ?? null,
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        // For API requests, throw JSON validation exception
        if ($this->expectsJson()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        // For web requests, redirect back with errors
        parent::failedValidation($validator);
    }
}
