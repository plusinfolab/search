<?php

namespace PlusInfoLab\Search\Contracts;

interface SearchAlgorithm
{
    /**
     * Search for matches using this algorithm.
     *
     * @param  string  $query  The search query
     * @param  array  $data  The data to search through
     * @param  array  $fields  The fields to search in
     * @param  array  $options  Algorithm-specific options
     * @return array Array of matches with scores
     */
    public function search(string $query, array $data, array $fields, array $options = []): array;

    /**
     * Get the algorithm name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Check if the algorithm is enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool;
}
