<?php

namespace PlusInfoLab\Search;

use Illuminate\Support\Facades\Cache;
use PlusInfoLab\Search\Algorithms\BooleanMatcher;
use PlusInfoLab\Search\Algorithms\ExactMatcher;
use PlusInfoLab\Search\Algorithms\FuzzyMatcher;
use PlusInfoLab\Search\Algorithms\PartialMatcher;
use PlusInfoLab\Search\Algorithms\PhoneticMatcher;
use PlusInfoLab\Search\Algorithms\PrefixSuffixMatcher;
use PlusInfoLab\Search\Algorithms\RegexMatcher;
use PlusInfoLab\Search\Algorithms\TrigramMatcher;
use PlusInfoLab\Search\Contracts\SearchAlgorithm;
use PlusInfoLab\Search\Features\RankingEngine;
use PlusInfoLab\Search\Features\SearchHighlighter;
use PlusInfoLab\Search\Features\SearchSuggester;
use PlusInfoLab\Search\Features\SynonymExpander;
use PlusInfoLab\Search\Results\SearchResult;
use PlusInfoLab\Search\Results\SearchResultCollection;

class SearchEngine
{
    /**
     * Configuration array.
     */
    protected array $config;

    /**
     * Registered search algorithms.
     */
    protected array $algorithms = [];

    /**
     * Ranking engine instance.
     */
    protected RankingEngine $rankingEngine;

    /**
     * Highlighter instance.
     */
    protected SearchHighlighter $highlighter;

    /**
     * Suggester instance.
     */
    protected SearchSuggester $suggester;

    /**
     * Synonym expander instance.
     */
    protected SynonymExpander $synonymExpander;

    /**
     * Create a new search engine instance.
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->registerAlgorithms();
        $this->rankingEngine = new RankingEngine($config['ranking'] ?? []);
        $this->highlighter = new SearchHighlighter($config['highlighting'] ?? []);
        $this->suggester = new SearchSuggester($config['suggestions'] ?? []);
        $this->synonymExpander = new SynonymExpander($config['synonyms'] ?? []);
    }

    /**
     * Register all search algorithms.
     */
    protected function registerAlgorithms(): void
    {
        $this->algorithms = [
            'exact' => new ExactMatcher($this->config['algorithms']['exact'] ?? []),
            'partial' => new PartialMatcher($this->config['algorithms']['partial'] ?? []),
            'fuzzy' => new FuzzyMatcher($this->config['algorithms']['fuzzy'] ?? []),
            'trigram' => new TrigramMatcher($this->config['algorithms']['trigram'] ?? []),
            'boolean' => new BooleanMatcher($this->config['algorithms']['boolean'] ?? []),
            'regex' => new RegexMatcher($this->config['algorithms']['regex'] ?? []),
            'phonetic' => new PhoneticMatcher($this->config['algorithms']['phonetic'] ?? []),
            'prefix' => new PrefixSuffixMatcher(array_merge($this->config['algorithms']['prefix'] ?? [], ['type' => 'prefix'])),
            'suffix' => new PrefixSuffixMatcher(array_merge($this->config['algorithms']['suffix'] ?? [], ['type' => 'suffix'])),
        ];
    }

    /**
     * Create a new search query.
     */
    public function query(string $query): SearchQuery
    {
        return (new SearchQuery())->query($query);
    }

    /**
     * Execute a search query.
     */
    public function search(SearchQuery $searchQuery): SearchResultCollection
    {
        $cacheKey = $this->getCacheKey($searchQuery);

        if ($this->isCacheEnabled() && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // Expand synonyms if enabled
        $query = $searchQuery->getQuery();
        if ($this->config['synonyms']['enabled'] ?? false) {
            $query = $this->synonymExpander->expand($query);
        }

        // Get the algorithm
        $algorithmName = $searchQuery->getAlgorithm() ?? $this->config['default_algorithm'] ?? 'partial';
        $algorithm = $this->algorithms[$algorithmName] ?? $this->algorithms['partial'];

        // Perform the search
        $results = new SearchResultCollection();

        // Execute search based on whether we have an Eloquent builder
        if ($builder = $searchQuery->getBuilder()) {
            $results = $this->searchEloquent($builder, $query, $searchQuery, $algorithm);
        }

        // Apply ranking
        if ($this->config['ranking']['enabled'] ?? true) {
            $results = $this->rankingEngine->rank($results, $searchQuery);
        }

        // Apply highlighting
        if ($this->config['highlighting']['enabled'] ?? true) {
            $results = $this->highlighter->highlight($results, $query);
        }

        // Filter by minimum score
        if ($minScore = $searchQuery->getMinScore()) {
            $results = $results->minScore($minScore);
        }

        // Apply limit and offset
        if ($offset = $searchQuery->getOffset()) {
            $results = $results->slice($offset);
        }

        if ($limit = $searchQuery->getLimit()) {
            $results = $results->take($limit);
        }

        // Cache results
        if ($this->isCacheEnabled()) {
            Cache::put($cacheKey, $results, $this->config['cache']['ttl'] ?? 3600);
        }

        return $results;
    }

    /**
     * Search using Eloquent builder.
     */
    protected function searchEloquent($builder, string $query, SearchQuery $searchQuery, SearchAlgorithm $algorithm): SearchResultCollection
    {
        $fields = $searchQuery->getFields();
        $weights = $searchQuery->getWeights();
        $results = new SearchResultCollection();

        // Apply where clauses
        foreach ($searchQuery->getWheres() as $where) {
            $builder->where($where['field'], $where['operator'], $where['value']);
        }

        // Get all records
        $records = $builder->get();

        // Search through records
        foreach ($records as $record) {
            $data = $record->toArray();
            $matches = $algorithm->search($query, [$data], $fields, $searchQuery->getOptions());

            foreach ($matches as $match) {
                $score = $match['score'] ?? 0;

                // Apply field weights
                if (isset($match['field']) && isset($weights[$match['field']])) {
                    $score *= $weights[$match['field']];
                }

                $results->push(new SearchResult(
                    item: $record,
                    score: $score,
                    algorithm: $algorithm->getName(),
                    matchedFields: $match['fields'] ?? [],
                    highlights: [],
                    metadata: $match['metadata'] ?? []
                ));
            }
        }

        return $results->sortByScore();
    }

    /**
     * Get search suggestions.
     */
    public function suggest(string $query, array $data = [], int $limit = 5): array
    {
        return $this->suggester->suggest($query, $data, $limit);
    }

    /**
     * Get a specific algorithm.
     */
    public function getAlgorithm(string $name): ?SearchAlgorithm
    {
        return $this->algorithms[$name] ?? null;
    }

    /**
     * Check if caching is enabled.
     */
    protected function isCacheEnabled(): bool
    {
        return $this->config['cache']['enabled'] ?? false;
    }

    /**
     * Generate cache key for search query.
     */
    protected function getCacheKey(SearchQuery $searchQuery): string
    {
        $prefix = $this->config['cache']['prefix'] ?? 'search:';

        return $prefix . md5(serialize([
            'query' => $searchQuery->getQuery(),
            'fields' => $searchQuery->getFields(),
            'algorithm' => $searchQuery->getAlgorithm(),
            'wheres' => $searchQuery->getWheres(),
            'options' => $searchQuery->getOptions(),
        ]));
    }
}
