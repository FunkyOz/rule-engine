---
title: Rule Definition
status: done
priority: High
description: Implement the Rule class that encapsulates conditions and enables rule evaluation
---

## Objectives
- Implement `Rule` class with condition expression
- Support rule naming and metadata
- Enable rule composition with RuleSet
- Provide rule evaluation methods

## Deliverables
1. `src/Rule/Rule.php`
2. `src/Rule/RuleSet.php`
3. `src/Rule/RuleResult.php`

## Technical Details

### Rule

The core rule class that wraps a condition expression.

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Rule;

use RuleEngine\Context\ContextInterface;
use RuleEngine\Expression\ExpressionInterface;

final readonly class Rule
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        private string $name,
        private ExpressionInterface $condition,
        private array $metadata = []
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getCondition(): ExpressionInterface
    {
        return $this->condition;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Get a specific metadata value.
     */
    public function getMeta(string $key, mixed $default = null): mixed
    {
        return $this->metadata[$key] ?? $default;
    }

    /**
     * Evaluate the rule condition.
     */
    public function evaluate(ContextInterface $context): bool
    {
        return (bool) $this->condition->evaluate($context);
    }

    /**
     * Evaluate and return a RuleResult with details.
     */
    public function evaluateWithResult(ContextInterface $context): RuleResult
    {
        $result = $this->evaluate($context);

        return new RuleResult(
            rule: $this,
            passed: $result,
            context: $context
        );
    }

    public function __toString(): string
    {
        return sprintf('Rule<%s>: %s', $this->name, (string) $this->condition);
    }
}
```

### RuleResult

Encapsulates the result of a rule evaluation.

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Rule;

use RuleEngine\Context\ContextInterface;

final readonly class RuleResult
{
    public function __construct(
        private Rule $rule,
        private bool $passed,
        private ContextInterface $context
    ) {}

    public function getRule(): Rule
    {
        return $this->rule;
    }

    public function getRuleName(): string
    {
        return $this->rule->getName();
    }

    public function passed(): bool
    {
        return $this->passed;
    }

    public function failed(): bool
    {
        return !$this->passed;
    }

    public function getContext(): ContextInterface
    {
        return $this->context;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'rule' => $this->rule->getName(),
            'passed' => $this->passed,
            'metadata' => $this->rule->getMetadata(),
        ];
    }
}
```

### RuleSet

A collection of rules with evaluation strategies.

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Rule;

use RuleEngine\Context\ContextInterface;

final class RuleSet
{
    /**
     * @var array<string, Rule>
     */
    private array $rules = [];

    public function __construct(
        private readonly string $name = 'default'
    ) {}

    public function add(Rule $rule): self
    {
        $this->rules[$rule->getName()] = $rule;

        return $this;
    }

    /**
     * @param array<Rule> $rules
     */
    public function addMany(array $rules): self
    {
        foreach ($rules as $rule) {
            $this->add($rule);
        }

        return $this;
    }

    public function get(string $name): ?Rule
    {
        return $this->rules[$name] ?? null;
    }

    public function has(string $name): bool
    {
        return isset($this->rules[$name]);
    }

    public function remove(string $name): self
    {
        unset($this->rules[$name]);

        return $this;
    }

    /**
     * @return array<string, Rule>
     */
    public function all(): array
    {
        return $this->rules;
    }

    public function count(): int
    {
        return count($this->rules);
    }

    /**
     * Evaluate all rules and return true if ALL pass.
     */
    public function evaluateAll(ContextInterface $context): bool
    {
        foreach ($this->rules as $rule) {
            if (!$rule->evaluate($context)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Evaluate all rules and return true if ANY passes.
     */
    public function evaluateAny(ContextInterface $context): bool
    {
        foreach ($this->rules as $rule) {
            if ($rule->evaluate($context)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Evaluate all rules and return detailed results.
     *
     * @return array<RuleResult>
     */
    public function evaluateWithResults(ContextInterface $context): array
    {
        return array_map(
            fn(Rule $rule) => $rule->evaluateWithResult($context),
            $this->rules
        );
    }

    /**
     * Get only the rules that passed.
     *
     * @return array<Rule>
     */
    public function getPassingRules(ContextInterface $context): array
    {
        return array_filter(
            $this->rules,
            fn(Rule $rule) => $rule->evaluate($context)
        );
    }

    /**
     * Get only the rules that failed.
     *
     * @return array<Rule>
     */
    public function getFailingRules(ContextInterface $context): array
    {
        return array_filter(
            $this->rules,
            fn(Rule $rule) => !$rule->evaluate($context)
        );
    }
}
```

## Usage Example

```php
use RuleEngine\Context\Context;
use RuleEngine\Expression\LiteralExpression;
use RuleEngine\Expression\OperatorExpression;
use RuleEngine\Expression\VariableExpression;
use RuleEngine\Operator\Comparison\GreaterThanOrEqualOperator;
use RuleEngine\Operator\Logical\AndOperator;
use RuleEngine\Rule\Rule;
use RuleEngine\Rule\RuleSet;

$context = Context::fromArray([
    'user' => [
        'age' => 25,
        'verified' => true,
    ],
]);

// age >= 18
$ageCondition = new OperatorExpression(
    new GreaterThanOrEqualOperator(),
    [
        new VariableExpression('user.age'),
        new LiteralExpression(18),
    ]
);

// verified = true
$verifiedCondition = new VariableExpression('user.verified');

// age >= 18 AND verified
$condition = new OperatorExpression(
    new AndOperator(),
    [$ageCondition, $verifiedCondition]
);

$rule = new Rule(
    name: 'adult_verified_user',
    condition: $condition,
    metadata: ['priority' => 1, 'category' => 'access']
);

// Simple evaluation
$passed = $rule->evaluate($context); // true

// Detailed result
$result = $rule->evaluateWithResult($context);
echo $result->passed(); // true

// RuleSet usage
$ruleSet = new RuleSet('access_rules');
$ruleSet->add($rule);

$allPassed = $ruleSet->evaluateAll($context); // true
```

## Dependencies
- Task 07 - Comparison Operators
- Task 08 - Logical Operators

## Estimated Complexity
**Medium** - Core rule abstraction with multiple evaluation strategies

## Implementation Notes
- Rule is immutable (readonly) for thread safety
- RuleSet allows fluent interface for rule management
- RuleResult provides detailed evaluation information
- Support for metadata enables rule categorization and priority
- Consider adding rule dependencies in the future

## Acceptance Criteria
- [x] `Rule` class encapsulates condition and metadata
- [x] `Rule::evaluate()` returns boolean result
- [x] `Rule::evaluateWithResult()` returns `RuleResult`
- [x] `RuleSet` manages collections of rules
- [x] `RuleSet::evaluateAll()` returns true if all pass
- [x] `RuleSet::evaluateAny()` returns true if any pass
- [x] `RuleResult` provides pass/fail status
- [x] Metadata is accessible from rules
- [x] PHPStan passes at level 8
- [x] Unit tests cover all functionality
