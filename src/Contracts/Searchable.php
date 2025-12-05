<?php

namespace PlusInfoLab\Search\Contracts;

interface Searchable
{
    /**
     * Get the searchable fields for this model.
     *
     * @return array
     */
    public function getSearchableFields(): array;

    /**
     * Get the search weights for fields.
     *
     * @return array
     */
    public function getSearchWeights(): array;

    /**
     * Check if this model is searchable.
     *
     * @return bool
     */
    public function isSearchable(): bool;

    /**
     * Get the search index name for this model.
     *
     * @return string
     */
    public function getSearchIndexName(): string;
}
