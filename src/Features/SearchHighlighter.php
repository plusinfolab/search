<?php

namespace PlusInfoLab\Search\Features;

use PlusInfoLab\Search\Results\SearchResultCollection;

class SearchHighlighter
{
    /**
     * Highlighting configuration.
     */
    protected array $config;

    /**
     * Create a new highlighter instance.
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Highlight matched terms in search results.
     */
    public function highlight(SearchResultCollection $results, string $query): SearchResultCollection
    {
        if (! ($this->config['enabled'] ?? true)) {
            return $results;
        }

        $prefix = $this->config['prefix'] ?? '<mark>';
        $suffix = $this->config['suffix'] ?? '</mark>';
        $maxFragments = $this->config['max_fragments'] ?? 3;
        $fragmentSize = $this->config['fragment_size'] ?? 150;

        return $results->map(function ($result) use ($query, $prefix, $suffix, $maxFragments, $fragmentSize) {
            $item = $result->getItem();
            $highlights = [];

            foreach ($result->getMatchedFields() as $field) {
                $value = is_object($item) ? ($item->$field ?? null) : ($item[$field] ?? null);

                if ($value === null) {
                    continue;
                }

                $highlighted = $this->highlightText($value, $query, $prefix, $suffix);
                $fragments = $this->extractFragments($highlighted, $prefix, $fragmentSize, $maxFragments);

                if (! empty($fragments)) {
                    $highlights[$field] = $fragments;
                }
            }

            $result->highlights = $highlights;

            return $result;
        });
    }

    /**
     * Highlight text with matched terms.
     */
    protected function highlightText(string $text, string $query, string $prefix, string $suffix): string
    {
        // Extract individual terms from query (handle boolean operators)
        $terms = $this->extractTerms($query);

        foreach ($terms as $term) {
            if (mb_strlen($term) < 2) {
                continue;
            }

            // Case-insensitive highlighting
            $pattern = '/(' . preg_quote($term, '/') . ')/iu';
            $text = preg_replace($pattern, $prefix . '$1' . $suffix, $text);
        }

        return $text;
    }

    /**
     * Extract search terms from query.
     */
    protected function extractTerms(string $query): array
    {
        // Remove boolean operators
        $query = preg_replace('/\b(AND|OR|NOT)\b/i', ' ', $query);

        // Extract quoted phrases
        preg_match_all('/"([^"]+)"/', $query, $phrases);
        $terms = $phrases[1] ?? [];

        // Remove quotes from query
        $query = preg_replace('/"[^"]+"/', '', $query);

        // Split remaining words
        $words = preg_split('/\s+/', trim($query), -1, PREG_SPLIT_NO_EMPTY);

        return array_merge($terms, $words);
    }

    /**
     * Extract fragments containing highlights.
     */
    protected function extractFragments(string $text, string $prefix, int $fragmentSize, int $maxFragments): array
    {
        $fragments = [];
        $positions = [];

        // Find all highlight positions
        $offset = 0;
        while (($pos = mb_strpos($text, $prefix, $offset)) !== false) {
            $positions[] = $pos;
            $offset = $pos + 1;
        }

        if (empty($positions)) {
            return [];
        }

        // Extract fragments around highlights
        foreach (array_slice($positions, 0, $maxFragments) as $pos) {
            $start = max(0, $pos - (int) ($fragmentSize / 2));
            $fragment = mb_substr($text, $start, $fragmentSize);

            // Add ellipsis if needed
            if ($start > 0) {
                $fragment = '...' . $fragment;
            }

            if ($start + $fragmentSize < mb_strlen($text)) {
                $fragment .= '...';
            }

            $fragments[] = $fragment;
        }

        return $fragments;
    }
}
