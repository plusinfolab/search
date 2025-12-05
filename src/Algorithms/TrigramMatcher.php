<?php

namespace PlusInfoLab\Search\Algorithms;

class TrigramMatcher extends BaseAlgorithm
{
    /**
     * Get the algorithm name.
     */
    public function getName(): string
    {
        return 'trigram';
    }

    /**
     * Search using trigram similarity.
     */
    public function search(string $query, array $data, array $fields, array $options = []): array
    {
        $minSimilarity = $options['min_similarity'] ?? $this->config['min_similarity'] ?? 0.3;
        $normalizedQuery = $this->normalize($query);
        $queryTrigrams = $this->generateTrigrams($normalizedQuery);
        $matches = [];

        if (empty($queryTrigrams)) {
            return [];
        }

        foreach ($data as $index => $item) {
            $matchedFields = [];
            $maxSimilarity = 0;

            foreach ($fields as $field) {
                $value = $this->getFieldValue($item, $field);

                if ($value === null) {
                    continue;
                }

                $normalizedValue = $this->normalize($value);
                $valueTrigrams = $this->generateTrigrams($normalizedValue);

                if (empty($valueTrigrams)) {
                    continue;
                }

                // Calculate Jaccard similarity coefficient
                $similarity = $this->calculateSimilarity($queryTrigrams, $valueTrigrams);

                if ($similarity >= $minSimilarity) {
                    $matchedFields[] = $field;
                    $maxSimilarity = max($maxSimilarity, $similarity);
                }

                // Also check word-level trigram matching
                $words = explode(' ', $normalizedValue);
                foreach ($words as $word) {
                    if (mb_strlen($word) < 2) {
                        continue;
                    }

                    $wordTrigrams = $this->generateTrigrams($word);
                    $wordSimilarity = $this->calculateSimilarity($queryTrigrams, $wordTrigrams);

                    if ($wordSimilarity >= $minSimilarity) {
                        if (! in_array($field, $matchedFields)) {
                            $matchedFields[] = $field;
                        }
                        $maxSimilarity = max($maxSimilarity, $wordSimilarity);
                    }
                }
            }

            if (! empty($matchedFields)) {
                $score = (int) (35 * $maxSimilarity);

                $matches[] = [
                    'index' => $index,
                    'score' => $score,
                    'fields' => $matchedFields,
                    'metadata' => [
                        'match_type' => 'trigram',
                        'similarity' => $maxSimilarity,
                    ],
                ];
            }
        }

        return $matches;
    }

    /**
     * Generate trigrams from a string.
     */
    protected function generateTrigrams(string $text): array
    {
        $text = '  ' . $text . ' '; // Padding for edge trigrams
        $trigrams = [];
        $length = mb_strlen($text);

        for ($i = 0; $i < $length - 2; $i++) {
            $trigrams[] = mb_substr($text, $i, 3);
        }

        return array_unique($trigrams);
    }

    /**
     * Calculate Jaccard similarity coefficient.
     */
    protected function calculateSimilarity(array $trigrams1, array $trigrams2): float
    {
        $intersection = count(array_intersect($trigrams1, $trigrams2));
        $union = count(array_unique(array_merge($trigrams1, $trigrams2)));

        return $union > 0 ? $intersection / $union : 0;
    }
}
