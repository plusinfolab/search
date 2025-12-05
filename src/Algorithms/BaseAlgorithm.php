<?php

namespace PlusInfoLab\Search\Algorithms;

use PlusInfoLab\Search\Contracts\SearchAlgorithm;

abstract class BaseAlgorithm implements SearchAlgorithm
{
    /**
     * Algorithm configuration.
     */
    protected array $config;

    /**
     * Create a new algorithm instance.
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Check if the algorithm is enabled.
     */
    public function isEnabled(): bool
    {
        return $this->config['enabled'] ?? true;
    }

    /**
     * Extract field value from data.
     */
    protected function getFieldValue(array $data, string $field): ?string
    {
        return data_get($data, $field);
    }

    /**
     * Normalize string for comparison.
     */
    protected function normalize(string $value, bool $caseSensitive = false): string
    {
        $value = trim($value);

        return $caseSensitive ? $value : mb_strtolower($value);
    }
}
