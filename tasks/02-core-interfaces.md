---
title: Core Interfaces & Contracts
status: done
priority: Critical
description: Define the foundational interfaces that all components will implement
---

## Objectives
- Define `ExpressionInterface` as the base for all evaluable expressions
- Define `OperatorInterface` for operator implementations
- Define `EvaluatorInterface` for the expression evaluation engine
- Define `ContextInterface` for variable resolution
- Establish type contracts for the entire system

## Deliverables
1. `src/Expression/ExpressionInterface.php`
2. `src/Operator/OperatorInterface.php`
3. `src/Evaluator/EvaluatorInterface.php`
4. `src/Context/ContextInterface.php`
5. `src/Registry/OperatorRegistryInterface.php`
6. `src/Exception/RuleEngineException.php` (base exception)

## Technical Details

### ExpressionInterface

The base interface for anything that can be evaluated to produce a value.

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Expression;

use RuleEngine\Context\ContextInterface;

interface ExpressionInterface
{
    /**
     * Evaluate the expression and return its value.
     */
    public function evaluate(ContextInterface $context): mixed;

    /**
     * Return a string representation for debugging.
     */
    public function __toString(): string;
}
```

### OperatorInterface

Interface for operators that take operands and produce a result.

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Operator;

interface OperatorInterface
{
    /**
     * Get the operator symbol/name (e.g., '=', 'AND', '+', 'IN').
     */
    public function getName(): string;

    /**
     * Get the number of operands this operator requires.
     * Return -1 for variadic operators.
     */
    public function getArity(): int;

    /**
     * Execute the operator with the given evaluated operand values.
     *
     * @param array<int, mixed> $operands The evaluated operand values
     */
    public function execute(array $operands): mixed;
}
```

### ContextInterface

Interface for the evaluation context that provides variable values.

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Context;

use RuleEngine\Exception\VariableNotFoundException;

interface ContextInterface
{
    /**
     * Get a value by name from the context.
     *
     * @throws VariableNotFoundException
     */
    public function get(string $name): mixed;

    /**
     * Check if a variable exists in the context.
     */
    public function has(string $name): bool;

    /**
     * Set a value in the context.
     */
    public function set(string $name, mixed $value): void;

    /**
     * Get all variables as an array.
     *
     * @return array<string, mixed>
     */
    public function all(): array;
}
```

### EvaluatorInterface

Interface for the expression evaluator.

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Evaluator;

use RuleEngine\Context\ContextInterface;
use RuleEngine\Expression\ExpressionInterface;

interface EvaluatorInterface
{
    /**
     * Evaluate an expression within a context.
     */
    public function evaluate(ExpressionInterface $expression, ContextInterface $context): mixed;
}
```

### OperatorRegistryInterface

Interface for operator registration and lookup.

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Registry;

use RuleEngine\Exception\OperatorNotFoundException;
use RuleEngine\Operator\OperatorInterface;

interface OperatorRegistryInterface
{
    /**
     * Register an operator.
     */
    public function register(OperatorInterface $operator): void;

    /**
     * Get an operator by name.
     *
     * @throws OperatorNotFoundException
     */
    public function get(string $name): OperatorInterface;

    /**
     * Check if an operator is registered.
     */
    public function has(string $name): bool;

    /**
     * Get all registered operator names.
     *
     * @return array<string>
     */
    public function names(): array;
}
```

### Base Exception

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Exception;

use Exception;

class RuleEngineException extends Exception
{
}
```

## Dependencies
- Task 01 - Project Setup

## Estimated Complexity
**Medium** - Requires careful design of contracts that will guide the entire system

## Implementation Notes
- All interfaces should use strict types
- Use `mixed` return types where necessary (PHP 8.0+)
- Operators should be stateless to allow reuse
- Interfaces are placed alongside their implementations in each component folder
- This keeps related code together and makes the codebase more navigable

## Acceptance Criteria
- [x] All interfaces are created in their respective component folders
- [x] PHPStan passes at level 8
- [x] Pint formatting applied
- [x] Interfaces are properly documented with PHPDoc
- [x] All method signatures use strict types
- [x] Base exception class created
