<?php

namespace PlusInfoLab\Search\Algorithms;

class PartialMatcher extends BaseAlgorithm
{
    /**
     * Get the algorithm name.
     */
    public function getName(): string
    {
        return 'partial';
    }

    /**
     * Search for partial matches (substring).
     */
    public function search(string $query, array $data, array $fields, array $options = []): array
    {
        $caseSensitive = $options['case_sensitive'] ?? $this->config['case_sensitive'] ?? false;
        $minLength = $options['min_length'] ?? $this->config['min_length'] ?? 2;
        $normalizedQuery = $this->normalize($query, $caseSensitive);
        $matches = [];

        if (mb_strlen($normalizedQuery) < $minLength) {
            return [];
        }

        foreach ($data as $index => $item) {
            $matchedFields = [];
            $maxScore = 0;

            foreach ($fields as $field) {
                $value = $this->getFieldValue($item, $field);

                if ($value === null) {
                    continue;
                }

                $normalizedValue = $this->normalize($value, $caseSensitive);

                if (str_contains($normalizedValue, $normalizedQuery)) {
                    $matchedFields[] = $field;

                    // Calculate score based on match position and length
                    $position = mb_strpos($normalizedValue, $normalizedQuery);
                    $valueLength = mb_strlen($normalizedValue);
                    $queryLength = mb_strlen($normalizedQuery);

                    // Score: 100 for exact match, decreasing based on position and length ratio
                    $lengthRatio = $queryLength / $valueLength;
                    $positionPenalty = $position / max($valueLength, 1);
                    $score = (int) (60 * $lengthRatio * (1 - $positionPenalty * 0.5));

                    $maxScore = max($maxScore, $score);
                }
            }

            if (! empty($matchedFields)) {
                $matches[] = [
                    'index' => $index,
                    'score' => $maxScore,
                    'fields' => $matchedFields,
                    'metadata' => [
                        'match_type' => 'partial',
                    ],
                ];
            }
        }

        return $matches;
    }
}
