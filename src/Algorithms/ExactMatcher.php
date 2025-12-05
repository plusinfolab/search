<?php

namespace PlusInfoLab\Search\Algorithms;

class ExactMatcher extends BaseAlgorithm
{
    /**
     * Get the algorithm name.
     */
    public function getName(): string
    {
        return 'exact';
    }

    /**
     * Search for exact matches.
     */
    public function search(string $query, array $data, array $fields, array $options = []): array
    {
        $caseSensitive = $options['case_sensitive'] ?? $this->config['case_sensitive'] ?? false;
        $normalizedQuery = $this->normalize($query, $caseSensitive);
        $matches = [];

        foreach ($data as $index => $item) {
            $matchedFields = [];
            $score = 0;

            foreach ($fields as $field) {
                $value = $this->getFieldValue($item, $field);

                if ($value === null) {
                    continue;
                }

                $normalizedValue = $this->normalize($value, $caseSensitive);

                if ($normalizedValue === $normalizedQuery) {
                    $matchedFields[] = $field;
                    $score = 100; // Perfect match
                }
            }

            if (! empty($matchedFields)) {
                $matches[] = [
                    'index' => $index,
                    'score' => $score,
                    'fields' => $matchedFields,
                    'metadata' => [
                        'match_type' => 'exact',
                    ],
                ];
            }
        }

        return $matches;
    }
}
