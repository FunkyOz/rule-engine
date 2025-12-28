---
title: Integration Tests
status: done
priority: High
description: Implement integration tests for end-to-end rule engine functionality
---

## Objectives
- Test complete rule evaluation workflows
- Test complex expression combinations
- Test real-world use case scenarios
- Verify all components work together correctly

## Deliverables
1. `tests/Integration/RuleEngineTest.php`
2. `tests/Integration/ComplexRulesTest.php`
3. `tests/Integration/SerializationRoundTripTest.php`
4. `tests/Integration/RealWorldScenariosTest.php`

## Technical Details

### RuleEngineTest

Test the main facade with complete workflows.

```php
<?php

declare(strict_types=1);

use RuleEngine\RuleEngine;

describe('RuleEngine Integration', function () {
    beforeEach(function () {
        $this->engine = RuleEngine::create();
    });

    it('evaluates rules with array context', function () {
        $this->engine->addRule(
            $this->engine->builder()
                ->name('adult')
                ->when('age')->greaterThanOrEqual(18)
                ->then()
                ->build()
        );

        expect($this->engine->evaluate('adult', ['age' => 25]))->toBeTrue();
        expect($this->engine->evaluate('adult', ['age' => 15]))->toBeFalse();
    });

    it('evaluates multiple rules with evaluateAll', function () {
        $this->engine->addRule(
            $this->engine->builder()
                ->name('adult')
                ->when('user.age')->greaterThanOrEqual(18)
                ->then()
                ->build()
        );

        $this->engine->addRule(
            $this->engine->builder()
                ->name('verified')
                ->when('user.verified')->equals(true)
                ->then()
                ->build()
        );

        $context = [
            'user' => [
                'age' => 25,
                'verified' => true,
            ],
        ];

        expect($this->engine->evaluateAll($context))->toBeTrue();

        $context['user']['verified'] = false;
        expect($this->engine->evaluateAll($context))->toBeFalse();
    });

    it('returns passing and failing rules', function () {
        $this->engine->addRule(
            $this->engine->builder()
                ->name('rule1')
                ->when('value')->greaterThan(10)
                ->then()
                ->build()
        );

        $this->engine->addRule(
            $this->engine->builder()
                ->name('rule2')
                ->when('value')->lessThan(5)
                ->then()
                ->build()
        );

        $passing = $this->engine->getPassingRules(['value' => 15]);
        $failing = $this->engine->getFailingRules(['value' => 15]);

        expect(array_keys($passing))->toBe(['rule1']);
        expect(array_keys($failing))->toBe(['rule2']);
    });
});
```

### ComplexRulesTest

Test complex expression combinations.

```php
<?php

declare(strict_types=1);

use RuleEngine\Context\Context;
use RuleEngine\Expression\LiteralExpression;
use RuleEngine\Expression\OperatorExpression;
use RuleEngine\Expression\VariableExpression;
use RuleEngine\Operator\Comparison\GreaterThanOperator;
use RuleEngine\Operator\Logical\AndOperator;
use RuleEngine\Operator\Logical\OrOperator;
use RuleEngine\Operator\Math\AddOperator;
use RuleEngine\Operator\Set\InOperator;
use RuleEngine\Rule\Rule;

describe('Complex Rules', function () {
    it('evaluates nested AND/OR conditions', function () {
        // (age > 18 AND verified) OR role IN ['admin', 'moderator']
        $ageCheck = new OperatorExpression(
            new GreaterThanOperator(),
            [new VariableExpression('age'), new LiteralExpression(18)]
        );

        $verifiedCheck = new VariableExpression('verified');

        $ageAndVerified = new OperatorExpression(
            new AndOperator(),
            [$ageCheck, $verifiedCheck]
        );

        $roleCheck = new OperatorExpression(
            new InOperator(),
            [
                new VariableExpression('role'),
                new LiteralExpression(['admin', 'moderator']),
            ]
        );

        $condition = new OperatorExpression(
            new OrOperator(),
            [$ageAndVerified, $roleCheck]
        );

        $rule = new Rule('access', $condition);

        // Adult + verified = pass
        expect($rule->evaluate(Context::fromArray([
            'age' => 25,
            'verified' => true,
            'role' => 'user',
        ])))->toBeTrue();

        // Not adult but admin = pass
        expect($rule->evaluate(Context::fromArray([
            'age' => 16,
            'verified' => false,
            'role' => 'admin',
        ])))->toBeTrue();

        // Not adult, not verified, not admin = fail
        expect($rule->evaluate(Context::fromArray([
            'age' => 16,
            'verified' => false,
            'role' => 'user',
        ])))->toBeFalse();
    });

    it('evaluates math expressions in conditions', function () {
        // (price * quantity) + shipping > 100
        $subtotal = new OperatorExpression(
            new \RuleEngine\Operator\Math\MultiplyOperator(),
            [
                new VariableExpression('price'),
                new VariableExpression('quantity'),
            ]
        );

        $total = new OperatorExpression(
            new AddOperator(),
            [$subtotal, new VariableExpression('shipping')]
        );

        $condition = new OperatorExpression(
            new GreaterThanOperator(),
            [$total, new LiteralExpression(100)]
        );

        $rule = new Rule('free_shipping_eligible', $condition);

        expect($rule->evaluate(Context::fromArray([
            'price' => 25,
            'quantity' => 4,
            'shipping' => 10,
        ])))->toBeTrue(); // 25*4 + 10 = 110 > 100

        expect($rule->evaluate(Context::fromArray([
            'price' => 10,
            'quantity' => 2,
            'shipping' => 5,
        ])))->toBeFalse(); // 10*2 + 5 = 25 < 100
    });
});
```

