---
title: Operator Registry
status: done
priority: Critical
description: Implement the operator registry for managing and looking up operators by name
---

## Objectives
- Implement `OperatorRegistry` for operator registration and lookup
- Create `AbstractOperator` base class for operators
- Create `OperatorNotFoundException` exception
- Enable dynamic operator registration

## Deliverables
1. `src/Registry/OperatorRegistry.php`
2. `src/Operator/AbstractOperator.php`
3. `src/Exception/OperatorNotFoundException.php`

## Technical Details

### OperatorRegistry

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Registry;

use RuleEngine\Exception\OperatorNotFoundException;
use RuleEngine\Operator\OperatorInterface;

final class OperatorRegistry implements OperatorRegistryInterface
{
    /**
     * @var array<string, OperatorInterface>
     */
    private array $operators = [];

    public function register(OperatorInterface $operator): void
    {
        $this->operators[$operator->getName()] = $operator;
    }

    /**
     * Register multiple operators at once.
     *
     * @param array<OperatorInterface> $operators
     */
    public function registerMany(array $operators): void
    {
        foreach ($operators as $operator) {
            $this->register($operator);
        }
    }

    public function get(string $name): OperatorInterface
    {
        if (!$this->has($name)) {
            throw new OperatorNotFoundException($name);
        }

        return $this->operators[$name];
    }

    public function has(string $name): bool
    {
        return isset($this->operators[$name]);
    }

    public function names(): array
    {
        return array_keys($this->operators);
    }

    /**
     * Get all registered operators.
     *
     * @return array<string, OperatorInterface>
     */
    public function all(): array
    {
        return $this->operators;
    }

    /**
     * Create a registry with default operators pre-registered.
     */
    public static function withDefaults(): self
    {
        $registry = new self();

        // Register all default operators
        // This will be populated as operators are implemented

        return $registry;
    }
}
```

### AbstractOperator

A base class for operators providing common functionality.

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Operator;

abstract class AbstractOperator implements OperatorInterface
{
    public function __construct(
        protected readonly string $name,
        protected readonly int $arity
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getArity(): int
    {
        return $this->arity;
    }

    /**
     * Validate the number of operands.
     *
     * @param array<int, mixed> $operands
     * @throws \InvalidArgumentException
     */
    protected function validateOperandCount(array $operands): void
    {
        $count = count($operands);

        if ($this->arity === -1) {
            // Variadic: at least one operand
            if ($count < 1) {
                throw new \InvalidArgumentException(
                    sprintf('Operator "%s" requires at least 1 operand, %d given', $this->name, $count)
                );
            }
            return;
        }

        if ($count !== $this->arity) {
            throw new \InvalidArgumentException(
                sprintf('Operator "%s" requires %d operand(s), %d given', $this->name, $this->arity, $count)
            );
        }
    }
}
```

### OperatorNotFoundException

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Exception;

final class OperatorNotFoundException extends RuleEngineException
{
    public function __construct(string $name)
    {
        parent::__construct("Operator '{$name}' not found in registry");
    }
}
```

### Usage Example

```php
use RuleEngine\Registry\OperatorRegistry;
use RuleEngine\Operator\Comparison\EqualOperator;
use RuleEngine\Operator\Logical\AndOperator;

$registry = new OperatorRegistry();

// Register operators
$registry->register(new EqualOperator());
$registry->register(new AndOperator());

// Or register many at once
$registry->registerMany([
    new EqualOperator(),
    new AndOperator(),
]);

// Lookup operators
$equal = $registry->get('=');
$and = $registry->get('AND');

// Check existence
if ($registry->has('OR')) {
    // ...
}

// Get all names
$names = $registry->names(); // ['=', 'AND']
```

## Dependencies
- Task 02 - Core Interfaces

## Estimated Complexity
**Medium** - Simple registry pattern but needs careful design for extensibility

## Implementation Notes
- Operators are indexed by name for O(1) lookup
- The registry is mutable to allow dynamic registration
- `AbstractOperator` provides validation and common properties
- Consider adding operator aliases in the future (e.g., "==" for "=")
- The `withDefaults()` factory will be populated as operators are implemented

## Acceptance Criteria
- [x] `OperatorRegistry` implements `OperatorRegistryInterface`
- [x] `register()` adds operators to the registry
- [x] `get()` returns the correct operator
- [x] `get()` throws `OperatorNotFoundException` for unknown operators
- [x] `has()` correctly checks operator existence
- [x] `names()` returns all registered operator names
- [x] `registerMany()` registers multiple operators
- [x] `AbstractOperator` provides name and arity
- [x] `AbstractOperator::validateOperandCount()` works correctly
- [x] PHPStan passes at level 8
- [x] Unit tests cover all functionality
