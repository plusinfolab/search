<?php

namespace PlusInfoLab\Search\Features;

use PlusInfoLab\Search\Results\SearchResultCollection;
use PlusInfoLab\Search\SearchQuery;

class RankingEngine
{
    /**
     * Ranking configuration.
     */
    protected array $config;

    /**
     * Create a new ranking engine instance.
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Rank search results.
     */
    public function rank(SearchResultCollection $results, SearchQuery $query): SearchResultCollection
    {
        if (! ($this->config['enabled'] ?? true)) {
            return $results;
        }

        return $results->map(function ($result) use ($query) {
            $score = $result->getScore();

            // Apply algorithm weight
            $algorithmWeights = $this->config['algorithm_weights'] ?? [];
            $algorithmWeight = $algorithmWeights[$result->getAlgorithm()] ?? 1;
            $score *= ($algorithmWeight / 100);

            // Apply field weights
            $fieldWeights = $query->getWeights();
            foreach ($result->getMatchedFields() as $field) {
                if (isset($fieldWeights[$field])) {
                    $score *= $fieldWeights[$field];
                }
            }

            // Apply recency boost
            if ($this->config['recency_boost']['enabled'] ?? false) {
                $score = $this->applyRecencyBoost($score, $result);
            }

            // Apply popularity boost
            if ($this->config['popularity_boost']['enabled'] ?? false) {
                $score = $this->applyPopularityBoost($score, $result);
            }

            $result->score = $score;

            return $result;
        })->sortByScore();
    }

    /**
     * Apply recency boost to score.
     */
    protected function applyRecencyBoost(float $score, $result): float
    {
        $field = $this->config['recency_boost']['field'] ?? 'created_at';
        $decayDays = $this->config['recency_boost']['decay_days'] ?? 30;
        $boostFactor = $this->config['recency_boost']['boost_factor'] ?? 1.5;

        $item = $result->getItem();

        if (is_object($item) && isset($item->$field)) {
            $date = $item->$field;

            if ($date instanceof \DateTimeInterface) {
                $daysSince = now()->diffInDays($date);
                $decayMultiplier = max(0, 1 - ($daysSince / $decayDays));
                $score *= (1 + ($decayMultiplier * $boostFactor));
            }
        }

        return $score;
    }

    /**
     * Apply popularity boost to score.
     */
    protected function applyPopularityBoost(float $score, $result): float
    {
        $field = $this->config['popularity_boost']['field'] ?? 'views_count';
        $boostFactor = $this->config['popularity_boost']['boost_factor'] ?? 0.1;

        $item = $result->getItem();

        if (is_object($item) && isset($item->$field)) {
            $popularity = (int) $item->$field;
            $score *= (1 + ($popularity * $boostFactor));
        }

        return $score;
    }
}
