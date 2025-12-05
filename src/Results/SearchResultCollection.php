<?php

namespace PlusInfoLab\Search\Results;

use Illuminate\Support\Collection;

class SearchResultCollection extends Collection
{
    /**
     * Sort results by score in descending order.
     */
    public function sortByScore(): self
    {
        return new static($this->sortByDesc(fn($result) => $result instanceof SearchResult ? $result->getScore() : 0)->values());
    }

    /**
     * Filter results by minimum score.
     */
    public function minScore(float $minScore): self
    {
        return new static($this->filter(fn($result) => $result instanceof SearchResult && $result->getScore() >= $minScore)->values());
    }

    /**
     * Filter results by algorithm.
     */
    public function byAlgorithm(string $algorithm): self
    {
        return new static($this->filter(fn($result) => $result instanceof SearchResult && $result->getAlgorithm() === $algorithm)->values());
    }

    /**
     * Get only the items from results.
     */
    public function items(): Collection
    {
        return $this->map(fn($result) => $result instanceof SearchResult ? $result->getItem() : $result);
    }

    /**
     * Get the highest score.
     */
    public function maxScore(): float
    {
        return $this->max(fn($result) => $result instanceof SearchResult ? $result->getScore() : 0) ?? 0.0;
    }

    /**
     * Get the lowest score.
     */
    public function minScoreValue(): float
    {
        return $this->min(fn($result) => $result instanceof SearchResult ? $result->getScore() : 0) ?? 0.0;
    }

    /**
     * Get the average score.
     */
    public function avgScore(): float
    {
        return $this->avg(fn($result) => $result instanceof SearchResult ? $result->getScore() : 0) ?? 0.0;
    }

    /**
     * Group results by algorithm.
     */
    public function groupByAlgorithm(): Collection
    {
        return $this->groupBy(fn($result) => $result instanceof SearchResult ? $result->getAlgorithm() : 'unknown');
    }

    /**
     * Convert all results to array.
     */
    public function toArray(): array
    {
        return $this->map(fn($result) => $result instanceof SearchResult ? $result->toArray() : $result)->all();
    }
}
