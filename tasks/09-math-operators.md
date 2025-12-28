---
title: Math Operators
status: done
priority: High
description: Implement mathematical operators for arithmetic operations
---

## Objectives
- Implement basic arithmetic operators (+, -, *, /, %)
- Implement power operator (^)
- Handle division by zero gracefully
- Support both integer and float operations

## Deliverables
1. `src/Operator/Math/AddOperator.php`
2. `src/Operator/Math/SubtractOperator.php`
3. `src/Operator/Math/MultiplyOperator.php`
4. `src/Operator/Math/DivideOperator.php`
5. `src/Operator/Math/ModuloOperator.php`
6. `src/Operator/Math/PowerOperator.php`
7. `src/Exception/DivisionByZeroException.php`

## Technical Details

### AddOperator

Variadic addition operator.

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Operator\Math;

use RuleEngine\Operator\AbstractOperator;

final class AddOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('+', -1); // Variadic
    }

    public function execute(array $operands): int|float
    {
        $this->validateOperandCount($operands);

        return array_sum($operands);
    }
}
```

### SubtractOperator

Binary subtraction operator.

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Operator\Math;

use RuleEngine\Operator\AbstractOperator;

final class SubtractOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('-', 2);
    }

    public function execute(array $operands): int|float
    {
        $this->validateOperandCount($operands);

        return $operands[0] - $operands[1];
    }
}
```

### MultiplyOperator

Variadic multiplication operator.

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Operator\Math;

use RuleEngine\Operator\AbstractOperator;

final class MultiplyOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('*', -1); // Variadic
    }

    public function execute(array $operands): int|float
    {
        $this->validateOperandCount($operands);

        return array_product($operands);
    }
}
```

### DivideOperator

Binary division operator with division by zero handling.

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Operator\Math;

use RuleEngine\Exception\DivisionByZeroException;
use RuleEngine\Operator\AbstractOperator;

final class DivideOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('/', 2);
    }

    public function execute(array $operands): int|float
    {
        $this->validateOperandCount($operands);

        if ($operands[1] == 0) {
            throw new DivisionByZeroException();
        }

        return $operands[0] / $operands[1];
    }
}
```

### ModuloOperator

Binary modulo operator.

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Operator\Math;

use RuleEngine\Exception\DivisionByZeroException;
use RuleEngine\Operator\AbstractOperator;

final class ModuloOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('%', 2);
    }

    public function execute(array $operands): int
    {
        $this->validateOperandCount($operands);

        if ($operands[1] == 0) {
            throw new DivisionByZeroException();
        }

        return (int) $operands[0] % (int) $operands[1];
    }
}
```

### PowerOperator

Binary power/exponentiation operator.

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Operator\Math;

use RuleEngine\Operator\AbstractOperator;

final class PowerOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('^', 2);
    }

    public function execute(array $operands): int|float
    {
        $this->validateOperandCount($operands);

        return $operands[0] ** $operands[1];
    }
}
```

### DivisionByZeroException

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Exception;

final class DivisionByZeroException extends RuleEngineException
{
    public function __construct()
    {
        parent::__construct('Division by zero');
    }
}
```

## Operator Summary

| Operator | Symbol | Arity | Description |
|----------|--------|-------|-------------|
| Add | `+` | Variadic | Sum of all operands |
| Subtract | `-` | 2 | First operand minus second |
| Multiply | `*` | Variadic | Product of all operands |
| Divide | `/` | 2 | First operand divided by second |
| Modulo | `%` | 2 | Remainder of integer division |
| Power | `^` | 2 | First operand to the power of second |

## Usage Example

```php
use RuleEngine\Context\Context;
use RuleEngine\Expression\LiteralExpression;
use RuleEngine\Expression\OperatorExpression;
use RuleEngine\Expression\VariableExpression;
use RuleEngine\Operator\Math\AddOperator;
use RuleEngine\Operator\Math\MultiplyOperator;

$context = Context::fromArray([
    'price' => 100,
    'quantity' => 5,
    'tax_rate' => 0.1,
]);

// total = price * quantity
$subtotal = new OperatorExpression(
    new MultiplyOperator(),
    [
        new VariableExpression('price'),
        new VariableExpression('quantity'),
    ]
);

// tax = subtotal * tax_rate
$tax = new OperatorExpression(
    new MultiplyOperator(),
    [
        $subtotal,
        new VariableExpression('tax_rate'),
    ]
);

// final = subtotal + tax
$final = new OperatorExpression(
    new AddOperator(),
    [$subtotal, $tax]
);

echo $final->evaluate($context); // 550
```

## Dependencies
- Task 05 - Expression Evaluator
- Task 06 - Operator Registry

## Estimated Complexity
**Medium** - Simple arithmetic with exception handling for edge cases

## Implementation Notes
- Add and Multiply are variadic for flexibility
- Subtract, Divide, Modulo, and Power are binary
- Division and Modulo throw `DivisionByZeroException` on zero divisor
- Return types preserve int when possible, float when necessary
- Modulo casts to int for proper integer modulo behavior

## Acceptance Criteria
- [x] All 6 math operators implemented
- [x] Add and Multiply work with multiple operands
- [x] Division by zero throws `DivisionByZeroException`
- [x] Modulo by zero throws `DivisionByZeroException`
- [x] Power operator works with negative and fractional exponents
- [x] Return types are int or float as appropriate
- [x] PHPStan passes at level 8
- [x] Unit tests cover all operators and edge cases
