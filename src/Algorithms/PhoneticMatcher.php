<?php

namespace PlusInfoLab\Search\Algorithms;

class PhoneticMatcher extends BaseAlgorithm
{
    /**
     * Get the algorithm name.
     */
    public function getName(): string
    {
        return 'phonetic';
    }

    /**
     * Search using phonetic algorithms (Soundex/Metaphone).
     */
    public function search(string $query, array $data, array $fields, array $options = []): array
    {
        $algorithm = $options['algorithm'] ?? $this->config['algorithm'] ?? 'metaphone';
        $normalizedQuery = $this->normalize($query);
        $matches = [];

        // Generate phonetic code for query
        $queryCode = $this->generatePhoneticCode($normalizedQuery, $algorithm);

        if (empty($queryCode)) {
            return [];
        }

        foreach ($data as $index => $item) {
            $matchedFields = [];

            foreach ($fields as $field) {
                $value = $this->getFieldValue($item, $field);

                if ($value === null) {
                    continue;
                }

                $normalizedValue = $this->normalize($value);

                // Check full value
                $valueCode = $this->generatePhoneticCode($normalizedValue, $algorithm);

                if ($valueCode === $queryCode) {
                    $matchedFields[] = $field;
                } else {
                    // Check individual words
                    $words = explode(' ', $normalizedValue);
                    foreach ($words as $word) {
                        if (mb_strlen($word) < 2) {
                            continue;
                        }

                        $wordCode = $this->generatePhoneticCode($word, $algorithm);

                        if ($wordCode === $queryCode) {
                            $matchedFields[] = $field;
                            break;
                        }
                    }
                }
            }

            if (! empty($matchedFields)) {
                $matches[] = [
                    'index' => $index,
                    'score' => 30,
                    'fields' => array_unique($matchedFields),
                    'metadata' => [
                        'match_type' => 'phonetic',
                        'algorithm' => $algorithm,
                        'query_code' => $queryCode,
                    ],
                ];
            }
        }

        return $matches;
    }

    /**
     * Generate phonetic code using specified algorithm.
     */
    protected function generatePhoneticCode(string $text, string $algorithm): string
    {
        $text = trim($text);

        if (empty($text)) {
            return '';
        }

        return match ($algorithm) {
            'soundex' => soundex($text),
            'metaphone' => metaphone($text),
            default => metaphone($text),
        };
    }
}
