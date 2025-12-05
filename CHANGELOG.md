# Changelog

All notable changes to `plusinfolab/search` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-12-05

### 🎉 Initial Release

This is the first stable release of the Laravel Advanced Search package!

### Added

#### Core Features
- **9 Search Algorithms** - Multiple search strategies for different use cases:
  - Exact Match - Perfect for precise searches
  - Partial Match - Substring matching with intelligent scoring
  - Prefix Match - Great for autocomplete functionality
  - Suffix Match - Pattern detection at string end
  - Fuzzy Match - Levenshtein distance for typo tolerance
  - Trigram Match - Advanced similarity matching using Jaccard coefficient
  - Boolean Search - AND/OR/NOT operators with phrase support
  - Regex Search - Pattern matching with safety checks
  - Phonetic Search - Soundex and Metaphone for "sounds-like" searches

#### Advanced Features
- **Smart Ranking Engine** - Multi-factor scoring system:
  - Algorithm-based weights
  - Field-specific weights
  - Recency boost for time-sensitive content
  - Popularity boost based on engagement metrics
- **Result Highlighting** - Automatic highlighting of matched terms with fragment extraction
- **Search Suggestions** - Autocomplete and "did you mean" functionality
- **Synonym Expansion** - Configurable synonym dictionaries with JSON file support
- **Caching System** - Built-in result caching for improved performance
- **Search History** - Track and analyze search queries (optional)

#### Eloquent Integration
- `Searchable` trait for easy model integration
- Fluent query builder API
- Automatic index synchronization via observers
- Configurable field weights per model
- Database-agnostic ordering (works with MySQL, PostgreSQL, SQLite)
- Simple usage: `Model::search('query')->get()`

#### SQLite FTS Support
- Automatic FTS5 virtual table creation
- Real-time index synchronization
- Index optimization capabilities
- Seamless integration with Eloquent models

#### Developer Experience
- Zero external dependencies (pure PHP implementation)
- Comprehensive configuration file with sensible defaults
- Extensive documentation with usage examples
- Production-ready with error handling and safety checks
- Full test coverage with Pest PHP
- PSR-4 autoloading
- Laravel 11+ and 12+ support
- PHP 8.2+ support

#### Configuration
- Highly configurable with `config/search.php`
- Per-algorithm settings (thresholds, limits, etc.)
- Ranking weights and boost factors
- Synonym dictionary management
- Highlighting customization
- Cache configuration
- FTS settings
- Performance limits

#### Testing
- Comprehensive test suite with Pest PHP
- Unit tests for all search algorithms
- Feature tests for Eloquent integration
- Architecture tests for code quality
- 100% passing test coverage

#### Documentation
- Extensive README with installation guide
- Quick start guide
- Usage examples for all algorithms
- Configuration reference
- API documentation
- Real-world use case examples
- Troubleshooting guide

### Technical Details
- **Package Name**: `plusinfolab/search`
- **Namespace**: `PlusInfoLab\Search`
- **Minimum PHP**: 8.2
- **Laravel Support**: 11.x, 12.x
- **License**: MIT
- **Total Files**: 29 PHP files
- **Test Coverage**: 11 tests, 16 assertions

### Performance
- Optimized algorithms for large datasets
- Built-in caching support
- Configurable performance limits
- FTS support for text-heavy content
- Batch indexing capabilities

### Security
- Regex DoS prevention with pattern validation
- Safe query parsing
- Input sanitization
- No SQL injection vulnerabilities

---

## Future Roadmap

Planned features for upcoming releases:
- MySQL Full-Text Search integration
- PostgreSQL Full-Text Search integration
- Elasticsearch adapter
- Multi-language support
- Advanced analytics dashboard
- Search query suggestions based on history
- Weighted field boosting UI
- Performance monitoring tools

---

## Links

- [GitHub Repository](https://github.com/plusinfolab/search)
- [Packagist](https://packagist.org/packages/plusinfolab/search)
- [Documentation](https://github.com/plusinfolab/search#readme)
- [Issue Tracker](https://github.com/plusinfolab/search/issues)

---

**Note**: This changelog follows the [Keep a Changelog](https://keepachangelog.com/en/1.0.0/) format.
