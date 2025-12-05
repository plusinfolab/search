<?php

namespace PlusInfoLab\Search\FTS;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use PlusInfoLab\Search\Results\SearchResult;
use PlusInfoLab\Search\Results\SearchResultCollection;

class FTSSearchAdapter
{
    /**
     * FTS indexer instance.
     */
    protected FTSIndexer $indexer;

    /**
     * Create a new FTS search adapter instance.
     */
    public function __construct(FTSIndexer $indexer = null)
    {
        $this->indexer = $indexer ?: app(FTSIndexer::class);
    }

    /**
     * Search using FTS.
     */
    public function search(Builder $builder, string $query): SearchResultCollection
    {
        $model = $builder->getModel();
        $ids = $this->indexer->search($model, $query);

        if (empty($ids)) {
            return new SearchResultCollection();
        }

        // Get models by IDs
        $models = $builder->whereIn($model->getKeyName(), $ids)->get();

        // Create search results
        $results = new SearchResultCollection();

        foreach ($models as $model) {
            $results->push(new SearchResult(
                item: $model,
                score: 50, // FTS doesn't provide scores in the same way
                algorithm: 'fts',
                matchedFields: $model->getSearchableFields(),
                highlights: [],
                metadata: ['match_type' => 'fts']
            ));
        }

        return $results;
    }

    /**
     * Index a model.
     */
    public function index(Model $model): void
    {
        $this->indexer->index($model);
    }

    /**
     * Remove a model from index.
     */
    public function remove(Model $model): void
    {
        $this->indexer->remove($model);
    }

    /**
     * Bulk index models.
     */
    public function bulkIndex(iterable $models): void
    {
        foreach ($models as $model) {
            $this->index($model);
        }
    }
}
