# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [0.2.0] Scores, Ingestion Rewrite & Prompt Enhancements - 2026-02-19

### Added
- **Score facade method** — `Langfuse::score()` for full CRUD access to Langfuse scores
- **Fluent ingestion API** — `Langfuse::ingestion()->trace()` now returns a `Trace` object with `span()`, `generation()`, and `update()` methods
- **Span support** — nested spans and generations via the fluent API
- **Default label config** — new `LANGFUSE_DEFAULT_LABEL` env variable, defaults to `APP_ENV`
- **Environment config** — `LANGFUSE_ENVIRONMENT` env variable, defaults to `APP_ENV`
- **HTTP timeout config** — configurable `LANGFUSE_CONNECT_TIMEOUT` and `LANGFUSE_TIMEOUT` env variables
- **Extended config file** — fully documented `langfuse-laravel.php` config with sections for base URI, API keys, environment, default label, and timeouts

### Changed
- **langfuse-php ^0.2.0** — requires the rewritten PHP package with fluent ingestion, scores, and prompt enhancements
- `Langfuse::ingestion()` — no longer accepts `$environment` parameter; set it via config
- `LangfuseServiceProvider` — now passes `environment` and `label` to the `Langfuse` constructor
- Updated README with new tracing, prompts, scores, and testing documentation

### Removed
- Old ingestion examples using positional `$traceId` and `$sessionId` parameters

## [0.1.4] Ingestion - 2025-08-11

### Added
- Ingestion support via `Langfuse::ingestion()` facade method
- OpenTelemetry-based logging setup
- Telemetry logging configuration
- CONTRIBUTING.md with guidelines for development setup, code quality, testing, and pull requests
- `composer analyse` to `composer codestyle` script

### Changed
- Updated README with detailed feature descriptions and usage examples for prompts and ingestion
- Refactored `composer.json` scripts for improved testing and linting workflow
- Updated Langfuse facade imports for consistency
- Bumped `langfuse-php` dependency to `^0.1.4`

## [0.1.3] Dependency Update - 2025-08-05

### Changed
- Updated `langfuse-php` dependency version
- Bumped `aglipanci/laravel-pint-action` from 2.5 to 2.6

## [0.1.2] Version Alignment - 2025-06-30

### Changed
- Set parent package to v0.1.2 to match langfuse-php version

## [0.1.1] Stability - 2025-06-30

### Changed
- Set parent package to v0.1.0 and set minimum stability to `stable`

## [0.1.0] Initial Release - 2025-06-30

### Added
- Langfuse facade with `prompt()` method
- Fallback prompt support
- Environment variable configuration for API keys and base URI
- Support for PHP 8.3 and Laravel 11+
