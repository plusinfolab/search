<?php

namespace PlusInfoLab\Search\Traits;

use Illuminate\Database\Eloquent\Builder;
use PlusInfoLab\Search\Facades\Search;
use PlusInfoLab\Search\Results\SearchResultCollection;
use PlusInfoLab\Search\SearchQuery;

trait Searchable
{
    /**
     * Perform a search on this model.
     */
    public function scopeSearch(Builder $query, string $searchQuery, ?string $algorithm = null): Builder
    {
        $searchEngine = app(\PlusInfoLab\Search\SearchEngine::class);

        $searchQueryObj = (new SearchQuery())
            ->query($searchQuery)
            ->in($this->getSearchableFields())
            ->weights($this->getSearchWeights())
            ->setBuilder($query);

        if ($algorithm) {
            $searchQueryObj->using($algorithm);
        }

        $results = $searchEngine->search($searchQueryObj);

        // Get IDs from results
        $ids = $results->items()->pluck($this->getKeyName())->toArray();

        // Return query filtered by IDs in order
        if (empty($ids)) {
            return $query->whereRaw('1 = 0'); // Return empty result
        }

        // Use CASE WHEN for ordering (works on all databases)
        $cases = [];
        foreach ($ids as $index => $id) {
            $cases[] = "WHEN {$this->getKeyName()} = {$id} THEN {$index}";
        }
        $orderBy = 'CASE ' . implode(' ', $cases) . ' END';

        return $query->whereIn($this->getKeyName(), $ids)
            ->orderByRaw($orderBy);
    }

    /**
     * Search and return results directly.
     */
    public static function searchQuery(string $query, ?string $algorithm = null): SearchResultCollection
    {
        $instance = new static;
        $searchEngine = app(\PlusInfoLab\Search\SearchEngine::class);

        $searchQueryObj = (new SearchQuery())
            ->query($query)
            ->in($instance->getSearchableFields())
            ->weights($instance->getSearchWeights())
            ->setBuilder($instance->newQuery());

        if ($algorithm) {
            $searchQueryObj->using($algorithm);
        }

        return $searchEngine->search($searchQueryObj);
    }

    /**
     * Get searchable fields.
     */
    public function getSearchableFields(): array
    {
        return property_exists($this, 'searchable') ? $this->searchable : [];
    }

    /**
     * Get search weights for fields.
     */
    public function getSearchWeights(): array
    {
        return property_exists($this, 'searchWeights') ? $this->searchWeights : [];
    }

    /**
     * Check if this model is searchable.
     */
    public function isSearchable(): bool
    {
        return ! empty($this->getSearchableFields());
    }

    /**
     * Get the search index name.
     */
    public function getSearchIndexName(): string
    {
        return property_exists($this, 'searchIndexName')
            ? $this->searchIndexName
            : $this->getTable();
    }

    /**
     * Make this model searchable (for manual indexing).
     */
    public function searchable(): void
    {
        // Hook for FTS indexing
        if (config('search.fts.enabled') && config('search.fts.auto_sync')) {
            $indexer = app(\PlusInfoLab\Search\FTS\FTSIndexer::class);
            $indexer->index($this);
        }
    }

    /**
     * Remove this model from search index.
     */
    public function unsearchable(): void
    {
        // Hook for FTS indexing
        if (config('search.fts.enabled') && config('search.fts.auto_sync')) {
            $indexer = app(\PlusInfoLab\Search\FTS\FTSIndexer::class);
            $indexer->remove($this);
        }
    }
}
