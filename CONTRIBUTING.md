# Contributing to Laravel Advanced Search

First off, thank you for considering contributing to Laravel Advanced Search! It's people like you that make this package better for everyone.

## 🤝 Code of Conduct

This project and everyone participating in it is governed by our Code of Conduct. By participating, you are expected to uphold this code. Please report unacceptable behavior to aditya@plusinfolab.in.

## 🐛 How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check the existing issues as you might find out that you don't need to create one. When you are creating a bug report, please include as many details as possible:

- **Use a clear and descriptive title**
- **Describe the exact steps to reproduce the problem**
- **Provide specific examples to demonstrate the steps**
- **Describe the behavior you observed and what behavior you expected**
- **Include code samples and error messages**
- **Specify your PHP version, Laravel version, and package version**

### Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues. When creating an enhancement suggestion, please include:

- **Use a clear and descriptive title**
- **Provide a detailed description of the suggested enhancement**
- **Explain why this enhancement would be useful**
- **List any similar features in other packages**

### Pull Requests

1. **Fork the repository** and create your branch from `master`
2. **Make your changes** following our coding standards
3. **Add tests** for any new functionality
4. **Ensure all tests pass** by running `composer test`
5. **Run static analysis** with `composer analyse`
6. **Format your code** with `composer format`
7. **Update documentation** if needed
8. **Write a clear commit message**

## 🔧 Development Setup

```bash
# Clone your fork
git clone https://github.com/YOUR-USERNAME/search.git
cd search

# Install dependencies
composer install

# Run tests
composer test

# Run static analysis
composer analyse

# Format code
composer format
```

## 📝 Coding Standards

We follow PSR-12 coding standards and use Laravel Pint for code formatting.

### PHP Code Style

- Use PSR-12 coding standard
- Use type hints for all parameters and return types
- Add PHPDoc blocks for all methods
- Keep methods focused and small
- Use meaningful variable and method names

### Testing

- Write tests for all new features
- Ensure all tests pass before submitting PR
- Aim for high test coverage
- Use descriptive test names

Example test:

```php
it('can search with fuzzy algorithm', function () {
    $posts = Post::search('laravle', 'fuzzy')->get();
    
    expect($posts)->not->toBeEmpty();
});
```

## 📚 Documentation

- Update README.md for new features
- Add examples for new functionality
- Update CHANGELOG.md following Keep a Changelog format
- Add inline code comments for complex logic

## 🔄 Pull Request Process

1. **Update the README.md** with details of changes if applicable
2. **Update the CHANGELOG.md** under the "Unreleased" section
3. **Ensure all tests pass** and code is formatted
4. **Request review** from maintainers
5. **Address review feedback** promptly
6. **Squash commits** if requested before merging

### Commit Message Guidelines

We follow conventional commits:

- `feat:` - New feature
- `fix:` - Bug fix
- `docs:` - Documentation changes
- `test:` - Adding or updating tests
- `refactor:` - Code refactoring
- `perf:` - Performance improvements
- `chore:` - Maintenance tasks

Examples:
```
feat: add support for MySQL full-text search
fix: resolve fuzzy search threshold issue
docs: update README with new examples
test: add tests for trigram matcher
```

## 🎯 What to Contribute

### Good First Issues

Look for issues labeled `good first issue` - these are great for newcomers!

### Priority Areas

- Additional search algorithms
- Performance optimizations
- Documentation improvements
- Test coverage improvements
- Bug fixes
- MySQL/PostgreSQL FTS integration
- Multi-language support

## 🧪 Testing Guidelines

### Running Tests

```bash
# Run all tests
composer test

# Run specific test file
vendor/bin/pest tests/Unit/Algorithms/FuzzyMatcherTest.php

# Run with coverage
composer test-coverage
```

### Writing Tests

- Place unit tests in `tests/Unit/`
- Place feature tests in `tests/Feature/`
- Use Pest PHP syntax
- Test edge cases and error conditions
- Mock external dependencies

## 📋 Checklist Before Submitting PR

- [ ] Code follows PSR-12 standards
- [ ] All tests pass (`composer test`)
- [ ] Static analysis passes (`composer analyse`)
- [ ] Code is formatted (`composer format`)
- [ ] Documentation is updated
- [ ] CHANGELOG.md is updated
- [ ] Commit messages follow conventions
- [ ] No debugging code left in
- [ ] New features have tests
- [ ] Breaking changes are documented

## 🚀 Release Process

Releases are managed by maintainers:

1. Update version in `composer.json`
2. Update CHANGELOG.md with release date
3. Create GitHub release with tag
4. Publish to Packagist (automatic)

## 💡 Questions?

- Open a [GitHub Discussion](https://github.com/plusinfolab/search/discussions)
- Email: aditya@plusinfolab.in

## 📜 License

By contributing, you agree that your contributions will be licensed under the MIT License.

---

Thank you for contributing! 🎉
