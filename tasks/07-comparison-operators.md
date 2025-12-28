---
title: Comparison Operators
status: done
priority: High
description: Implement comparison operators for equality and relational comparisons
---

## Objectives
- Implement all comparison operators
- Support both strict and loose comparisons
- Handle null comparisons correctly

## Deliverables
1. `src/Operator/Comparison/EqualOperator.php`
2. `src/Operator/Comparison/NotEqualOperator.php`
3. `src/Operator/Comparison/LessThanOperator.php`
4. `src/Operator/Comparison/LessThanOrEqualOperator.php`
5. `src/Operator/Comparison/GreaterThanOperator.php`
6. `src/Operator/Comparison/GreaterThanOrEqualOperator.php`
7. `src/Operator/Comparison/StrictEqualOperator.php`
8. `src/Operator/Comparison/StrictNotEqualOperator.php`

## Technical Details

### EqualOperator

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Operator\Comparison;

use RuleEngine\Operator\AbstractOperator;

final class EqualOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('=', 2);
    }

    public function execute(array $operands): bool
    {
        $this->validateOperandCount($operands);

        return $operands[0] == $operands[1];
    }
}
```

### NotEqualOperator

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Operator\Comparison;

use RuleEngine\Operator\AbstractOperator;

final class NotEqualOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('!=', 2);
    }

    public function execute(array $operands): bool
    {
        $this->validateOperandCount($operands);

        return $operands[0] != $operands[1];
    }
}
```

### LessThanOperator

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Operator\Comparison;

use RuleEngine\Operator\AbstractOperator;

final class LessThanOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('<', 2);
    }

    public function execute(array $operands): bool
    {
        $this->validateOperandCount($operands);

        return $operands[0] < $operands[1];
    }
}
```

### LessThanOrEqualOperator

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Operator\Comparison;

use RuleEngine\Operator\AbstractOperator;

final class LessThanOrEqualOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('<=', 2);
    }

    public function execute(array $operands): bool
    {
        $this->validateOperandCount($operands);

        return $operands[0] <= $operands[1];
    }
}
```

### GreaterThanOperator

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Operator\Comparison;

use RuleEngine\Operator\AbstractOperator;

final class GreaterThanOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('>', 2);
    }

    public function execute(array $operands): bool
    {
        $this->validateOperandCount($operands);

        return $operands[0] > $operands[1];
    }
}
```

### GreaterThanOrEqualOperator

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Operator\Comparison;

use RuleEngine\Operator\AbstractOperator;

final class GreaterThanOrEqualOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('>=', 2);
    }

    public function execute(array $operands): bool
    {
        $this->validateOperandCount($operands);

        return $operands[0] >= $operands[1];
    }
}
```

### StrictEqualOperator

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Operator\Comparison;

use RuleEngine\Operator\AbstractOperator;

final class StrictEqualOperator extends AbstractOperator
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

### StrictNotEqualOperator

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Operator\Comparison;

use RuleEngine\Operator\AbstractOperator;

final class StrictNotEqualOperator extends AbstractOperator
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

## Operator Summary

| Operator | Symbol | Description |
|----------|--------|-------------|
| Equal | `=` | Loose equality (`==`) |
| NotEqual | `!=` | Loose inequality (`!=`) |
| LessThan | `<` | Less than |
| LessThanOrEqual | `<=` | Less than or equal |
| GreaterThan | `>` | Greater than |
| GreaterThanOrEqual | `>=` | Greater than or equal |
| StrictEqual | `===` | Strict equality |
| StrictNotEqual | `!==` | Strict inequality |

## Dependencies
- Task 05 - Expression Evaluator
- Task 06 - Operator Registry

## Estimated Complexity
**Medium** - Multiple simple operators following the same pattern

## Implementation Notes
- All comparison operators are binary (arity = 2)
- All return boolean values
- Loose equality uses PHP's `==` operator
- Strict equality uses PHP's `===` operator
- Consider edge cases with null, empty strings, and type coercion

## Acceptance Criteria
- [x] All 8 comparison operators implemented
- [x] Each operator extends `AbstractOperator`
- [x] Each operator validates operand count
- [x] Each operator returns boolean
- [x] Strict vs loose equality works correctly
- [x] Null comparisons work correctly
- [x] PHPStan passes at level 8
- [x] Unit tests cover all operators with various types
