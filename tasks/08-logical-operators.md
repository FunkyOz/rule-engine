---
title: Logical Operators
status: done
priority: High
description: Implement logical operators for boolean logic operations
---

## Objectives
- Implement AND, OR, NOT, XOR operators
- Support short-circuit evaluation where applicable
- Handle truthy/falsy values correctly

## Deliverables
1. `src/Operator/Logical/AndOperator.php`
2. `src/Operator/Logical/OrOperator.php`
3. `src/Operator/Logical/NotOperator.php`
4. `src/Operator/Logical/XorOperator.php`

## Technical Details

### AndOperator

Variadic AND operator - returns true if all operands are truthy.

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Operator\Logical;

use RuleEngine\Operator\AbstractOperator;

final class AndOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('AND', -1); // Variadic
    }

    public function execute(array $operands): bool
    {
        $this->validateOperandCount($operands);

        foreach ($operands as $operand) {
            if (!$operand) {
                return false;
            }
        }

        return true;
    }
}
```

### OrOperator

Variadic OR operator - returns true if any operand is truthy.

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Operator\Logical;

use RuleEngine\Operator\AbstractOperator;

final class OrOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('OR', -1); // Variadic
    }

    public function execute(array $operands): bool
    {
        $this->validateOperandCount($operands);

        foreach ($operands as $operand) {
            if ($operand) {
                return true;
            }
        }

        return false;
    }
}
```

### NotOperator

Unary NOT operator - negates the boolean value.

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Operator\Logical;

use RuleEngine\Operator\AbstractOperator;

final class NotOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('NOT', 1);
    }

    public function execute(array $operands): bool
    {
        $this->validateOperandCount($operands);

        return !$operands[0];
    }
}
```

### XorOperator

Binary XOR operator - returns true if exactly one operand is truthy.

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Operator\Logical;

use RuleEngine\Operator\AbstractOperator;

final class XorOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('XOR', 2);
    }

    public function execute(array $operands): bool
    {
        $this->validateOperandCount($operands);

        return (bool) $operands[0] xor (bool) $operands[1];
    }
}
```

## Operator Summary

| Operator | Symbol | Arity | Description |
|----------|--------|-------|-------------|
| And | `AND` | Variadic | True if all operands are truthy |
| Or | `OR` | Variadic | True if any operand is truthy |
| Not | `NOT` | 1 | Negates the boolean value |
| Xor | `XOR` | 2 | True if exactly one operand is truthy |

## Usage Example

```php
use RuleEngine\Context\Context;
use RuleEngine\Expression\LiteralExpression;
use RuleEngine\Expression\OperatorExpression;
use RuleEngine\Expression\VariableExpression;
use RuleEngine\Operator\Logical\AndOperator;
use RuleEngine\Operator\Logical\OrOperator;
use RuleEngine\Operator\Comparison\GreaterThanOperator;

$context = Context::fromArray([
    'age' => 25,
    'hasLicense' => true,
    'hasInsurance' => false,
]);

// age > 18 AND hasLicense
$canDrive = new OperatorExpression(
    new AndOperator(),
    [
        new OperatorExpression(
            new GreaterThanOperator(),
            [new VariableExpression('age'), new LiteralExpression(18)]
        ),
        new VariableExpression('hasLicense'),
    ]
);

echo $canDrive->evaluate($context); // true

// (age > 18 AND hasLicense) AND hasInsurance
$canDriveLegally = new OperatorExpression(
    new AndOperator(),
    [
        $canDrive,
        new VariableExpression('hasInsurance'),
    ]
);

echo $canDriveLegally->evaluate($context); // false
```

## Dependencies
- Task 05 - Expression Evaluator
- Task 06 - Operator Registry

## Estimated Complexity
**Medium** - Need to handle variadic operators and boolean coercion

## Implementation Notes
- AND and OR are variadic (can take 2+ operands)
- NOT is unary (takes exactly 1 operand)
- XOR is binary (takes exactly 2 operands)
- All operators cast operands to boolean for evaluation
- Short-circuit evaluation is natural in the loop implementations
- Consider adding NAND, NOR in the future if needed

## Acceptance Criteria
- [x] All 4 logical operators implemented
- [x] AND returns true only if all operands are truthy
- [x] OR returns true if any operand is truthy
- [x] NOT correctly negates the boolean value
- [x] XOR returns true if exactly one operand is truthy
- [x] Variadic operators work with 2+ operands
- [x] PHPStan passes at level 8
- [x] Unit tests cover all operators and edge cases
