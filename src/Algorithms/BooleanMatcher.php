<?php

namespace PlusInfoLab\Search\Algorithms;

class BooleanMatcher extends BaseAlgorithm
{
    /**
     * Get the algorithm name.
     */
    public function getName(): string
    {
        return 'boolean';
    }

    /**
     * Search using boolean operators (AND, OR, NOT).
     */
    public function search(string $query, array $data, array $fields, array $options = []): array
    {
        $operators = $options['operators'] ?? $this->config['operators'] ?? ['AND', 'OR', 'NOT'];
        $allowGrouping = $options['allow_grouping'] ?? $this->config['allow_grouping'] ?? true;
        $matches = [];

        // Parse the boolean query
        $parsedQuery = $this->parseQuery($query);

        foreach ($data as $index => $item) {
            $matchedFields = [];
            $isMatch = false;

            foreach ($fields as $field) {
                $value = $this->getFieldValue($item, $field);

                if ($value === null) {
                    continue;
                }

                $normalizedValue = $this->normalize($value);

                if ($this->evaluateQuery($parsedQuery, $normalizedValue)) {
                    $matchedFields[] = $field;
                    $isMatch = true;
                }
            }

            if ($isMatch) {
                $matches[] = [
                    'index' => $index,
                    'score' => 50,
                    'fields' => $matchedFields,
                    'metadata' => [
                        'match_type' => 'boolean',
                        'query' => $query,
                    ],
                ];
            }
        }

        return $matches;
    }

    /**
     * Parse boolean query into structured format.
     */
    protected function parseQuery(string $query): array
    {
        // Simple parser for boolean queries
        // Supports: term1 AND term2, term1 OR term2, NOT term1, "exact phrase"
        $query = trim($query);
        $tokens = [];

        // Extract quoted phrases first
        preg_match_all('/"([^"]+)"/', $query, $phrases);
        $phraseMap = [];

        foreach ($phrases[1] as $i => $phrase) {
            $placeholder = '__PHRASE_' . $i . '__';
            $phraseMap[$placeholder] = $phrase;
            $query = str_replace('"' . $phrase . '"', $placeholder, $query);
        }

        // Split by operators while preserving them
        $parts = preg_split('/\s+(AND|OR|NOT)\s+/i', $query, -1, PREG_SPLIT_DELIM_CAPTURE);

        $result = [
            'operator' => 'AND', // Default operator
            'terms' => [],
            'not_terms' => [],
        ];

        $currentOperator = 'AND';
        $nextIsNot = false;

        foreach ($parts as $part) {
            $part = trim($part);

            if (empty($part)) {
                continue;
            }

            $upperPart = strtoupper($part);

            if (in_array($upperPart, ['AND', 'OR', 'NOT'])) {
                if ($upperPart === 'NOT') {
                    $nextIsNot = true;
                } else {
                    $currentOperator = $upperPart;
                }

                continue;
            }

            // Replace phrase placeholders
            foreach ($phraseMap as $placeholder => $phrase) {
                $part = str_replace($placeholder, $phrase, $part);
            }

            if ($nextIsNot) {
                $result['not_terms'][] = mb_strtolower($part);
                $nextIsNot = false;
            } else {
                $result['terms'][] = [
                    'value' => mb_strtolower($part),
                    'operator' => $currentOperator,
                ];
            }
        }

        return $result;
    }

    /**
     * Evaluate parsed query against value.
     */
    protected function evaluateQuery(array $parsedQuery, string $value): bool
    {
        $value = mb_strtolower($value);

        // Check NOT terms first
        foreach ($parsedQuery['not_terms'] as $notTerm) {
            if (str_contains($value, $notTerm)) {
                return false;
            }
        }

        // Evaluate terms with operators
        $hasAndMatch = true;
        $hasOrMatch = false;

        foreach ($parsedQuery['terms'] as $term) {
            $termValue = $term['value'];
            $operator = $term['operator'];
            $matches = str_contains($value, $termValue);

            if ($operator === 'AND') {
                $hasAndMatch = $hasAndMatch && $matches;
            } elseif ($operator === 'OR') {
                $hasOrMatch = $hasOrMatch || $matches;
            }
        }

        // If we have OR terms, at least one must match
        // If we only have AND terms, all must match
        $hasOrTerms = false;
        foreach ($parsedQuery['terms'] as $term) {
            if ($term['operator'] === 'OR') {
                $hasOrTerms = true;
                break;
            }
        }

        return $hasOrTerms ? $hasOrMatch : $hasAndMatch;
    }
}
