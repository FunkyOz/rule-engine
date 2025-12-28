---
title: Add Fluent API for Strict Comparison Operators
status: done
priority: Medium
description: Rename StrictEqual/StrictNotEqual operators to Identical/NotIdentical and add fluent API methods
---

## Objectives
- Rename `StrictEqualOperator` to `IdenticalOperator`
- Rename `StrictNotEqualOperator` to `NotIdenticalOperator`
- Update all references and tests to use new class names
- Add `identical()` method to ConditionBuilder
- Add `notIdentical()` method to ConditionBuilder

## Deliverables
1. Renamed operator classes:
   - `src/Operator/Comparison/StrictEqualOperator.php` → `src/Operator/Comparison/IdenticalOperator.php`
   - `src/Operator/Comparison/StrictNotEqualOperator.php` → `src/Operator/Comparison/NotIdenticalOperator.php`
2. Updated `src/Rule/ConditionBuilder.php` with two new methods:
   - `identical(mixed $value): self`
   - `notIdentical(mixed $value): self`
3. Updated test file: `tests/Unit/Operator/Comparison/ComparisonOperatorsTest.php`
4. Updated RuleBuilderTest helper and any other references
5. New unit tests for the fluent API methods

## Technical Details

### Step 1: Rename Operator Classes

**IdenticalOperator.php** (formerly StrictEqualOperator.php):
```php
<?php

declare(strict_types=1);

namespace RuleEngine\Operator\Comparison;

use RuleEngine\Operator\AbstractOperator;

final class IdenticalOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('===', 2);
    }

    public function execute(array $operands): bool
    {
        $this->validateOperandCount($operands);

        return $operands[0] === $operands[1];
    }
}
```

**NotIdenticalOperator.php** (formerly StrictNotEqualOperator.php):
```php
<?php

declare(strict_types=1);

namespace RuleEngine\Operator\Comparison;

use RuleEngine\Operator\AbstractOperator;

final class NotIdenticalOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('!==', 2);
    }

    public function execute(array $operands): bool
    {
        $this->validateOperandCount($operands);

        return $operands[0] !== $operands[1];
    }
}
```

### Step 2: Add Fluent API Methods

Add to ConditionBuilder.php in the "Comparison operators" section:

```php
public function identical(mixed $value): self
{
    return $this->applyOperator('===', $this->toExpression($value));
}

public function notIdentical(mixed $value): self
{
    return $this->applyOperator('!==', $this->toExpression($value));
}
```

### Step 3: Update All References

Files to update:
- `tests/Unit/Operator/Comparison/ComparisonOperatorsTest.php` - rename test descriptions and imports
- `tests/Unit/Rule/RuleBuilderTest.php` - update helper function imports

### Usage Example

```php
$rule = $builder
    ->name('strict_type_check')
    ->when('value')->identical(5)  // Must be integer 5, not string "5"
    ->then()
    ->build();

$rule2 = $builder
    ->name('not_strictly_null')
    ->when('value')->notIdentical(null)  // Must not be exactly null
    ->then()
    ->build();
```

## Dependencies
- None

## Estimated Complexity
**Low** - Class renaming and addition of two methods following established patterns

## Implementation Notes
- Delete the old StrictEqualOperator.php and StrictNotEqualOperator.php files after creating new ones
- The operator names (`===` and `!==`) remain unchanged for registry compatibility
- Update test names to use "Identical" instead of "StrictEqual"

## Acceptance Criteria
- [x] StrictEqualOperator renamed to IdenticalOperator
- [x] StrictNotEqualOperator renamed to NotIdenticalOperator
- [x] All imports and references updated
- [x] Test descriptions updated (e.g., "IdenticalOperator name" instead of "StrictEqualOperator name")
- [x] `identical()` method added to ConditionBuilder
- [x] `notIdentical()` method added to ConditionBuilder
- [x] Unit tests verify strict type comparison behavior
- [x] Unit tests verify fluent API chaining works
- [x] All existing tests continue to pass
- [x] Old operator files deleted
- [x] Code follows PSR-12 style (passes `composer tests:lint`)
- [x] PHPStan analysis passes
