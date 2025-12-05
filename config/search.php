<?php

// config for PlusInfoLab/Search
return [

    /*
    |--------------------------------------------------------------------------
    | Default Search Algorithm
    |--------------------------------------------------------------------------
    |
    | This option controls the default search algorithm used when no specific
    | algorithm is specified. Available options: exact, partial, fuzzy,
    | trigram, boolean, regex, phonetic, prefix, suffix
    |
    */

    'default_algorithm' => env('SEARCH_DEFAULT_ALGORITHM', 'partial'),

    /*
    |--------------------------------------------------------------------------
    | Search Algorithms Configuration
    |--------------------------------------------------------------------------
    |
    | Configure individual search algorithms with their specific settings.
    |
    */

    'algorithms' => [
        'exact' => [
            'enabled' => true,
            'case_sensitive' => false,
        ],

        'partial' => [
            'enabled' => true,
            'min_length' => 2,
            'case_sensitive' => false,
        ],

        'fuzzy' => [
            'enabled' => true,
            'threshold' => 2, // Maximum Levenshtein distance
            'max_length' => 255, // Maximum string length for performance
        ],

        'trigram' => [
            'enabled' => true,
            'min_similarity' => 0.3, // Minimum similarity coefficient (0-1)
        ],

        'boolean' => [
            'enabled' => true,
            'operators' => ['AND', 'OR', 'NOT'],
            'allow_grouping' => true,
        ],

        'regex' => [
            'enabled' => true,
            'timeout' => 1000, // Milliseconds
            'max_pattern_length' => 500,
        ],

        'phonetic' => [
            'enabled' => true,
            'algorithm' => 'metaphone', // soundex or metaphone
        ],

        'prefix' => [
            'enabled' => true,
            'min_length' => 2,
        ],

        'suffix' => [
            'enabled' => true,
            'min_length' => 2,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Ranking & Scoring
    |--------------------------------------------------------------------------
    |
    | Configure how search results are ranked and scored.
    |
    */

    'ranking' => [
        'enabled' => true,

        // Algorithm weights (higher = better match)
        'algorithm_weights' => [
            'exact' => 100,
            'prefix' => 80,
            'suffix' => 70,
            'partial' => 60,
            'fuzzy' => 40,
            'trigram' => 35,
            'phonetic' => 30,
            'boolean' => 50,
            'regex' => 45,
        ],

        // Field weights (multiply score by field weight)
        'default_field_weight' => 1,

        // Boost recent results
        'recency_boost' => [
            'enabled' => false,
            'field' => 'created_at',
            'decay_days' => 30,
            'boost_factor' => 1.5,
        ],

        // Boost popular results
        'popularity_boost' => [
            'enabled' => false,
            'field' => 'views_count',
            'boost_factor' => 0.1,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Synonym Expansion
    |--------------------------------------------------------------------------
    |
    | Enable synonym expansion to improve search results.
    |
    */

    'synonyms' => [
        'enabled' => false,

        // Synonym dictionary
        'dictionary' => [
            'car' => ['automobile', 'vehicle'],
            'phone' => ['mobile', 'smartphone', 'cellphone'],
            'laptop' => ['notebook', 'computer'],
            // Add more synonyms as needed
        ],

        // Load synonyms from file
        'file' => null, // Path to JSON file with synonyms
    ],

    /*
    |--------------------------------------------------------------------------
    | Search Highlighting
    |--------------------------------------------------------------------------
    |
    | Configure how matched terms are highlighted in results.
    |
    */

    'highlighting' => [
        'enabled' => true,
        'prefix' => '<mark>',
        'suffix' => '</mark>',
        'max_fragments' => 3,
        'fragment_size' => 150,
    ],

    /*
    |--------------------------------------------------------------------------
    | Search Suggestions
    |--------------------------------------------------------------------------
    |
    | Configure autocomplete and "did you mean" suggestions.
    |
    */

    'suggestions' => [
        'enabled' => true,
        'max_suggestions' => 5,
        'min_score' => 0.5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | Cache search results for improved performance.
    |
    */

    'cache' => [
        'enabled' => env('SEARCH_CACHE_ENABLED', true),
        'ttl' => env('SEARCH_CACHE_TTL', 3600), // seconds
        'prefix' => 'search:',
        'driver' => env('SEARCH_CACHE_DRIVER', null), // null = use default
    ],

    /*
    |--------------------------------------------------------------------------
    | SQLite FTS Configuration
    |--------------------------------------------------------------------------
    |
    | Configure SQLite Full-Text Search integration.
    |
    */

    'fts' => [
        'enabled' => false,
        'version' => 'fts5', // fts4 or fts5
        'tokenizer' => 'unicode61',
        'auto_sync' => true, // Automatically sync model changes to FTS index
        'table_prefix' => 'fts_',
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    |
    | Configure performance-related settings.
    |
    */

    'performance' => [
        'max_results' => 1000,
        'batch_size' => 100,
        'timeout' => 30, // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Search History
    |--------------------------------------------------------------------------
    |
    | Track search queries for analytics and suggestions.
    |
    */

    'history' => [
        'enabled' => false,
        'table' => 'search_history',
        'retention_days' => 90,
    ],

];
