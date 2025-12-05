<?php

namespace PlusInfoLab\Search\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \PlusInfoLab\Search\SearchQuery query(string $query)
 * @method static \PlusInfoLab\Search\Results\SearchResultCollection search(\PlusInfoLab\Search\SearchQuery $searchQuery)
 * @method static array suggest(string $query, array $data = [], int $limit = 5)
 * @method static \PlusInfoLab\Search\Contracts\SearchAlgorithm|null getAlgorithm(string $name)
 *
 * @see \PlusInfoLab\Search\SearchEngine
 */
class Search extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'search';
    }
}
