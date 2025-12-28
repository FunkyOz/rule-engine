---
title: Value Expressions
status: done
priority: Critical
description: Implement literal and variable expressions as the building blocks for all evaluations
---

## Objectives
- Implement `LiteralExpression` for static values
- Implement `VariableExpression` for context variable references
- Create the foundation for the expression tree

## Deliverables
1. `src/Expression/LiteralExpression.php`
2. `src/Expression/VariableExpression.php`

## Technical Details

### LiteralExpression

Represents a literal/static value that evaluates to itself.

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Expression;

use RuleEngine\Context\ContextInterface;

final readonly class LiteralExpression implements ExpressionInterface
{
    public function __construct(
        private mixed $value
    ) {}

    public function evaluate(ContextInterface $context): mixed
    {
        return $this->value;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return match (true) {
            is_null($this->value) => 'null',
            is_bool($this->value) => $this->value ? 'true' : 'false',
            is_string($this->value) => '"' . addslashes($this->value) . '"',
            is_array($this->value) => '[' . implode(', ', array_map(
                fn($v) => (new self($v))->__toString(),
                $this->value
            )) . ']',
            default => (string) $this->value,
        };
    }
}
```

### VariableExpression

Represents a reference to a variable in the context.

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Expression;

use RuleEngine\Context\ContextInterface;

final readonly class VariableExpression implements ExpressionInterface
{
    public function __construct(
        private string $name
    ) {}

    public function evaluate(ContextInterface $context): mixed
    {
        return $context->get($this->name);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function __toString(): string
    {
        return '$' . $this->name;
    }
}
```

### Usage Example

```php
use RuleEngine\Context\Context;
use RuleEngine\Expression\LiteralExpression;
use RuleEngine\Expression\VariableExpression;

$context = Context::fromArray([
    'user' => [
        'name' => 'John',
        'age' => 30,
    ],
    'threshold' => 18,
]);

// Literal expression
$literal = new LiteralExpression(100);
echo $literal->evaluate($context); // 100

// Variable expression
$variable = new VariableExpression('user.name');
echo $variable->evaluate($context); // "John"

// Nested variable expression
$age = new VariableExpression('user.age');
echo $age->evaluate($context); // 30
```

## Dependencies
- Task 02 - Core Interfaces
- Task 03 - Context System

## Estimated Complexity
**Low** - Simple implementations that delegate to the context

## Implementation Notes
- Use `readonly` classes (PHP 8.2+) for immutability
- `LiteralExpression` holds any PHP value (scalar, array, object, null)
- `VariableExpression` uses dot notation through the context
- Both provide meaningful `__toString()` for debugging

## Acceptance Criteria
- [x] `LiteralExpression` implements `ExpressionInterface`
- [x] `VariableExpression` implements `ExpressionInterface`
- [x] `LiteralExpression::evaluate()` returns the stored value
- [x] `VariableExpression::evaluate()` returns the context value
- [x] `VariableExpression` throws `VariableNotFoundException` for missing variables
- [x] Both provide meaningful string representations
- [x] PHPStan passes at level 8
- [x] Unit tests cover all functionality
