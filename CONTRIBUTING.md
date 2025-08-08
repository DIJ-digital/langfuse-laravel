# Contributing to Langfuse Laravel

Thank you for considering contributing to Langfuse Laravel! This document provides guidelines and information for contributors.

## Development Setup

### Prerequisites

- PHP 8.3 or PHP 8.4
- Composer

### Installation

Clone the repository and install dependencies:

```bash
git clone https://github.com/dij-digital/langfuse-laravel.git
cd langfuse-laravel
composer install
```

## Development Commands

### Code Quality

🤙 **Modern codebase, refactoring and static analysis in one command:**
```bash
composer codestyle
```

This command runs:
- Code linting with Laravel Pint
- Refactoring with Rector
- Type checking with PHPStan

### Individual Commands

**Linting (Code Style):**
```bash
composer lint
```

**Refactoring:**
```bash
composer refactor
```

**Testing:**
```bash
# Run the entire test suite
composer test

# Run only unit tests
composer test:unit

# Run specific test types
composer test:lint           # Code style tests
composer test:types          # PHPStan type checking
composer test:typos          # Typo checking
composer test:refactor       # Rector dry-run
```

## Code Standards

This project follows:
- **PSR-12** coding standard
- **Strict types** declaration in all PHP files
- **Modern PHP 8.3+** features and syntax
- **100% type coverage** requirement
- **100% test coverage** requirement

## Testing

All contributions must include tests. The project uses **Pest PHP** for testing.

### Test Structure
- `tests/Feature/` - Integration and feature tests
- `tests/Unit/` - Unit tests for individual classes

### Writing Tests
- All test methods must start with `test` and use CamelCase
- Use the `// Arrange // Act // Assert` pattern
- Extend `Tests\TestCase` for all tests
- Avoid using `RefreshDatabase` (already included in TestCase)

## Pull Request Process

1. **Fork** the repository
2. **Create a feature branch** from `main`
3. **Write tests** for your changes
4. **Ensure all tests pass**: `composer test`
5. **Run code style checks**: `composer codestyle`
6. **Commit** with a clear, descriptive message
7. **Submit a pull request** with a detailed description

## Code Review

All contributions will be reviewed for:
- Code quality and adherence to standards
- Test coverage and quality
- Documentation updates (if applicable)
- Backward compatibility

## Questions?

If you have questions about contributing, please:
- Open an issue for discussion
- Reach out to the maintainers
- Check existing issues and pull requests

Thank you for contributing! 🚀