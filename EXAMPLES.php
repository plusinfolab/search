<?php

/**
 * Laravel Advanced Search Package - Usage Examples
 * 
 * This file demonstrates various ways to use the search package.
 */

use PlusInfoLab\Search\Facades\Search;
use App\Models\Post;
use App\Models\Product;
use App\Models\User;

// ============================================
// BASIC USAGE
// ============================================

// Simple search with default algorithm (partial match)
$posts = Post::search('laravel')->get();

// Search with specific algorithm
$posts = Post::search('laravle', 'fuzzy')->get();

// Get results with scores and metadata
$results = Post::searchQuery('laravel framework');
foreach ($results as $result) {
    echo "Title: " . $result->getItem()->title . "\n";
    echo "Score: " . $result->getScore() . "\n";
    echo "Algorithm: " . $result->getAlgorithm() . "\n";
}

// ============================================
// ALGORITHM-SPECIFIC EXAMPLES
// ============================================

// 1. EXACT MATCH - Perfect for precise searches
$posts = Post::search('Laravel Framework', 'exact')->get();

// 2. PARTIAL MATCH - Substring matching
$posts = Post::search('frame', 'partial')->get();

// 3. PREFIX MATCH - Great for autocomplete
$posts = Post::search('lar', 'prefix')->get();

// 4. SUFFIX MATCH - Pattern detection
$files = File::search('.pdf', 'suffix')->get();

// 5. FUZZY MATCH - Typo tolerance (Levenshtein)
$posts = Post::search('laravle', 'fuzzy')->get();

// 6. TRIGRAM MATCH - Advanced similarity
$posts = Post::search('laravl', 'trigram')->get();

// 7. BOOLEAN SEARCH - Complex queries
$posts = Post::search('(laravel OR symfony) AND framework NOT deprecated', 'boolean')->get();
$posts = Post::search('"exact phrase" AND tutorial', 'boolean')->get();

// 8. REGEX SEARCH - Pattern matching
$posts = Post::search('/^Laravel.*Framework$/i', 'regex')->get();

// 9. PHONETIC SEARCH - Sounds-like matching
$users = User::search('Stephen', 'phonetic')->get(); // Matches Steven, Stefan, etc.

// ============================================
// ADVANCED SEARCH QUERY BUILDER
// ============================================

use PlusInfoLab\Search\SearchQuery;

$query = (new SearchQuery())
    ->query('laravel framework')
    ->in(['title', 'content', 'tags'])
    ->weights(['title' => 10, 'content' => 5, 'tags' => 3])
    ->using('fuzzy')
    ->minScore(0.5)
    ->limit(20)
    ->offset(0)
    ->setBuilder(Post::query());

$results = Search::search($query);

// ============================================
// SEARCH WITH FILTERS
// ============================================

$query = (new SearchQuery())
    ->query('laravel')
    ->in(['title', 'content'])
    ->where('status', 'published')
    ->where('created_at', '>', now()->subDays(30))
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->setBuilder(Post::query());

$results = Search::search($query);

// ============================================
// WORKING WITH RESULTS
// ============================================

$results = Post::searchQuery('laravel framework');

// Get items only
$posts = $results->items();

// Filter by minimum score
$topResults = $results->minScore(50);

// Sort by score
$sorted = $results->sortByScore();

// Group by algorithm
$grouped = $results->groupByAlgorithm();

// Get statistics
$maxScore = $results->maxScore();
$avgScore = $results->avgScore();

// Get highlights
foreach ($results as $result) {
    $highlights = $result->getHighlights();
    if (isset($highlights['title'])) {
        echo $highlights['title'][0]; // "<mark>Laravel</mark> <mark>Framework</mark>"
    }
}

// ============================================
// SEARCH SUGGESTIONS
// ============================================

// Autocomplete suggestions
$suggestions = Search::suggest('larav', [
    'Laravel',
    'Laravel Nova',
    'Laravel Forge',
    'JavaScript',
    'Java'
], 5);
// Returns: ['Laravel', 'Laravel Nova', 'Laravel Forge']

// "Did you mean" functionality
$suggester = app(\PlusInfoLab\Search\Features\SearchSuggester::class);
$suggestion = $suggester->didYouMean('laravle', [
    'Laravel',
    'Symfony',
    'CodeIgniter',
    'CakePHP'
]);
// Returns: 'Laravel'

