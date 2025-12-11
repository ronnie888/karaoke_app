<?php

namespace App\Services;

class FilenameParser
{
    protected array $genreMap = [
        // Rock
        'Beatles' => 'Rock',
        'Queen' => 'Rock',
        'Rolling Stones' => 'Rock',
        'Led Zeppelin' => 'Rock',
        'Eagles' => 'Rock',
        'Scorpions' => 'Rock',
        'Bon Jovi' => 'Rock',
        'Journey' => 'Rock',
        'Foreigner' => 'Rock',
        'Heart' => 'Rock',
        'Def Leppard' => 'Rock',
        'Green Day' => 'Rock',

        // Pop
        'Madonna' => 'Pop',
        'Michael Jackson' => 'Pop',
        'Whitney Houston' => 'Pop',
        'Mariah Carey' => 'Pop',
        'Celine Dion' => 'Pop',
        'Bruno Mars' => 'Pop',
        'Lady Gaga' => 'Pop',
        'Taylor Swift' => 'Pop',
        'Ed Sheeran' => 'Pop',
        'Ariana Grande' => 'Pop',

        // Country
        'Kenny Rogers' => 'Country',
        'Dolly Parton' => 'Country',
        'Johnny Cash' => 'Country',
        'Willie Nelson' => 'Country',
        'Garth Brooks' => 'Country',

        // R&B/Soul
        'Stevie Wonder' => 'R&B',
        'Aretha Franklin' => 'R&B',
        'Al Green' => 'R&B',

        // OPM (Original Pilipino Music)
        'Aegis' => 'OPM',
        'Rivermaya' => 'OPM',
        'Parokya Ni Edgar' => 'OPM',
        'Eraserheads' => 'OPM',
        'APO Hiking Society' => 'OPM',
        'Freddie Aguilar' => 'OPM',
        'Martin Nievera' => 'OPM',
        'Regine Velasquez' => 'OPM',
        'Sarah Geronimo' => 'OPM',
        'Gary Valenciano' => 'OPM',
        'Ogie Alcasid' => 'OPM',
        'Basil Valdez' => 'OPM',
        'Sharon Cuneta' => 'OPM',
        'Zsa Zsa Padilla' => 'OPM',
        'Yeng Constantino' => 'OPM',
        'Moira Dela Torre' => 'OPM',
        'Angeline Quinto' => 'OPM',
        'KZ Tandingan' => 'OPM',
        'Siakol' => 'OPM',
        'Asin' => 'OPM',
        'Janno Gibbs' => 'OPM',
        'Kitchie Nadal' => 'OPM',
        'Imago' => 'OPM',
        '6cyclemind' => 'OPM',
        'Orange and Lemons' => 'OPM',
        'Cueshe' => 'OPM',
        'Sponge Cola' => 'OPM',
        'Bamboo' => 'OPM',
        'Kamikazee' => 'OPM',
        'Yano' => 'OPM',
        'Introvoys' => 'OPM',
        'South Border' => 'OPM',
        'Side A' => 'OPM',
        'True Faith' => 'OPM',
        'Freestyle' => 'OPM',

        // Soft Rock / MOR
        'Air Supply' => 'Soft Rock',
        'Bread' => 'Soft Rock',
        'Carpenters' => 'Soft Rock',
        'America' => 'Soft Rock',
        'Chicago' => 'Soft Rock',
        'Styx' => 'Soft Rock',
        'REO Speedwagon' => 'Soft Rock',
        'Toto' => 'Soft Rock',
    ];

    protected array $languageKeywords = [
        'filipino' => ['ang', 'ako', 'ikaw', 'siya', 'tayo', 'kami', 'sila', 'na', 'ay', 'sa'],
        'tagalog' => ['ang', 'ako', 'ikaw', 'siya', 'tayo', 'kami', 'sila', 'na', 'ay', 'sa'],
        'english' => ['the', 'is', 'are', 'was', 'were', 'you', 'me', 'love', 'heart'],
    ];

    /**
     * Parse filename to extract title, artist, and metadata
     */
    public function parse(string $filename): array
    {
        // Remove extension
        $name = pathinfo($filename, PATHINFO_FILENAME);

        // Remove common suffixes
        $name = $this->removeCommonSuffixes($name);

        // Try different parsing patterns
        $parsed = $this->tryPatterns($name);

        // Detect language
        $language = $this->detectLanguage($parsed['title'] ?? $name);

        return [
            'title' => $parsed['title'] ?? $name,
            'artist' => $parsed['artist'] ?? null,
            'raw_name' => pathinfo($filename, PATHINFO_FILENAME),
            'language' => $language,
        ];
    }

