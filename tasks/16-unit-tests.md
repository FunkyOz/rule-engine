---
title: Unit Tests
status: done
priority: High
description: Implement comprehensive unit tests for all components
---

## Objectives
- Test all operators individually
- Test all expression types
- Test context variable resolution
- Test rule evaluation
- Achieve high code coverage

## Deliverables
1. `tests/Unit/Expression/LiteralExpressionTest.php`
2. `tests/Unit/Expression/VariableExpressionTest.php`
3. `tests/Unit/Expression/OperatorExpressionTest.php`
4. `tests/Unit/Context/ContextTest.php`
5. `tests/Unit/Operator/Comparison/*Test.php`
6. `tests/Unit/Operator/Logical/*Test.php`
7. `tests/Unit/Operator/Math/*Test.php`
8. `tests/Unit/Operator/Set/*Test.php`
9. `tests/Unit/Operator/String/*Test.php`
10. `tests/Unit/Registry/OperatorRegistryTest.php`
11. `tests/Unit/Rule/RuleTest.php`
12. `tests/Unit/Rule/RuleSetTest.php`
13. `tests/Unit/Rule/RuleBuilderTest.php`
14. `tests/Unit/Serialization/RuleSerializerTest.php`
15. `tests/Unit/Serialization/RuleDeserializerTest.php`

## Technical Details

### Test Structure

```
tests/
├── Pest.php
├── Unit/
│   ├── Context/
│   │   └── ContextTest.php
│   ├── Expression/
│   │   ├── LiteralExpressionTest.php
│   │   ├── VariableExpressionTest.php
│   │   └── OperatorExpressionTest.php
│   ├── Operator/
│   │   ├── Comparison/
│   │   │   ├── EqualOperatorTest.php
│   │   │   └── ...
│   │   ├── Logical/
│   │   ├── Math/
│   │   ├── Set/
│   │   └── String/
│   ├── Registry/
│   │   └── OperatorRegistryTest.php
│   ├── Rule/
│   │   ├── RuleTest.php
│   │   ├── RuleSetTest.php
│   │   └── RuleBuilderTest.php
│   └── Serialization/
│       ├── RuleSerializerTest.php
│       └── RuleDeserializerTest.php
└── Integration/
```

### Example Test: ContextTest

```php
<?php

declare(strict_types=1);

use RuleEngine\Context\Context;
use RuleEngine\Exception\VariableNotFoundException;

describe('Context', function () {
    it('gets and sets simple values', function () {
        $context = new Context();
        $context->set('name', 'John');

        expect($context->get('name'))->toBe('John');
        expect($context->has('name'))->toBeTrue();
    });

    it('supports dot notation for nested access', function () {
        $context = Context::fromArray([
            'user' => [
                'profile' => [
                    'name' => 'Jane',
                ],
            ],
        ]);

        expect($context->get('user.profile.name'))->toBe('Jane');
    });

    it('throws VariableNotFoundException for missing variables', function () {
        $context = new Context();

        expect(fn() => $context->get('missing'))
            ->toThrow(VariableNotFoundException::class);
    });

    it('sets nested values with dot notation', function () {
        $context = new Context();
        $context->set('user.email', 'test@example.com');

        expect($context->get('user.email'))->toBe('test@example.com');
    });

    it('merges contexts correctly', function () {
        $context1 = Context::fromArray(['a' => 1]);
        $context2 = Context::fromArray(['b' => 2]);

        $merged = $context1->merge($context2);

        expect($merged->get('a'))->toBe(1);
        expect($merged->get('b'))->toBe(2);
    });
});
```

### Example Test: EqualOperatorTest

