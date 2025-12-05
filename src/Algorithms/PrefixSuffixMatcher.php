<?php

namespace PlusInfoLab\Search\Algorithms;

class PrefixSuffixMatcher extends BaseAlgorithm
{
    /**
     * Get the algorithm name.
     */
    public function getName(): string
    {
        return $this->config['type'] ?? 'prefix';
    }

    /**
     * Search for prefix or suffix matches.
     */
    public function search(string $query, array $data, array $fields, array $options = []): array
    {
        $type = $options['type'] ?? $this->config['type'] ?? 'prefix';
        $minLength = $options['min_length'] ?? $this->config['min_length'] ?? 2;
        $caseSensitive = $options['case_sensitive'] ?? false;
        $normalizedQuery = $this->normalize($query, $caseSensitive);
        $matches = [];

        if (mb_strlen($normalizedQuery) < $minLength) {
            return [];
        }

        foreach ($data as $index => $item) {
            $matchedFields = [];
            $score = 0;

            foreach ($fields as $field) {
                $value = $this->getFieldValue($item, $field);

                if ($value === null) {
                    continue;
                }

                $normalizedValue = $this->normalize($value, $caseSensitive);
                $isMatch = false;

                if ($type === 'prefix') {
                    $isMatch = str_starts_with($normalizedValue, $normalizedQuery);
                } elseif ($type === 'suffix') {
                    $isMatch = str_ends_with($normalizedValue, $normalizedQuery);
                }

                if ($isMatch) {
                    $matchedFields[] = $field;

                    // Score based on length ratio
                    $queryLength = mb_strlen($normalizedQuery);
                    $valueLength = mb_strlen($normalizedValue);
                    $score = (int) (($type === 'prefix' ? 80 : 70) * ($queryLength / $valueLength));
                }
            }

            if (! empty($matchedFields)) {
                $matches[] = [
                    'index' => $index,
                    'score' => $score,
                    'fields' => $matchedFields,
                    'metadata' => [
                        'match_type' => $type,
                    ],
                ];
            }
        }

        return $matches;
    }
}
