# Phase 2: YouTube Integration & Search — Overview

**Phase Duration**: 3-4 days
**Status**: 🚧 In Progress
**Started**: 2025-01-23

---

## 📋 Phase Objectives

This phase connects the core architecture (Phase 1) to user-facing endpoints by:

1. **Creating HTTP layer** - Controllers, routes, middleware
2. **Building search functionality** - Web and API endpoints for video search
3. **Implementing video player routing** - Watch page with YouTube IFrame integration
4. **Adding validation** - Form requests for input sanitization
5. **Configuring rate limiting** - Protect YouTube API quota

---

## 🎯 Success Criteria

- [x] SearchRequest form validation created
- [ ] Rate limiting middleware configured
- [ ] Web routes defined (/, /search, /watch/{id})
- [ ] API routes defined (/api/v1/search, /api/v1/videos/{id})
- [ ] KaraokeController implemented (web UI)
- [ ] Api\SearchController implemented (JSON API)
- [ ] Api\VideoController implemented (JSON API)
- [ ] All controllers have tests
- [ ] PHPStan Level 8 passes
- [ ] Documentation complete

---

## 📐 Architecture Overview

### Request Flow (Web)
```
User Browser
    ↓
Route: GET /search?q=karaoke
    ↓
SearchRequest validation
    ↓
KaraokeController@search
    ↓
SearchVideosAction
    ↓
YouTubeService
    ↓
Cache / YouTube API
    ↓
VideoResource collection
    ↓
Blade view with results
```

### Request Flow (API)
```
Client (Mobile/SPA)
    ↓
Route: GET /api/v1/search?q=karaoke
    ↓
Throttle: 60/min
    ↓
SearchRequest validation
    ↓
Api\SearchController@search
    ↓
SearchVideosAction
    ↓
YouTubeService
    ↓
ApiResponse::collection()
    ↓
JSON response
```

---

## 📁 Files to Create

### Form Requests
- `app/Http/Requests/SearchRequest.php` - Search input validation

### Middleware
- `app/Http/Middleware/RateLimitYouTubeApi.php` - Custom YouTube quota protection

### Controllers
- `app/Http/Controllers/KaraokeController.php` - Web UI controller
- `app/Http/Controllers/Api/SearchController.php` - API search endpoint
- `app/Http/Controllers/Api/VideoController.php` - API video details endpoint

### Routes
- `routes/web.php` - Web routes (/, /search, /watch/{id})
- `routes/api.php` - API routes (/api/v1/*)

### Tests
- `tests/Feature/Web/SearchTest.php` - Web search integration tests
- `tests/Feature/Api/SearchApiTest.php` - API search tests
- `tests/Feature/Api/VideoApiTest.php` - API video details tests

### Documentation
- `01-form-requests.md` - Validation documentation
- `02-middleware.md` - Rate limiting setup
- `03-routes.md` - Route definitions
- `04-controllers.md` - Controller implementation
- `05-testing.md` - Test coverage
- `PHASE-2-COMPLETE.md` - Summary document

---

## 🔑 Key Design Decisions

### 1. Thin Controllers
Controllers delegate all business logic to Actions:
```php
public function search(SearchRequest $request)
{
    $results = $this->searchAction->executeFromArray($request->validated());
    return view('karaoke.search', compact('results'));
}
```

### 2. Form Request Validation
All input validation happens in dedicated Form Request classes:
```php
class SearchRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'q' => 'required|string|min:2|max:100',
            'maxResults' => 'integer|min:1|max:50',
        ];
    }
}
```

### 3. Dual Response Formats
Same actions power both web and API:
- Web: Returns Blade views with data
- API: Returns ApiResponse::collection() JSON

### 4. Rate Limiting Strategy
- Web routes: 60 requests/minute per IP
- API routes: 60 requests/minute per user/IP
- YouTube-specific: Custom middleware for quota tracking

### 5. Error Handling
- Web: Flash messages + redirect
- API: JSON error responses with proper status codes

---

## 🛡️ Security Measures

1. **Input Validation**
   - All requests validated via SearchRequest
   - SQL injection prevention (Eloquent/Query Builder)
   - XSS prevention (Blade escaping)

2. **Rate Limiting**
   - Global API throttling (60/min)
   - Custom YouTube quota middleware (10/min)
   - IP-based limiting for anonymous users

3. **CSRF Protection**
   - Enabled for all POST/PUT/DELETE web routes
   - Excluded for API routes (using Sanctum later)

4. **API Key Security**
   - Never exposed to frontend
   - Server-side only in YouTubeService
   - Environment variable (.env)

---

## 📊 Testing Strategy

### Feature Tests (E2E)
- Test full request → response flow
- Use HTTP mocking for YouTube API
- Test validation errors
- Test rate limiting

### Example Test
```php
test('search page returns results', function () {
    Http::fake([
        'youtube.googleapis.com/*' => Http::response([
            'items' => [/* mock video data */]
        ])
    ]);

    $response = $this->get('/search?q=karaoke');

    $response->assertOk()
        ->assertViewHas('results')
        ->assertSee('karaoke');
});
```

---

## 🚀 Implementation Order

1. ✅ **Form Requests** - Validation foundation
2. ✅ **Middleware** - Rate limiting setup
3. **Routes** - Define endpoints
4. **Controllers** - Business logic integration
5. **Tests** - Verify functionality
6. **Documentation** - Complete phase summary

---

## 📦 Dependencies

### From Phase 1
- ✅ VideoSearchDTO
- ✅ VideoResultDTO
- ✅ YouTubeService
- ✅ SearchVideosAction
- ✅ GetVideoDetailsAction
- ✅ ApiResponse helper
- ✅ VideoResource

### External Packages
- Laravel HTTP Client (included)
- Laravel Validation (included)
- Laravel Rate Limiting (included)

---

## ⏭️ Next Steps After Phase 2

Phase 3 will focus on frontend:
- Blade layouts and components
- Search UI with Alpine.js
- Video player integration
- Mobile responsiveness

---

## 📝 Notes

- YouTube API key not required for controller development (use HTTP mocking)
- Redis optional (file cache works fine for development)
- All controllers follow RESTful conventions
- API versioned (/api/v1/) for future compatibility

---

**Last Updated**: 2025-01-23
**Next Review**: After controller implementation
