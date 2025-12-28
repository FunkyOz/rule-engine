---
title: Set Operators
status: done
priority: High
description: Implement set operators for collection operations
---

## Objectives
- Implement membership operators (IN, NOT IN)
- Implement containment operators (CONTAINS, SUBSET)
- Implement set operation operators (UNION, INTERSECT, DIFF)
- Handle various iterable types

## Deliverables
1. `src/Operator/Set/InOperator.php`
2. `src/Operator/Set/NotInOperator.php`
3. `src/Operator/Set/ContainsOperator.php`
4. `src/Operator/Set/SubsetOperator.php`
5. `src/Operator/Set/UnionOperator.php`
6. `src/Operator/Set/IntersectOperator.php`
7. `src/Operator/Set/DiffOperator.php`

## Technical Details

### InOperator

Check if a value is in an array/set.

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Operator\Set;

use RuleEngine\Operator\AbstractOperator;

final class InOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('IN', 2);
    }

    public function execute(array $operands): bool
    {
        $this->validateOperandCount($operands);

        $needle = $operands[0];
        $haystack = $operands[1];

        if (!is_array($haystack)) {
            return false;
        }

        return in_array($needle, $haystack, strict: true);
    }
}
```

### NotInOperator

Check if a value is NOT in an array/set.

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Operator\Set;

use RuleEngine\Operator\AbstractOperator;

final class NotInOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('NOT_IN', 2);
    }

    public function execute(array $operands): bool
    {
        $this->validateOperandCount($operands);

        $needle = $operands[0];
        $haystack = $operands[1];

        if (!is_array($haystack)) {
            return true;
        }

        return !in_array($needle, $haystack, strict: true);
    }
}
```

### ContainsOperator

Check if an array contains a value (reverse of IN).

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Operator\Set;

use RuleEngine\Operator\AbstractOperator;

final class ContainsOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('CONTAINS', 2);
    }

    public function execute(array $operands): bool
    {
        $this->validateOperandCount($operands);

        $haystack = $operands[0];
        $needle = $operands[1];

        if (!is_array($haystack)) {
            return false;
        }

        return in_array($needle, $haystack, strict: true);
    }
}
```

### SubsetOperator

Check if the first array is a subset of the second.

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Operator\Set;

use RuleEngine\Operator\AbstractOperator;

final class SubsetOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('SUBSET', 2);
    }

    public function execute(array $operands): bool
    {
        $this->validateOperandCount($operands);

        $subset = $operands[0];
        $superset = $operands[1];

        if (!is_array($subset) || !is_array($superset)) {
            return false;
        }

        return array_diff($subset, $superset) === [];
    }
}
```

### UnionOperator

Return the union of two or more arrays.

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Operator\Set;

use RuleEngine\Operator\AbstractOperator;

final class UnionOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('UNION', -1); // Variadic
    }

    /**
     * @return array<mixed>
     */
    public function execute(array $operands): array
    {
        $this->validateOperandCount($operands);

        $result = [];

        foreach ($operands as $operand) {
            if (is_array($operand)) {
                $result = array_merge($result, $operand);
            }
        }

        return array_values(array_unique($result, SORT_REGULAR));
    }
}
```

### IntersectOperator

Return the intersection of two or more arrays.

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Operator\Set;

use RuleEngine\Operator\AbstractOperator;

final class IntersectOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('INTERSECT', -1); // Variadic
    }

    /**
     * @return array<mixed>
     */
    public function execute(array $operands): array
    {
        $this->validateOperandCount($operands);

        $arrays = array_filter($operands, 'is_array');

        if (count($arrays) === 0) {
            return [];
        }

        if (count($arrays) === 1) {
            return array_values(reset($arrays));
        }

        $result = array_shift($arrays);

        foreach ($arrays as $array) {
            $result = array_intersect($result, $array);
        }

        return array_values($result);
    }
}
```

### DiffOperator

Return the difference between two arrays (elements in first but not in second).

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Operator\Set;

use RuleEngine\Operator\AbstractOperator;

final class DiffOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('DIFF', 2);
    }

    /**
     * @return array<mixed>
     */
    public function execute(array $operands): array
    {
        $this->validateOperandCount($operands);

        $first = $operands[0];
        $second = $operands[1];

        if (!is_array($first)) {
            return [];
        }

        if (!is_array($second)) {
            return array_values($first);
        }

        return array_values(array_diff($first, $second));
    }
}
```

## Operator Summary

| Operator | Symbol | Arity | Returns | Description |
|----------|--------|-------|---------|-------------|
| In | `IN` | 2 | bool | Value is in array |
| NotIn | `NOT_IN` | 2 | bool | Value is not in array |
| Contains | `CONTAINS` | 2 | bool | Array contains value |
| Subset | `SUBSET` | 2 | bool | First array is subset of second |
| Union | `UNION` | Variadic | array | Combined unique values |
| Intersect | `INTERSECT` | Variadic | array | Common values |
| Diff | `DIFF` | 2 | array | Values in first but not in second |

## Usage Example

```php
use RuleEngine\Context\Context;
use RuleEngine\Expression\LiteralExpression;
use RuleEngine\Expression\OperatorExpression;
use RuleEngine\Expression\VariableExpression;
use RuleEngine\Operator\Set\InOperator;

$context = Context::fromArray([
    'user_role' => 'admin',
    'allowed_roles' => ['admin', 'moderator', 'editor'],
    'tags' => ['php', 'rule-engine', 'library'],
]);

// Check if user_role IN allowed_roles
$hasPermission = new OperatorExpression(
    new InOperator(),
    [
        new VariableExpression('user_role'),
        new VariableExpression('allowed_roles'),
    ]
);

echo $hasPermission->evaluate($context); // true
```

## Dependencies
- Task 05 - Expression Evaluator
- Task 06 - Operator Registry

## Estimated Complexity
**Medium** - Set operations with proper type handling

## Implementation Notes
- IN and CONTAINS use strict comparison for type safety
- UNION removes duplicates using `SORT_REGULAR` for mixed types
- INTERSECT and DIFF use PHP's array functions
- All operators handle non-array inputs gracefully
- Results are re-indexed with `array_values()` for consistency

## Acceptance Criteria
- [x] All 7 set operators implemented
- [x] IN correctly checks membership with strict comparison
- [x] NOT_IN is the inverse of IN
- [x] CONTAINS is the reverse argument order of IN
- [x] SUBSET correctly checks subset relationship
- [x] UNION combines and deduplicates arrays
- [x] INTERSECT returns common elements
- [x] DIFF returns elements in first but not second
- [x] All operators handle non-array inputs gracefully
- [x] PHPStan passes at level 8
- [x] Unit tests cover all operators and edge cases