    /**
     * Remove common suffixes from filename
     */
    protected function removeCommonSuffixes(string $name): string
    {
        $patterns = [
            '/\s*\(HD\s+Karaoke\)/i',
            '/\s*HD\s+Karaoke$/i',
            '/\s*\[HD\s+Karaoke\]/i',
            '/\s*-\s*HD\s+Karaoke$/i',
            '/\s*Karaoke$/i',
            '/\s*\(Official\s+Video\)/i',
            '/\s*Official\s+Video$/i',
            '/\s*\(Lyrics\)/i',
            '/\s*with\s+lyrics$/i',
        ];

        foreach ($patterns as $pattern) {
            $name = preg_replace($pattern, '', $name);
        }

        return trim($name);
    }

    /**
     * Try different filename patterns
     */
    protected function tryPatterns(string $name): array
    {
        // Pattern 1: "TITLE - ARTIST"
        if (preg_match('/^(.+?)\s*-\s*(.+?)$/i', $name, $matches)) {
            $part1 = trim($matches[1]);
            $part2 = trim($matches[2]);

            // Determine which is title and which is artist
            // If part2 is in our genre map, it's likely the artist
            if (isset($this->genreMap[$part2])) {
                return [
                    'title' => $part1,
                    'artist' => $part2,
                ];
            }

            // If part1 looks like a song title (has common title words), swap them
            if ($this->looksLikeSongTitle($part1)) {
                return [
                    'title' => $part1,
                    'artist' => $part2,
                ];
            }

            // Default: assume first part is title
            return [
                'title' => $part1,
                'artist' => $part2,
            ];
        }

        // Pattern 2: "ARTIST - TITLE (extra info)"
        if (preg_match('/^(.+?)\s*-\s*(.+?)\s*\(.*?\)$/i', $name, $matches)) {
            return [
                'title' => trim($matches[2]),
                'artist' => trim($matches[1]),
            ];
        }

        // No pattern matched
        return ['title' => $name, 'artist' => null];
    }

    /**
     * Check if string looks like a song title
     */
    protected function looksLikeSongTitle(string $text): bool
    {
        $titleIndicators = [
            'love', 'heart', 'you', 'me', 'i', 'forever', 'always',
            'never', 'dream', 'night', 'day', 'baby', 'girl', 'boy'
        ];

        $lowerText = strtolower($text);

        foreach ($titleIndicators as $indicator) {
            if (str_contains($lowerText, $indicator)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect genre based on artist
     */
    public function detectGenre(?string $artist): ?string
    {
        if (!$artist) {
            return null;
        }

        // Direct match
        if (isset($this->genreMap[$artist])) {
            return $this->genreMap[$artist];
        }

        // Fuzzy match
        foreach ($this->genreMap as $knownArtist => $genre) {
            if (stripos($artist, $knownArtist) !== false || stripos($knownArtist, $artist) !== false) {
                return $genre;
            }
        }

        return null;
    }

    /**
     * Detect language based on title content
     */
    protected function detectLanguage(string $title): string
    {
        $lowerTitle = strtolower($title);

        // Check for Filipino/Tagalog keywords
        foreach ($this->languageKeywords['filipino'] as $keyword) {
            if (str_contains($lowerTitle, $keyword)) {
                return 'filipino';
            }
        }

        // Default to English
        return 'english';
    }

    /**
     * Clean and normalize title
     */
    public function cleanTitle(string $title): string
    {
        // Remove extra spaces
        $title = preg_replace('/\s+/', ' ', $title);

        // Capitalize properly
        $title = ucwords(strtolower($title));

        // Fix common abbreviations
        $title = str_replace("'S", "'s", $title);
        $title = str_replace("'T", "'t", $title);

        return trim($title);
    }

    /**
     * Clean and normalize artist name
     */
    public function cleanArtist(?string $artist): ?string
    {
        if (!$artist) {
            return null;
        }

        // Remove extra spaces
        $artist = preg_replace('/\s+/', ' ', $artist);

        // Keep original capitalization for artist names
        return trim($artist);
    }
}
