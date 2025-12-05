# Laravel Advanced Search

[![Latest Version on Packagist](https://img.shields.io/packagist/v/plusinfolab/search.svg?style=flat-square)](https://packagist.org/packages/plusinfolab/search)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/plusinfolab/search/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/plusinfolab/search/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/plusinfolab/search.svg?style=flat-square)](https://packagist.org/packages/plusinfolab/search)
[![License](https://img.shields.io/packagist/l/plusinfolab/search.svg?style=flat-square)](https://packagist.org/packages/plusinfolab/search)
[![PHP Version](https://img.shields.io/packagist/php-v/plusinfolab/search.svg?style=flat-square)](https://packagist.org/packages/plusinfolab/search)

A comprehensive, production-ready search solution for Laravel applications with **9 powerful search algorithms**, seamless **Eloquent integration**, and **SQLite FTS support**. Make searching in your Laravel apps incredibly easy and powerful—**no external services required**!


## ✨ Features

### 🔍 Multiple Search Algorithms
- **Exact Match** - Perfect for precise searches
- **Partial Match** - Substring matching with intelligent scoring
- **Prefix/Suffix** - Great for autocomplete
- **Fuzzy Search** - Levenshtein distance for typo tolerance
- **Trigram Search** - Advanced similarity matching
- **Boolean Search** - AND/OR/NOT operators with phrase support
- **Regex Search** - Pattern matching with safety checks
- **Phonetic Search** - Soundex/Metaphone for "sounds-like" searches

### 🚀 Advanced Features
- **Smart Ranking** - Multi-factor scoring (algorithm weights, field weights, recency, popularity)
- **Result Highlighting** - Automatic highlighting of matched terms
- **Search Suggestions** - Autocomplete and "did you mean" functionality
- **Synonym Expansion** - Configurable synonym dictionaries
- **Caching** - Built-in result caching for performance
- **Search History** - Track and analyze search queries

### 💎 Eloquent Integration
- Simple `Searchable` trait for models
- Fluent query builder API
- Automatic index synchronization
- Configurable field weights
- No external dependencies required!

### 🗄️ SQLite FTS Support
- Automatic FTS5 table creation
- Real-time index synchronization
- Optimized full-text search

## 📦 Installation

Install via Composer:

```bash
composer require plusinfolab/search
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag="search-config"
```

Optionally, publish and run migrations for search history:

```bash
php artisan vendor:publish --tag="search-migrations"
php artisan migrate
```

## 🚀 Quick Start

### 1. Make Your Model Searchable

```php
use Illuminate\Database\Eloquent\Model;
use PlusInfoLab\Search\Traits\Searchable;

class Post extends Model
{
    use Searchable;

    // Define searchable fields
    protected $searchable = ['title', 'content', 'tags'];

    // Optional: Define field weights for ranking
    protected $searchWeights = [
        'title' => 10,    // Title matches score higher
        'content' => 5,   // Content matches score medium
        'tags' => 3,      // Tag matches score lower
    ];
}
```

### 2. Search Your Models

```php
// Simple search using default algorithm (partial match)
$posts = Post::search('laravel')->get();

// Search with specific algorithm
$posts = Post::search('laravle', 'fuzzy')->get();

// Get search results with scores and metadata
$results = Post::searchQuery('laravel framework');

foreach ($results as $result) {
    echo $result->getItem()->title;
    echo "Score: " . $result->getScore();
    echo "Matched fields: " . implode(', ', $result->getMatchedFields());
}
```

## 📖 Usage Examples

### Using the Search Facade

```php
use PlusInfoLab\Search\Facades\Search;

// Create a search query
$query = Search::query('laravel framework')
    ->in(['title', 'content'])
    ->weights(['title' => 10, 'content' => 5])
    ->using('fuzzy')
    ->minScore(0.5)
    ->limit(10);

// Execute search on Eloquent builder
$results = Search::search($query->setBuilder(Post::query()));
```

### Boolean Search

```php
// AND operator
$posts = Post::search('laravel AND framework', 'boolean')->get();

// OR operator
$posts = Post::search('laravel OR symfony', 'boolean')->get();

// NOT operator
$posts = Post::search('framework NOT deprecated', 'boolean')->get();

// Complex queries with grouping
$posts = Post::search('(laravel OR symfony) AND framework NOT old', 'boolean')->get();

// Exact phrases
$posts = Post::search('"laravel framework" AND tutorial', 'boolean')->get();
```

### Fuzzy Search (Typo Tolerance)

```php
// Will match "Laravel", "Laravle", "Laravell", etc.
$posts = Post::search('Laravle', 'fuzzy')->get();

// Configure threshold in config/search.php
'fuzzy' => [
    'threshold' => 2, // Maximum Levenshtein distance
],
```

### Phonetic Search (Sounds-Like)

```php
// Will match "Stephen", "Steven", "Stefan"
$users = User::search('Stephen', 'phonetic')->get();

// Configure algorithm (soundex or metaphone)
'phonetic' => [
    'algorithm' => 'metaphone', // or 'soundex'
],
```

### Prefix Search (Autocomplete)

```php
// Match titles starting with "lar"
$posts = Post::search('lar', 'prefix')->get();
// Returns: "Laravel Tutorial", "Laravel Tips", etc.
```

### With Highlighting

```php
$results = Post::searchQuery('laravel framework');

foreach ($results as $result) {
    $highlights = $result->getHighlights();
    
    // Highlights contain matched fragments with <mark> tags
    echo $highlights['title'][0]; 
    // Output: "The <mark>Laravel</mark> <mark>Framework</mark> Guide"
}
```

### Search Suggestions

```php
use PlusInfoLab\Search\Facades\Search;

// Get autocomplete suggestions
$suggestions = Search::suggest('larav', ['Laravel', 'JavaScript', 'Laravel Nova'], 5);
// Returns: ['Laravel', 'Laravel Nova']

// "Did you mean" functionality
$suggester = app(\PlusInfoLab\Search\Features\SearchSuggester::class);
$suggestion = $suggester->didYouMean('laravle', ['Laravel', 'Symfony', 'CodeIgniter']);
// Returns: 'Laravel'
```

### Synonym Expansion

```php
// Configure synonyms in config/search.php
'synonyms' => [
    'enabled' => true,
    'dictionary' => [
        'car' => ['automobile', 'vehicle'],
        'phone' => ['mobile', 'smartphone', 'cellphone'],
    ],
],

// Search for "car" will also search for "automobile" and "vehicle"
$results = Product::searchQuery('car');
```

### Advanced Ranking

```php
// Configure ranking in config/search.php
'ranking' => [
    'enabled' => true,
    
    // Boost recent results
    'recency_boost' => [
        'enabled' => true,
        'field' => 'created_at',
        'decay_days' => 30,
        'boost_factor' => 1.5,
    ],
    
    // Boost popular results
    'popularity_boost' => [
        'enabled' => true,
        'field' => 'views_count',
        'boost_factor' => 0.1,
    ],
],
```

### SQLite FTS Integration

```php
// Enable in config/search.php
'fts' => [
    'enabled' => true,
    'version' => 'fts5',
    'auto_sync' => true, // Automatically sync model changes
],

// FTS tables are created automatically
// Search uses FTS when available for better performance
$posts = Post::search('laravel')->get();
```

## ⚙️ Configuration

The package is highly configurable. Here are some key options:

```php
// config/search.php

return [
    // Default search algorithm
    'default_algorithm' => 'partial',
    
    // Algorithm-specific settings
    'algorithms' => [
        'fuzzy' => [
            'threshold' => 2,
            'max_length' => 255,
        ],
        'trigram' => [
            'min_similarity' => 0.3,
        ],
        // ... more algorithms
    ],
    
    // Ranking configuration
    'ranking' => [
        'enabled' => true,
        'algorithm_weights' => [
            'exact' => 100,
            'prefix' => 80,
            'partial' => 60,
            'fuzzy' => 40,
        ],
    ],
    
    // Caching
    'cache' => [
        'enabled' => true,
        'ttl' => 3600,
    ],
    
    // Highlighting
    'highlighting' => [
        'enabled' => true,
        'prefix' => '<mark>',
        'suffix' => '</mark>',
    ],
];
```

## 🧪 Testing

```bash
composer test
```

Run tests with coverage:

```bash
composer test-coverage
```

## 📊 Performance

- **Zero external dependencies** - Pure PHP implementation
- **Optimized algorithms** - Efficient string matching
- **Built-in caching** - Cache search results
- **FTS support** - Use SQLite FTS5 for large datasets
- **Configurable limits** - Prevent performance issues

## 🎯 Use Cases

- **E-commerce** - Product search with typo tolerance
- **Blog/CMS** - Content search with highlighting
- **Documentation** - Technical documentation search
- **User Directory** - Name search with phonetic matching
- **Knowledge Base** - Boolean search for complex queries
- **Autocomplete** - Prefix search for suggestions

## 📋 Requirements

- PHP 8.2 or higher
- Laravel 11.x or 12.x
- SQLite 3.9.0+ (optional, for FTS support)

## 🧪 Testing

Run the test suite:

```bash
composer test
```

Run tests with coverage:

```bash
composer test-coverage
```

Run static analysis:

```bash
composer analyse
```

## 📝 Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## 🤝 Contributing

Contributions are welcome! Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Development Setup

1. Clone the repository
2. Install dependencies: `composer install`
3. Run tests: `composer test`
4. Run static analysis: `composer analyse`
5. Format code: `composer format`

## 🔒 Security

If you discover any security issues, please email aditya@plusinfolab.in instead of using the issue tracker.

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## 🙏 Credits

- [Aditya Jodhani](https://github.com/adityajodhani) - Creator & Maintainer
- [PlusInfoLab](https://plusinfolab.in) - Development Company
- [All Contributors](../../contributors)

## 💬 Support

- **Issues**: [GitHub Issues](https://github.com/plusinfolab/search/issues)
- **Discussions**: [GitHub Discussions](https://github.com/plusinfolab/search/discussions)
- **Email**: aditya@plusinfolab.in

## 🌟 Why This Package?

Unlike other search solutions that require external services (Elasticsearch, Algolia) or complex setup, this package:

- ✅ Works out of the box with zero configuration
- ✅ No external dependencies or services required
- ✅ Multiple algorithms for different use cases
- ✅ Seamless Eloquent integration
- ✅ Production-ready with caching and optimization
- ✅ Highly configurable and extensible
- ✅ Comprehensive documentation and examples
- ✅ **100% Free and Open Source**

## 📈 Roadmap

Future features planned:
- MySQL Full-Text Search integration
- PostgreSQL Full-Text Search integration
- Elasticsearch adapter
- Multi-language support
- Advanced analytics dashboard
- Performance monitoring tools

## ⭐ Show Your Support

If you find this package useful, please consider giving it a ⭐ on [GitHub](https://github.com/plusinfolab/search)!

---

**Make search awesome in your Laravel apps! 🚀**

**Version**: 1.0.0 | **Released**: December 2025 | **License**: MIT
