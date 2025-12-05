<?php

namespace PlusInfoLab\Search\Algorithms;

class RegexMatcher extends BaseAlgorithm
{
    /**
     * Get the algorithm name.
     */
    public function getName(): string
    {
        return 'regex';
    }

    /**
     * Search using regular expressions.
     */
    public function search(string $query, array $data, array $fields, array $options = []): array
    {
        $timeout = $options['timeout'] ?? $this->config['timeout'] ?? 1000;
        $maxPatternLength = $options['max_pattern_length'] ?? $this->config['max_pattern_length'] ?? 500;
        $matches = [];

        // Validate pattern length
        if (mb_strlen($query) > $maxPatternLength) {
            return [];
        }

        // Validate regex pattern
        if (! $this->isValidRegex($query)) {
            return [];
        }

        foreach ($data as $index => $item) {
            $matchedFields = [];

            foreach ($fields as $field) {
                $value = $this->getFieldValue($item, $field);

                if ($value === null) {
                    continue;
                }

                // Set timeout for regex execution
                ini_set('pcre.backtrack_limit', '100000');
                ini_set('pcre.recursion_limit', '100000');

                try {
                    if (@preg_match($query, $value)) {
                        $matchedFields[] = $field;
                    }
                } catch (\Exception $e) {
                    // Skip on regex error
                    continue;
                }
            }

            if (! empty($matchedFields)) {
                $matches[] = [
                    'index' => $index,
                    'score' => 45,
                    'fields' => $matchedFields,
                    'metadata' => [
                        'match_type' => 'regex',
                        'pattern' => $query,
                    ],
                ];
            }
        }

        return $matches;
    }

    /**
     * Validate if string is a valid regex pattern.
     */
    protected function isValidRegex(string $pattern): bool
    {
        // Check if pattern has delimiters
        if (! preg_match('/^[\/\#\~\@\%\|\!].*[\/\#\~\@\%\|\!][imsxeADSUXJu]*$/', $pattern)) {
            return false;
        }

        // Test the pattern
        set_error_handler(function () {});
        $isValid = @preg_match($pattern, '') !== false;
        restore_error_handler();

        return $isValid;
    }
}
