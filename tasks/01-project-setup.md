---
title: Project Setup & Dependencies
status: done
priority: Critical
description: Configure development environment with testing, linting, and static analysis tools
---

## Objectives
- Configure Composer with required development dependencies
- Set up PHPStan for static analysis
- Set up Pest for testing
- Set up Laravel Pint for code formatting
- Establish coding standards and configuration

## Deliverables
1. Updated `composer.json` with all dependencies
2. PHPStan configuration (`phpstan.neon`)
3. Pest configuration (`phpunit.xml` and `tests/Pest.php`)
4. Pint configuration (`pint.json`)
5. Basic directory structure created

## Technical Details

### Required Dependencies

```json
{
    "require": {
        "php": "^8.2"
    },
    "require-dev": {
        "pestphp/pest": "^3.0",
        "phpstan/phpstan": "^2.0",
        "laravel/pint": "^1.0"
    }
}
```

### PHPStan Configuration (phpstan.neon)

```neon
parameters:
    level: 8
    paths:
        - src
    tmpDir: .phpstan-cache
```

### Pint Configuration (pint.json)

```json
{
    "preset": "psr12",
    "rules": {
        "array_syntax": {"syntax": "short"},
        "ordered_imports": {"sort_algorithm": "alpha"},
        "no_unused_imports": true,
        "single_quote": true
    }
}
```

### Directory Structure

```
src/
├── Contracts/
├── Expressions/
├── Operators/
│   ├── Comparison/
│   ├── Logical/
│   ├── Math/
│   ├── Set/
│   └── String/
├── Context/
├── Registry/
├── Rule/
├── Evaluator/
└── Serialization/
tests/
├── Unit/
├── Integration/
└── Pest.php
```

## Dependencies
- None (this is the first task)

## Estimated Complexity
**Low** - Standard project configuration with well-documented tools

## Implementation Notes
- Use PHP 8.2+ for modern features (readonly properties, enums, match expressions)
- PHPStan level 8 ensures strict type safety
- Pest provides a clean, expressive testing syntax
- Pint with PSR-12 ensures consistent code style

## Acceptance Criteria
- [x] `composer install` completes without errors
- [x] `vendor/bin/phpstan analyze` runs (may have no files to analyze initially)
- [x] `vendor/bin/phpunit` runs (may have no tests initially)
- [x] `vendor/bin/pint` runs without errors
- [x] All directories are created
- [x] `.gitignore` excludes vendor, cache, and IDE files