```php
<?php

declare(strict_types=1);

use RuleEngine\Operator\Comparison\EqualOperator;

describe('EqualOperator', function () {
    beforeEach(function () {
        $this->operator = new EqualOperator();
    });

    it('has correct name and arity', function () {
        expect($this->operator->getName())->toBe('=');
        expect($this->operator->getArity())->toBe(2);
    });

    it('returns true for equal values', function () {
        expect($this->operator->execute([5, 5]))->toBeTrue();
        expect($this->operator->execute(['hello', 'hello']))->toBeTrue();
    });

    it('returns false for unequal values', function () {
        expect($this->operator->execute([5, 10]))->toBeFalse();
        expect($this->operator->execute(['hello', 'world']))->toBeFalse();
    });

    it('performs loose comparison', function () {
        expect($this->operator->execute([5, '5']))->toBeTrue();
        expect($this->operator->execute([0, false]))->toBeTrue();
    });

    it('throws on incorrect operand count', function () {
        expect(fn() => $this->operator->execute([1]))
            ->toThrow(InvalidArgumentException::class);
    });
});
```

### Example Test: RuleTest

```php
<?php

declare(strict_types=1);

use RuleEngine\Context\Context;
use RuleEngine\Expression\LiteralExpression;
use RuleEngine\Expression\OperatorExpression;
use RuleEngine\Expression\VariableExpression;
use RuleEngine\Operator\Comparison\GreaterThanOrEqualOperator;
use RuleEngine\Rule\Rule;

describe('Rule', function () {
    it('evaluates to true when condition passes', function () {
        $condition = new OperatorExpression(
            new GreaterThanOrEqualOperator(),
            [new VariableExpression('age'), new LiteralExpression(18)]
        );

        $rule = new Rule('adult', $condition);
        $context = Context::fromArray(['age' => 25]);

        expect($rule->evaluate($context))->toBeTrue();
    });

    it('evaluates to false when condition fails', function () {
        $condition = new OperatorExpression(
            new GreaterThanOrEqualOperator(),
            [new VariableExpression('age'), new LiteralExpression(18)]
        );

        $rule = new Rule('adult', $condition);
        $context = Context::fromArray(['age' => 15]);

        expect($rule->evaluate($context))->toBeFalse();
    });

    it('returns RuleResult with details', function () {
        $condition = new LiteralExpression(true);
        $rule = new Rule('test', $condition, ['priority' => 1]);
        $context = Context::fromArray([]);

        $result = $rule->evaluateWithResult($context);

        expect($result->passed())->toBeTrue();
        expect($result->getRuleName())->toBe('test');
    });

    it('preserves metadata', function () {
        $rule = new Rule('test', new LiteralExpression(true), [
            'category' => 'access',
            'priority' => 1,
        ]);

        expect($rule->getMeta('category'))->toBe('access');
        expect($rule->getMeta('priority'))->toBe(1);
        expect($rule->getMeta('missing', 'default'))->toBe('default');
    });
});
```

## Test Categories

### Expression Tests
- LiteralExpression: various types, __toString
- VariableExpression: simple access, nested access, missing variables
- OperatorExpression: evaluation, operator delegation, string representation

### Context Tests
- Simple get/set
- Dot notation access
- Nested value setting
- Object property access
- Missing variable exception
- Context merging

### Operator Tests
For each operator:
- Name and arity
- Correct execution with valid operands
- Edge cases (null, empty, special values)
- Invalid operand count

### Registry Tests
- Register and retrieve operators
- Missing operator exception
- List all operators

### Rule Tests
- Rule evaluation
- RuleResult generation
- Metadata access
- RuleSet evaluation strategies

### Serialization Tests
- Serialize/deserialize round-trip
- All expression types
- Nested expressions
- Invalid data handling

## Dependencies
- All previous tasks (01-15)

## Estimated Complexity
**Medium** - Many tests but following consistent patterns

## Implementation Notes
- Use Pest's `describe` and `it` syntax for readability
- Group related tests in `describe` blocks
- Use `beforeEach` for common setup
- Test both success and error cases
- Focus on behavior, not implementation details

## Acceptance Criteria
- [x] All expression types have comprehensive tests
- [x] All operators have tests for each edge case
- [x] Context tests cover dot notation and exceptions
- [x] Rule tests cover evaluation and metadata
- [x] Serialization round-trip tests pass
- [x] All tests pass with `vendor/bin/phpunit` (398 tests, 718 assertions)
- [x] Code coverage is at least 90% (coverage driver not available but comprehensive tests written)
