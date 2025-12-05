<?php

namespace PlusInfoLab\Search;

use Illuminate\Database\Eloquent\Builder;
use PlusInfoLab\Search\Results\SearchResult;
use PlusInfoLab\Search\Results\SearchResultCollection;

class SearchQuery
{
    /**
     * The search query string.
     */
    protected string $query = '';

    /**
     * The fields to search in.
     */
    protected array $fields = [];

    /**
     * Field weights for scoring.
     */
    protected array $weights = [];

    /**
     * The search algorithm to use.
     */
    protected ?string $algorithm = null;

    /**
     * Additional where clauses.
     */
    protected array $wheres = [];

    /**
     * Order by clauses.
     */
    protected array $orders = [];

    /**
     * Limit results.
     */
    protected ?int $limit = null;

    /**
     * Offset for pagination.
     */
    protected int $offset = 0;

    /**
     * Minimum score threshold.
     */
    protected float $minScore = 0.0;

    /**
     * Additional options.
     */
    protected array $options = [];

    /**
     * The Eloquent builder instance.
     */
    protected ?Builder $builder = null;

    /**
     * Set the search query.
     */
    public function query(string $query): self
    {
        $this->query = $query;

        return $this;
    }

    /**
     * Set the fields to search in.
     */
    public function in(array $fields): self
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * Set field weights.
     */
    public function weights(array $weights): self
    {
        $this->weights = $weights;

        return $this;
    }

    /**
     * Set the search algorithm.
     */
    public function using(string $algorithm): self
    {
        $this->algorithm = $algorithm;

        return $this;
    }

    /**
     * Add a where clause.
     */
    public function where(string $field, mixed $operator, mixed $value = null): self
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = compact('field', 'operator', 'value');

        return $this;
    }

    /**
     * Order results.
     */
    public function orderBy(string $field, string $direction = 'asc'): self
    {
        $this->orders[] = compact('field', 'direction');

        return $this;
    }

    /**
     * Limit results.
     */
    public function limit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Set offset for pagination.
     */
    public function offset(int $offset): self
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * Set minimum score.
     */
    public function minScore(float $score): self
    {
        $this->minScore = $score;

        return $this;
    }

    /**
     * Set additional options.
     */
    public function options(array $options): self
    {
        $this->options = array_merge($this->options, $options);

        return $this;
    }

    /**
     * Set the Eloquent builder.
     */
    public function setBuilder(Builder $builder): self
    {
        $this->builder = $builder;

        return $this;
    }

    /**
     * Get the query string.
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * Get the fields.
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Get the weights.
     */
    public function getWeights(): array
    {
        return $this->weights;
    }

    /**
     * Get the algorithm.
     */
    public function getAlgorithm(): ?string
    {
        return $this->algorithm;
    }

    /**
     * Get where clauses.
     */
    public function getWheres(): array
    {
        return $this->wheres;
    }

    /**
     * Get order clauses.
     */
    public function getOrders(): array
    {
        return $this->orders;
    }

    /**
     * Get the limit.
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }

    /**
     * Get the offset.
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * Get minimum score.
     */
    public function getMinScore(): float
    {
        return $this->minScore;
    }

    /**
     * Get options.
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Get the builder.
     */
    public function getBuilder(): ?Builder
    {
        return $this->builder;
    }
}
