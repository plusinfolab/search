<?php

namespace PlusInfoLab\Search\Features;

class SearchSuggester
{
    /**
     * Suggester configuration.
     */
    protected array $config;

    /**
     * Create a new suggester instance.
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Generate search suggestions.
     */
    public function suggest(string $query, array $data = [], int $limit = 5): array
    {
        if (! ($this->config['enabled'] ?? true)) {
            return [];
        }

        $maxSuggestions = $limit ?: ($this->config['max_suggestions'] ?? 5);
        $minScore = $this->config['min_score'] ?? 0.5;
        $suggestions = [];

        // Generate suggestions based on data
        foreach ($data as $item) {
            $text = is_string($item) ? $item : (is_array($item) ? implode(' ', $item) : '');

            if (empty($text)) {
                continue;
            }

            $score = $this->calculateSuggestionScore($query, $text);

            if ($score >= $minScore) {
                $suggestions[] = [
                    'text' => $text,
                    'score' => $score,
                ];
            }
        }

        // Sort by score and limit
        usort($suggestions, fn($a, $b) => $b['score'] <=> $a['score']);

        return array_slice(array_column($suggestions, 'text'), 0, $maxSuggestions);
    }

    /**
     * Calculate suggestion score.
     */
    protected function calculateSuggestionScore(string $query, string $suggestion): float
    {
        $query = mb_strtolower(trim($query));
        $suggestion = mb_strtolower(trim($suggestion));

        // Exact match
        if ($query === $suggestion) {
            return 1.0;
        }

        // Prefix match
        if (str_starts_with($suggestion, $query)) {
            return 0.9;
        }

        // Contains match
        if (str_contains($suggestion, $query)) {
            return 0.7;
        }

        // Levenshtein distance
        $distance = levenshtein($query, $suggestion);
        $maxLength = max(mb_strlen($query), mb_strlen($suggestion));

        if ($maxLength === 0) {
            return 0;
        }

        $similarity = 1 - ($distance / $maxLength);

        return max(0, $similarity);
    }

    /**
     * Generate "did you mean" suggestion.
     */
    public function didYouMean(string $query, array $dictionary): ?string
    {
        $query = mb_strtolower(trim($query));
        $bestMatch = null;
        $bestScore = 0;

        foreach ($dictionary as $word) {
            $word = mb_strtolower(trim($word));
            $score = $this->calculateSuggestionScore($query, $word);

            if ($score > $bestScore && $score >= 0.7) {
                $bestScore = $score;
                $bestMatch = $word;
            }
        }

        return $bestMatch;
    }
}
