<?php

namespace App\Http\Controllers;

use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SongStreamController extends Controller
{
    /**
     * Stream a song with HTTP range request support
     */
    public function stream(Request $request, Song $song)
    {
        // Update play statistics
        $song->incrementPlayCount();

        // Get storage disk
        $disk = Storage::disk($song->storage_driver);

        // Check if file exists
        if (!$disk->exists($song->file_path)) {
            abort(404, 'Video file not found');
        }

        // For cloud storage (Spaces/S3), redirect to CDN URL
        if (in_array($song->storage_driver, ['spaces', 's3']) && $song->cdn_url) {
            return redirect()->away($song->cdn_url);
        }

        // For local files, stream with range support
        return $this->streamLocalFile($disk, $song, $request);
    }

    /**
     * Stream local file with HTTP range request support
     */
    protected function streamLocalFile($disk, Song $song, Request $request): StreamedResponse
    {
        $path = $song->file_path;
        $size = $song->file_size;
        $mimeType = 'video/mp4';

        // Get range header
        $range = $request->header('Range');

        // No range request - send entire file
        if (!$range) {
            return response()->stream(
                function () use ($path) {
                    $stream = fopen($path, 'rb');
                    fpassthru($stream);
                    fclose($stream);
                },
                200,
                [
                    'Content-Type' => $mimeType,
                    'Content-Length' => $size,
                    'Accept-Ranges' => 'bytes',
                    'Cache-Control' => 'max-age=31536000, public',
                ]
            );
        }

        // Parse range header (e.g., "bytes=0-1023")
        if (!preg_match('/bytes=(\d+)-(\d+)?/', $range, $matches)) {
            abort(416, 'Requested Range Not Satisfiable');
        }

        $start = (int) $matches[1];
        $end = isset($matches[2]) && $matches[2] !== '' ? (int) $matches[2] : $size - 1;

        // Validate range
        if ($start > $end || $start < 0 || $end >= $size) {
            abort(416, 'Requested Range Not Satisfiable');
        }

        $length = $end - $start + 1;

        // Stream partial content
        return response()->stream(
            function () use ($path, $start, $length) {
                $stream = fopen($path, 'rb');
                fseek($stream, $start);

                $buffer = 8192; // 8KB chunks
                $remaining = $length;

                while ($remaining > 0 && !feof($stream)) {
                    $read = min($buffer, $remaining);
                    echo fread($stream, $read);
                    $remaining -= $read;
                    flush();
                }

                fclose($stream);
            },
            206, // Partial Content
            [
                'Content-Type' => $mimeType,
                'Content-Length' => $length,
                'Content-Range' => "bytes {$start}-{$end}/{$size}",
                'Accept-Ranges' => 'bytes',
                'Cache-Control' => 'max-age=31536000, public',
            ]
        );
    }

    /**
     * Get song metadata (for frontend player)
     */
    public function metadata(Song $song)
    {
        return response()->json([
            'id' => $song->id,
            'title' => $song->title,
            'artist' => $song->artist,
            'duration' => $song->duration,
            'formatted_duration' => $song->formatted_duration,
            'stream_url' => $song->stream_url,
            'thumbnail_url' => $song->thumbnail_url,
            'genre' => $song->genre,
            'language' => $song->language,
        ]);
    }
}
