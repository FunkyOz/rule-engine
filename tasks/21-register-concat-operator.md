---
title: Register ConcatOperator in Engine
status: done
priority: Critical
description: Register the new CONCAT operator in the RuleEngine
---

## Objectives
- Register `ConcatOperator` in the default operator registry
- Ensure the operator is available when `RuleEngine::create()` is called
- Follow existing operator registration patterns
- Verify operator can be retrieved via `$registry->get('CONCAT')`

## Deliverables
1. Add `ConcatOperator` registration in `RuleEngine` class
2. Operator available in default engine configuration
3. Operator can be used in both fluent API and direct expressions

## Technical Details

### File to Modify
```
src/RuleEngine.php
```

### Implementation Steps

1. **Import the ConcatOperator**
   Add import statement with other string operators:
   ```php
   use RuleEngine\Operator\String\ConcatOperator;
   ```

2. **Register in the Registry**
   Locate the section where operators are registered (typically in `create()` or a similar factory method). Add:
   ```php
   $registry->register(new ConcatOperator());
   ```

### Finding the Registration Point

Based on the project structure, operators are likely registered in one of these locations:
- `RuleEngine::create()` method
- A separate registry initialization method
- The `OperatorRegistry` constructor

Reference the registration of existing string operators like:
- `StartsWithOperator`
- `EndsWithOperator`
- `ContainsStringOperator`
- `MatchesOperator`

### Verification

After implementation, verify that:
```php
$engine = RuleEngine::create();
$operator = $engine->getRegistry()->get('CONCAT');
// Should return instance of ConcatOperator
```

## Dependencies
- Task 20 - Create ConcatOperator (must be completed first)

## Estimated Complexity
**Low** - Simple registration following existing pattern

## Implementation Notes
- Check if there's a specific order for operator registration (alphabetical, by category, etc.)
- Ensure the registration follows the same pattern as other string operators
- The operator should be registered as part of the default/core operators, not as a custom operator
- No configuration options needed for `ConcatOperator` (unlike case-sensitive operators)

## Acceptance Criteria
- [x] `ConcatOperator` imported in `RuleEngine.php`
- [x] Operator registered in the default operator registry
- [x] `$engine->getRegistry()->get('CONCAT')` returns `ConcatOperator` instance
- [x] Registration follows existing pattern and conventions
- [x] Code follows PSR-12 standards
- [x] PHPStan level 9 passes with no errors
- [x] No breaking changes to existing functionality
