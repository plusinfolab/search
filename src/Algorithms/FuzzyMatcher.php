<?php

namespace PlusInfoLab\Search\Algorithms;

class FuzzyMatcher extends BaseAlgorithm
{
    /**
     * Get the algorithm name.
     */
    public function getName(): string
    {
        return 'fuzzy';
    }

    /**
     * Search using Levenshtein distance for fuzzy matching.
     */
    public function search(string $query, array $data, array $fields, array $options = []): array
    {
        $threshold = $options['threshold'] ?? $this->config['threshold'] ?? 2;
        $maxLength = $options['max_length'] ?? $this->config['max_length'] ?? 255;
        $normalizedQuery = $this->normalize($query);
        $matches = [];

        // Skip if query is too long for performance
        if (mb_strlen($normalizedQuery) > $maxLength) {
            return [];
        }

        foreach ($data as $index => $item) {
            $matchedFields = [];
            $minDistance = PHP_INT_MAX;

            foreach ($fields as $field) {
                $value = $this->getFieldValue($item, $field);

                if ($value === null) {
                    continue;
                }

                $normalizedValue = $this->normalize($value);

                // Skip if value is too long
                if (mb_strlen($normalizedValue) > $maxLength) {
                    continue;
                }

                // Calculate Levenshtein distance
                $distance = levenshtein($normalizedQuery, $normalizedValue);

                if ($distance <= $threshold) {
                    $matchedFields[] = $field;
                    $minDistance = min($minDistance, $distance);
                }

                // Also check for word-level fuzzy matching
                $words = explode(' ', $normalizedValue);
                foreach ($words as $word) {
                    if (mb_strlen($word) < 2) {
                        continue;
                    }

                    $wordDistance = levenshtein($normalizedQuery, $word);
                    if ($wordDistance <= $threshold) {
                        if (! in_array($field, $matchedFields)) {
                            $matchedFields[] = $field;
                        }
                        $minDistance = min($minDistance, $wordDistance);
                    }
                }
            }

            if (! empty($matchedFields)) {
                // Score: higher score for lower distance
                $score = max(0, (int) (40 * (1 - $minDistance / max($threshold, 1))));

                $matches[] = [
                    'index' => $index,
                    'score' => $score,
                    'fields' => $matchedFields,
                    'metadata' => [
                        'match_type' => 'fuzzy',
                        'distance' => $minDistance,
                    ],
                ];
            }
        }

        return $matches;
    }
}
