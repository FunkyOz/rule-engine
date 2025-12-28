---
title: Rule Builder (Fluent API)
status: done
priority: High
description: Implement a fluent builder API for constructing rules without manually creating expression trees
---

## Objectives
- Create fluent API for building rules
- Support all operator types through method chaining
- Simplify rule construction for end users
- Maintain type safety and IDE autocompletion

## Deliverables
1. `src/Rule/RuleBuilder.php`
2. `src/Rule/ConditionBuilder.php`

## Technical Details

### RuleBuilder

The main builder for constructing rules.

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Rule;

use RuleEngine\Expression\ExpressionInterface;
use RuleEngine\Registry\OperatorRegistryInterface;

final class RuleBuilder
{
    private ?string $name = null;
    private ?ExpressionInterface $condition = null;
    /** @var array<string, mixed> */
    private array $metadata = [];

    public function __construct(
        private readonly OperatorRegistryInterface $registry
    ) {}

    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function meta(string $key, mixed $value): self
    {
        $this->metadata[$key] = $value;

        return $this;
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function metadata(array $metadata): self
    {
        $this->metadata = array_merge($this->metadata, $metadata);

        return $this;
    }

    /**
     * Start building a condition.
     */
    public function when(string|ExpressionInterface $subject): ConditionBuilder
    {
        return new ConditionBuilder($this, $this->registry, $subject);
    }

    /**
     * Set the condition directly.
     */
    public function condition(ExpressionInterface $condition): self
    {
        $this->condition = $condition;

        return $this;
    }

    /**
     * Build the rule.
     *
     * @throws \InvalidArgumentException
     */
    public function build(): Rule
    {
        if ($this->name === null) {
            throw new \InvalidArgumentException('Rule name is required');
        }

        if ($this->condition === null) {
            throw new \InvalidArgumentException('Rule condition is required');
        }

        return new Rule(
            name: $this->name,
            condition: $this->condition,
            metadata: $this->metadata
        );
    }

    /**
     * Set the condition from the condition builder (internal).
     */
    public function setCondition(ExpressionInterface $condition): self
    {
        $this->condition = $condition;

        return $this;
    }
}
```

### ConditionBuilder

Builder for constructing conditions with a fluent API.

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Rule;

use RuleEngine\Expression\ExpressionInterface;
use RuleEngine\Expression\LiteralExpression;
use RuleEngine\Expression\OperatorExpression;
use RuleEngine\Expression\VariableExpression;
use RuleEngine\Registry\OperatorRegistryInterface;

final class ConditionBuilder
{
    private ExpressionInterface $subject;
    private ?ExpressionInterface $currentCondition = null;

    public function __construct(
        private readonly RuleBuilder $ruleBuilder,
        private readonly OperatorRegistryInterface $registry,
        string|ExpressionInterface $subject
    ) {
        $this->subject = is_string($subject)
            ? new VariableExpression($subject)
            : $subject;
    }

    // Comparison operators

    public function equals(mixed $value): self
    {
        return $this->applyOperator('=', $this->toExpression($value));
    }

    public function notEquals(mixed $value): self
    {
        return $this->applyOperator('!=', $this->toExpression($value));
    }

    public function greaterThan(mixed $value): self
    {
        return $this->applyOperator('>', $this->toExpression($value));
    }

    public function greaterThanOrEqual(mixed $value): self
    {
        return $this->applyOperator('>=', $this->toExpression($value));
    }

    public function lessThan(mixed $value): self
    {
        return $this->applyOperator('<', $this->toExpression($value));
    }

    public function lessThanOrEqual(mixed $value): self
    {
        return $this->applyOperator('<=', $this->toExpression($value));
    }

    // Set operators

    public function in(array $values): self
    {
        return $this->applyOperator('IN', new LiteralExpression($values));
    }

    public function notIn(array $values): self
    {
        return $this->applyOperator('NOT_IN', new LiteralExpression($values));
    }

    public function contains(mixed $value): self
    {
        return $this->applyOperator('CONTAINS', $this->toExpression($value));
    }

    // String operators

    public function startsWith(string $prefix): self
    {
        return $this->applyOperator('STARTS_WITH', new LiteralExpression($prefix));
    }

    public function endsWith(string $suffix): self
    {
        return $this->applyOperator('ENDS_WITH', new LiteralExpression($suffix));
    }

    public function containsString(string $substring): self
    {
        return $this->applyOperator('CONTAINS_STRING', new LiteralExpression($substring));
    }

    public function matches(string $pattern): self
    {
        return $this->applyOperator('MATCHES', new LiteralExpression($pattern));
    }

    // Logical operators

    public function and(callable $callback): self
    {
        $subBuilder = new ConditionBuilder($this->ruleBuilder, $this->registry, $this->subject);
        $callback($subBuilder);

        if ($this->currentCondition !== null && $subBuilder->currentCondition !== null) {
            $this->currentCondition = new OperatorExpression(
                $this->registry->get('AND'),
                [$this->currentCondition, $subBuilder->currentCondition]
            );
        }

        return $this;
    }

    public function or(callable $callback): self
    {
        $subBuilder = new ConditionBuilder($this->ruleBuilder, $this->registry, $this->subject);
        $callback($subBuilder);

        if ($this->currentCondition !== null && $subBuilder->currentCondition !== null) {
            $this->currentCondition = new OperatorExpression(
                $this->registry->get('OR'),
                [$this->currentCondition, $subBuilder->currentCondition]
            );
        }

        return $this;
    }

    public function andWhen(string|ExpressionInterface $subject): self
    {
        $newSubject = is_string($subject)
            ? new VariableExpression($subject)
            : $subject;

        $this->subject = $newSubject;

        return $this;
    }

    // Finalization

    /**
     * Build and return to the rule builder.
     */
    public function then(): RuleBuilder
    {
        if ($this->currentCondition !== null) {
            $this->ruleBuilder->setCondition($this->currentCondition);
        }

        return $this->ruleBuilder;
    }

    /**
     * Get the current condition expression.
     */
    public function getCondition(): ?ExpressionInterface
    {
        return $this->currentCondition;
    }

    private function applyOperator(string $name, ExpressionInterface $value): self
    {
        $operator = $this->registry->get($name);

        $newCondition = new OperatorExpression(
            $operator,
            [$this->subject, $value]
        );

        if ($this->currentCondition === null) {
            $this->currentCondition = $newCondition;
        } else {
            // Chain with AND by default
            $this->currentCondition = new OperatorExpression(
                $this->registry->get('AND'),
                [$this->currentCondition, $newCondition]
            );
        }

        return $this;
    }

    private function toExpression(mixed $value): ExpressionInterface
    {
        if ($value instanceof ExpressionInterface) {
            return $value;
        }

        // Check if it's a variable reference (starts with $)
        if (is_string($value) && str_starts_with($value, '$')) {
            return new VariableExpression(substr($value, 1));
        }

        return new LiteralExpression($value);
    }
}
```

## Usage Example

```php
use RuleEngine\Registry\OperatorRegistry;
use RuleEngine\Rule\RuleBuilder;

$registry = OperatorRegistry::withDefaults();

// Build a rule using the fluent API
$rule = (new RuleBuilder($registry))
    ->name('adult_premium_user')
    ->meta('priority', 1)
    ->meta('category', 'access')
    ->when('user.age')
        ->greaterThanOrEqual(18)
        ->andWhen('user.subscription')
        ->equals('premium')
    ->then()
    ->build();

// More complex example
$rule2 = (new RuleBuilder($registry))
    ->name('discount_eligible')
    ->when('order.total')
        ->greaterThan(100)
        ->andWhen('customer.loyalty_tier')
        ->in(['gold', 'platinum'])
        ->andWhen('coupon.valid')
        ->equals(true)
    ->then()
    ->metadata([
        'discount' => 0.15,
        'message' => '15% loyalty discount applied',
    ])
    ->build();

// String matching
$rule3 = (new RuleBuilder($registry))
    ->name('valid_email')
    ->when('user.email')
        ->matches('/^[^@]+@[^@]+\.[^@]+$/')
        ->andWhen('user.email')
        ->endsWith('.com')
    ->then()
    ->build();
```

## Dependencies
- Task 12 - Rule Definition

## Estimated Complexity
**Medium** - Fluent API design with operator chaining

## Implementation Notes
- ConditionBuilder supports chaining multiple conditions
- Use `$variable` syntax in values to reference other variables
- `then()` returns to RuleBuilder for finalization
- Default chaining uses AND operator
- Consider adding `orWhen()` for OR-based chaining
- Registry is required for operator lookup

## Acceptance Criteria
- [x] `RuleBuilder` creates rules with fluent API
- [x] `ConditionBuilder` supports all comparison operators
- [x] `ConditionBuilder` supports set operators (in, notIn, contains)
- [x] `ConditionBuilder` supports string operators
- [x] Method chaining works correctly
- [x] Variable references work with `$` prefix
- [x] `then()` returns to RuleBuilder
- [x] `build()` validates required fields
- [x] PHPStan passes at level 8
- [x] Unit tests cover all methods
