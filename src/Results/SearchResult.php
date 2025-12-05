<?php

namespace PlusInfoLab\Search\Results;

class SearchResult
{
    /**
     * The matched item.
     */
    public mixed $item;

    /**
     * The search score.
     */
    public float $score;

    /**
     * The matched fields.
     */
    public array $matchedFields = [];

    /**
     * Highlighted content.
     */
    public array $highlights = [];

    /**
     * The algorithm used for this match.
     */
    public string $algorithm;

    /**
     * Additional metadata.
     */
    public array $metadata = [];

    /**
     * Create a new search result instance.
     */
    public function __construct(
        mixed $item,
        float $score = 0.0,
        string $algorithm = 'unknown',
        array $matchedFields = [],
        array $highlights = [],
        array $metadata = []
    ) {
        $this->item = $item;
        $this->score = $score;
        $this->algorithm = $algorithm;
        $this->matchedFields = $matchedFields;
        $this->highlights = $highlights;
        $this->metadata = $metadata;
    }

    /**
     * Get the item.
     */
    public function getItem(): mixed
    {
        return $this->item;
    }

    /**
     * Get the score.
     */
    public function getScore(): float
    {
        return $this->score;
    }

    /**
     * Get matched fields.
     */
    public function getMatchedFields(): array
    {
        return $this->matchedFields;
    }

    /**
     * Get highlights.
     */
    public function getHighlights(): array
    {
        return $this->highlights;
    }

    /**
     * Get the algorithm name.
     */
    public function getAlgorithm(): string
    {
        return $this->algorithm;
    }

    /**
     * Get metadata.
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'item' => $this->item,
            'score' => $this->score,
            'algorithm' => $this->algorithm,
            'matched_fields' => $this->matchedFields,
            'highlights' => $this->highlights,
            'metadata' => $this->metadata,
        ];
    }
}