// ============================================
// SYNONYM EXPANSION
// ============================================

// Configure in config/search.php
/*
'synonyms' => [
    'enabled' => true,
    'dictionary' => [
        'car' => ['automobile', 'vehicle'],
        'phone' => ['mobile', 'smartphone'],
    ],
],
*/

// Search for "car" will also search for "automobile" and "vehicle"
$products = Product::searchQuery('car');

// Add synonyms at runtime
$expander = app(\PlusInfoLab\Search\Features\SynonymExpander::class);
$expander->addSynonym('laptop', ['notebook', 'computer']);

// ============================================
// CUSTOM RANKING
// ============================================

// Configure in config/search.php
/*
'ranking' => [
    'enabled' => true,
    'algorithm_weights' => [
        'exact' => 100,
        'prefix' => 80,
        'partial' => 60,
        'fuzzy' => 40,
    ],
    'recency_boost' => [
        'enabled' => true,
        'field' => 'created_at',
        'decay_days' => 30,
        'boost_factor' => 1.5,
    ],
    'popularity_boost' => [
        'enabled' => true,
        'field' => 'views_count',
        'boost_factor' => 0.1,
    ],
],
*/

// ============================================
// SQLITE FTS INTEGRATION
// ============================================

// Enable in config/search.php
/*
'fts' => [
    'enabled' => true,
    'version' => 'fts5',
    'auto_sync' => true,
],
*/

// FTS tables are created automatically
// Search uses FTS when available
$posts = Post::search('laravel')->get();

// Manual indexing
$post = Post::find(1);
$post->searchable(); // Add to index
$post->unsearchable(); // Remove from index

// ============================================
// MODEL CONFIGURATION
// ============================================

/*
use Illuminate\Database\Eloquent\Model;
use PlusInfoLab\Search\Traits\Searchable;

class Post extends Model
{
    use Searchable;

    // Define searchable fields
    protected $searchable = ['title', 'content', 'tags', 'author'];

    // Define field weights for ranking
    protected $searchWeights = [
        'title' => 10,
        'tags' => 5,
        'content' => 3,
        'author' => 2,
    ];

    // Optional: Custom search index name
    protected $searchIndexName = 'posts_search';
}
*/

// ============================================
// CACHING
// ============================================

// Configure in config/search.php
/*
'cache' => [
    'enabled' => true,
    'ttl' => 3600, // 1 hour
    'prefix' => 'search:',
],
*/

// Searches are automatically cached
// Same query will be served from cache

// ============================================
// PERFORMANCE TIPS
// ============================================

// 1. Use appropriate algorithm for your use case
//    - Exact: When you need precise matches
//    - Partial: General purpose searching
//    - Fuzzy: When typos are expected
//    - Prefix: For autocomplete
//    - Boolean: For complex queries

// 2. Configure field weights properly
//    - Higher weights for more important fields
//    - This improves ranking accuracy

// 3. Enable caching for frequently searched queries
//    - Reduces database load
//    - Improves response time

// 4. Use FTS for large datasets
//    - Much faster than algorithm-based search
//    - Especially for text-heavy content

// 5. Set appropriate limits
//    - Prevent performance issues
//    - Use pagination for large result sets

// ============================================
// REAL-WORLD EXAMPLES
// ============================================

// E-commerce product search with typo tolerance
$products = Product::search('iphone 15', 'fuzzy')
    ->where('status', 'active')
    ->where('stock', '>', 0)
    ->get();

// Blog search with highlighting
$results = Post::searchQuery('laravel tutorial');
foreach ($results as $result) {
    $post = $result->getItem();
    $highlights = $result->getHighlights();

    echo $post->title . "\n";
    if (isset($highlights['content'])) {
        echo $highlights['content'][0] . "\n"; // Highlighted excerpt
    }
}

// User directory with phonetic search
$users = User::search('Jon Smith', 'phonetic')->get();
// Matches: John Smith, Jon Smyth, etc.

// Documentation search with boolean operators
$docs = Documentation::search('(installation OR setup) AND laravel NOT deprecated', 'boolean')->get();

// Autocomplete for search box
$query = request('q'); // User input: "lar"
$suggestions = Search::suggest($query, Post::pluck('title')->toArray(), 5);
// Returns: ['Laravel Tutorial', 'Laravel Tips', 'Laravel Best Practices']