### RealWorldScenariosTest

Test practical use cases.

```php
<?php

declare(strict_types=1);

use RuleEngine\RuleEngine;

describe('Real World Scenarios', function () {
    describe('E-commerce discount rules', function () {
        it('applies loyalty discount for premium members with high order value', function () {
            $engine = RuleEngine::create();

            $engine->addRule(
                $engine->builder()
                    ->name('loyalty_discount')
                    ->when('customer.tier')->in(['gold', 'platinum'])
                    ->andWhen('order.total')->greaterThan(100)
                    ->then()
                    ->meta('discount', 0.15)
                    ->meta('message', '15% loyalty discount')
                    ->build()
            );

            $context = [
                'customer' => ['tier' => 'gold'],
                'order' => ['total' => 150],
            ];

            $passing = $engine->getPassingRules($context);
            expect($passing)->toHaveCount(1);
            expect($passing['loyalty_discount']->getMeta('discount'))->toBe(0.15);
        });
    });

    describe('Access control rules', function () {
        it('determines user permissions based on role and status', function () {
            $engine = RuleEngine::create();

            $engine->addRule(
                $engine->builder()
                    ->name('can_edit')
                    ->when('user.role')->in(['admin', 'editor'])
                    ->andWhen('user.status')->equals('active')
                    ->then()
                    ->build()
            );

            $engine->addRule(
                $engine->builder()
                    ->name('can_delete')
                    ->when('user.role')->equals('admin')
                    ->then()
                    ->build()
            );

            $editor = [
                'user' => ['role' => 'editor', 'status' => 'active'],
            ];

            $admin = [
                'user' => ['role' => 'admin', 'status' => 'active'],
            ];

            expect($engine->evaluate('can_edit', $editor))->toBeTrue();
            expect($engine->evaluate('can_delete', $editor))->toBeFalse();

            expect($engine->evaluate('can_edit', $admin))->toBeTrue();
            expect($engine->evaluate('can_delete', $admin))->toBeTrue();
        });
    });

    describe('Validation rules', function () {
        it('validates email format and domain', function () {
            $engine = RuleEngine::create();

            $engine->addRule(
                $engine->builder()
                    ->name('valid_corporate_email')
                    ->when('email')->matches('/^[^@]+@[^@]+\.[^@]+$/')
                    ->andWhen('email')->endsWith('@company.com')
                    ->then()
                    ->build()
            );

            expect($engine->evaluate('valid_corporate_email', [
                'email' => 'john.doe@company.com',
            ]))->toBeTrue();

            expect($engine->evaluate('valid_corporate_email', [
                'email' => 'john.doe@gmail.com',
            ]))->toBeFalse();
        });
    });
});
```

### SerializationRoundTripTest

Test that rules survive serialization.

```php
<?php

declare(strict_types=1);

use RuleEngine\Context\Context;
use RuleEngine\Registry\OperatorRegistry;
use RuleEngine\RuleEngine;
use RuleEngine\Serialization\RuleDeserializer;
use RuleEngine\Serialization\RuleSerializer;

describe('Serialization Round Trip', function () {
    it('preserves rule behavior through serialization', function () {
        $engine = RuleEngine::create();

        $originalRule = $engine->builder()
            ->name('complex_rule')
            ->when('user.age')->greaterThanOrEqual(18)
            ->andWhen('user.subscription')->in(['premium', 'enterprise'])
            ->then()
            ->meta('priority', 1)
            ->build();

        $serializer = new RuleSerializer();
        $deserializer = new RuleDeserializer($engine->getRegistry());

        $json = $serializer->serializeRuleToJson($originalRule);
        $loadedRule = $deserializer->deserializeRuleFromJson($json);

        $context = Context::fromArray([
            'user' => [
                'age' => 25,
                'subscription' => 'premium',
            ],
        ]);

        expect($originalRule->evaluate($context))
            ->toBe($loadedRule->evaluate($context));
        expect($loadedRule->getName())->toBe('complex_rule');
        expect($loadedRule->getMeta('priority'))->toBe(1);
    });
});
```

## Test Scenarios

### RuleEngine Facade
- Creating engine with defaults
- Adding and evaluating rules
- Array context conversion
- Passing/failing rule retrieval
- Custom operator registration

### Complex Expressions
- Nested logical operators
- Math expressions in conditions
- Set operations with variables
- String matching with regex

### Real World Use Cases
- E-commerce discount rules
- Access control/authorization
- Form validation rules
- Pricing rules
- Feature flags

### Serialization
- Simple rules
- Complex nested rules
- Metadata preservation
- Error handling for invalid data

## Dependencies
- Task 16 - Unit Tests
- All implementation tasks (01-15)

## Estimated Complexity
**Medium** - Focus on realistic scenarios and edge cases

## Implementation Notes
- Integration tests should test complete workflows
- Use realistic data structures
- Test error conditions and edge cases
- Keep tests independent and repeatable

## Acceptance Criteria
- [x] RuleEngine facade tests pass
- [x] Complex expression combinations work
- [x] Real-world scenarios are covered
- [x] Serialization round-trip preserves behavior
- [x] All tests pass with `vendor/bin/phpunit` (436 tests, 844 assertions)
- [x] Tests are readable and maintainable
