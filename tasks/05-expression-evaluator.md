---
title: Expression Evaluator
status: done
priority: Critical
description: Implement the expression evaluation engine that processes expressions and operators
---

## Objectives
- Implement `Evaluator` class for expression evaluation
- Create `OperatorExpression` for operator-based expressions
- Handle recursive expression evaluation
- Integrate with the operator registry

## Deliverables
1. `src/Evaluator/Evaluator.php`
2. `src/Expression/OperatorExpression.php`

## Technical Details

### OperatorExpression

Represents an operator applied to operand expressions.

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Expression;

use RuleEngine\Context\ContextInterface;
use RuleEngine\Operator\OperatorInterface;

final readonly class OperatorExpression implements ExpressionInterface
{
    /**
     * @param array<int, ExpressionInterface> $operands
     */
    public function __construct(
        private OperatorInterface $operator,
        private array $operands
    ) {}

    public function evaluate(ContextInterface $context): mixed
    {
        // Evaluate all operands first
        $evaluatedOperands = array_map(
            fn(ExpressionInterface $operand) => $operand->evaluate($context),
            $this->operands
        );

        // Execute the operator with evaluated values
        return $this->operator->execute($evaluatedOperands);
    }

    public function getOperator(): OperatorInterface
    {
        return $this->operator;
    }

    /**
     * @return array<int, ExpressionInterface>
     */
    public function getOperands(): array
    {
        return $this->operands;
    }

    public function __toString(): string
    {
        $operandStrings = array_map(
            fn(ExpressionInterface $op) => (string) $op,
            $this->operands
        );

        $name = $this->operator->getName();

        // Unary operator
        if (count($this->operands) === 1) {
            return sprintf('%s(%s)', $name, $operandStrings[0]);
        }

        // Binary operator (infix notation)
        if (count($this->operands) === 2) {
            return sprintf('(%s %s %s)', $operandStrings[0], $name, $operandStrings[1]);
        }

        // N-ary operator
        return sprintf('%s(%s)', $name, implode(', ', $operandStrings));
    }
}
```

### Evaluator

The main evaluation engine.

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Evaluator;

use RuleEngine\Context\ContextInterface;
use RuleEngine\Expression\ExpressionInterface;

final class Evaluator implements EvaluatorInterface
{
    public function evaluate(ExpressionInterface $expression, ContextInterface $context): mixed
    {
        return $expression->evaluate($context);
    }

    /**
     * Evaluate an expression and cast the result to boolean.
     */
    public function evaluateAsBoolean(ExpressionInterface $expression, ContextInterface $context): bool
    {
        return (bool) $this->evaluate($expression, $context);
    }

    /**
     * Evaluate multiple expressions and return all results.
     *
     * @param array<ExpressionInterface> $expressions
     * @return array<mixed>
     */
    public function evaluateAll(array $expressions, ContextInterface $context): array
    {
        return array_map(
            fn(ExpressionInterface $expr) => $this->evaluate($expr, $context),
            $expressions
        );
    }
}
```

### Usage Example

```php
use RuleEngine\Context\Context;
use RuleEngine\Evaluator\Evaluator;
use RuleEngine\Expression\LiteralExpression;
use RuleEngine\Expression\OperatorExpression;
use RuleEngine\Expression\VariableExpression;
use RuleEngine\Operator\Comparison\EqualOperator;

$context = Context::fromArray(['age' => 25]);
$evaluator = new Evaluator();

// Build expression: age = 25
$expression = new OperatorExpression(
    new EqualOperator(),
    [
        new VariableExpression('age'),
        new LiteralExpression(25),
    ]
);

$result = $evaluator->evaluate($expression, $context); // true
$asBool = $evaluator->evaluateAsBoolean($expression, $context); // true
```

## Dependencies
- Task 04 - Value Expressions
- Task 06 - Operator Registry (for integration)

## Estimated Complexity
**Medium** - Requires recursive evaluation and proper integration with operators

## Implementation Notes
- `OperatorExpression` evaluates operands before passing to operator
- The evaluator is a simple coordinator; complexity is in expressions
- Consider adding evaluation tracing/debugging in the future
- The evaluator is stateless and can be shared across evaluations

## Acceptance Criteria
- [x] `Evaluator` implements `EvaluatorInterface`
- [x] `OperatorExpression` implements `ExpressionInterface`
- [x] `OperatorExpression` correctly evaluates operands before operator execution
- [x] `Evaluator::evaluateAsBoolean()` works correctly
- [x] Nested expressions evaluate correctly
- [x] String representation is readable
- [x] PHPStan passes at level 8
- [x] Unit tests cover all functionality
