<?php

namespace App\Services;

use Exception;
use FFMpeg\FFProbe;

class VideoMetadataExtractor
{
    protected ?FFProbe $ffprobe = null;

    public function __construct()
    {
        try {
            $this->ffprobe = FFProbe::create([
                'ffmpeg.binaries'  => config('media.ffmpeg_binaries', '/usr/bin/ffmpeg'),
                'ffprobe.binaries' => config('media.ffprobe_binaries', '/usr/bin/ffprobe'),
                'timeout'          => 3600,
                'ffmpeg.threads'   => 12,
            ]);
        } catch (Exception $e) {
            // FFmpeg not available, will use fallback methods
            logger()->warning('FFmpeg not available: ' . $e->getMessage());
        }
    }

    /**
     * Extract video metadata from file
     */
    public function extract(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new Exception("File not found: {$filePath}");
        }

        // If FFProbe is available, use it
        if ($this->ffprobe) {
            return $this->extractWithFFProbe($filePath);
        }

        // Fallback to basic file info
        return $this->extractBasicInfo($filePath);
    }

    /**
     * Extract metadata using FFProbe
     */
    protected function extractWithFFProbe(string $filePath): array
    {
        try {
            $format = $this->ffprobe->format($filePath);
            $videoStreams = $this->ffprobe->streams($filePath)->videos();
            $audioStreams = $this->ffprobe->streams($filePath)->audios();

            $videoStream = $videoStreams->first();
            $audioStream = $audioStreams->first();

            return [
                'duration' => (int) ceil($format->get('duration', 0)),
                'bitrate' => (int) ($format->get('bit_rate', 0) / 1000), // to kbps
                'file_size' => (int) $format->get('size', filesize($filePath)),
                'width' => $videoStream ? $videoStream->get('width') : null,
                'height' => $videoStream ? $videoStream->get('height') : null,
                'video_codec' => $videoStream ? $videoStream->get('codec_name') : null,
                'audio_codec' => $audioStream ? $audioStream->get('codec_name') : null,
                'fps' => $videoStream ? $this->calculateFps($videoStream) : null,
            ];
        } catch (Exception $e) {
            throw new Exception("Failed to extract metadata with FFProbe: {$e->getMessage()}");
        }
    }

    /**
     * Calculate FPS from video stream
     */
    protected function calculateFps($videoStream): ?float
    {
        try {
            $rFrameRate = $videoStream->get('r_frame_rate');
            if ($rFrameRate && str_contains($rFrameRate, '/')) {
                [$num, $den] = explode('/', $rFrameRate);
                return $den > 0 ? round($num / $den, 2) : null;
            }
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Extract basic file information without FFProbe
     */
    protected function extractBasicInfo(string $filePath): array
    {
        $fileSize = filesize($filePath);

        // Try to get duration from getID3 or other methods
        $duration = $this->estimateDuration($filePath);

        return [
            'duration' => $duration,
            'bitrate' => null,
            'file_size' => $fileSize,
            'width' => null,
            'height' => null,
            'video_codec' => 'unknown',
            'audio_codec' => 'unknown',
            'fps' => null,
        ];
    }

    /**
     * Estimate duration using file size (rough estimate)
     * Assumes average bitrate of 2000 kbps for karaoke videos
     */
    protected function estimateDuration(string $filePath): int
    {
        $fileSize = filesize($filePath);
        $avgBitrate = 2000; // kbps
        $duration = ($fileSize * 8) / ($avgBitrate * 1000);

        return (int) ceil($duration);
    }

    /**
     * Check if FFProbe is available
     */
    public function isAvailable(): bool
    {
        return $this->ffprobe !== null;
    }

    /**
     * Get video dimensions
     */
    public function getDimensions(string $filePath): ?array
    {
        if (!$this->ffprobe) {
            return null;
        }

        try {
            $videoStream = $this->ffprobe->streams($filePath)->videos()->first();

            if (!$videoStream) {
                return null;
            }

            return [
                'width' => $videoStream->get('width'),
                'height' => $videoStream->get('height'),
            ];
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get video duration only
     */
    public function getDuration(string $filePath): int
    {
        if (!$this->ffprobe) {
            return $this->estimateDuration($filePath);
        }

        try {
            $format = $this->ffprobe->format($filePath);
            return (int) ceil($format->get('duration', 0));
        } catch (Exception $e) {
            return $this->estimateDuration($filePath);
        }
    }

    /**
     * Generate thumbnail from video (if needed in future)
     */
    public function generateThumbnail(string $filePath, string $outputPath, int $timeSeconds = 5): bool
    {
        if (!$this->ffprobe) {
            return false;
        }

        try {
            $ffmpeg = \FFMpeg\FFMpeg::create([
                'ffmpeg.binaries'  => config('media.ffmpeg_binaries', '/usr/bin/ffmpeg'),
                'ffprobe.binaries' => config('media.ffprobe_binaries', '/usr/bin/ffprobe'),
            ]);

            $video = $ffmpeg->open($filePath);
            $frame = $video->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds($timeSeconds));
            $frame->save($outputPath);

            return true;
        } catch (Exception $e) {
            logger()->error('Failed to generate thumbnail: ' . $e->getMessage());
            return false;
        }
    }
}
