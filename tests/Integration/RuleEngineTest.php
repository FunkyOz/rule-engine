<?php

declare(strict_types=1);

use RuleEngine\RuleEngine;

beforeEach(function (): void {
    $this->engine = RuleEngine::create();
});

test('evaluates rules with array context', function (): void {
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

test('evaluates multiple rules with evaluateAll', function (): void {
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

test('returns passing and failing rules', function (): void {
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

    expect($passing)->toHaveKey('rule1');
    expect($passing)->toHaveCount(1);

    expect($failing)->toHaveKey('rule2');
    expect($failing)->toHaveCount(1);
});

test('evaluateAny returns true when any rule passes', function (): void {
    $this->engine->addRule(
        $this->engine->builder()
            ->name('rule1')
            ->when('value')->greaterThan(100)
            ->then()
            ->build()
    );

    $this->engine->addRule(
        $this->engine->builder()
            ->name('rule2')
            ->when('value')->lessThan(50)
            ->then()
            ->build()
    );

    // value = 30: rule1 fails, rule2 passes
    expect($this->engine->evaluateAny(['value' => 30]))->toBeTrue();

    // value = 75: both fail
    expect($this->engine->evaluateAny(['value' => 75]))->toBeFalse();
});

test('complex nested context access', function (): void {
    $this->engine->addRule(
        $this->engine->builder()
            ->name('location_check')
            ->when('user.location.country')->equals('USA')
            ->andWhen('user.location.state')->equals('CA')
            ->then()
            ->build()
    );

    $context = [
        'user' => [
            'location' => [
                'country' => 'USA',
                'state' => 'CA',
            ],
        ],
    ];

    expect($this->engine->evaluate('location_check', $context))->toBeTrue();

    $context['user']['location']['state'] = 'NY';
    expect($this->engine->evaluate('location_check', $context))->toBeFalse();
});

test('rules with metadata', function (): void {
    $this->engine->addRule(
        $this->engine->builder()
            ->name('premium_discount')
            ->when('customer.tier')->equals('premium')
            ->then()
            ->meta('discount', 0.20)
            ->meta('description', '20% discount for premium members')
            ->build()
    );

    $passing = $this->engine->getPassingRules(['customer' => ['tier' => 'premium']]);

    expect($passing)->toHaveKey('premium_discount');

    $rule = $passing['premium_discount'];
    expect($rule->getMeta('discount'))->toBe(0.20);
    expect($rule->getMeta('description'))->toBe('20% discount for premium members');
});

test('evaluateAllWithResults', function (): void {
    $this->engine->addRule(
        $this->engine->builder()
            ->name('rule1')
            ->when('value')->greaterThan(10)
            ->then()
            ->meta('priority', 1)
            ->build()
    );

    $this->engine->addRule(
        $this->engine->builder()
            ->name('rule2')
            ->when('value')->lessThan(100)
            ->then()
            ->meta('priority', 2)
            ->build()
    );

    $results = $this->engine->evaluateAllWithResults(['value' => 50]);

    expect($results)->toHaveCount(2);
    expect($results['rule1']->passed())->toBeTrue();
    expect($results['rule2']->passed())->toBeTrue();

    // Check metadata is preserved
    expect($results['rule1']->getRule()->getMeta('priority'))->toBe(1);
    expect($results['rule2']->getRule()->getMeta('priority'))->toBe(2);
});

test('multiple conditions with set operators', function (): void {
    $this->engine->addRule(
        $this->engine->builder()
            ->name('allowed_role')
            ->when('user.role')->in(['admin', 'moderator', 'editor'])
            ->andWhen('user.status')->equals('active')
            ->then()
            ->build()
    );

    // Both conditions pass
    expect($this->engine->evaluate('allowed_role', [
        'user' => ['role' => 'admin', 'status' => 'active'],
    ]))->toBeTrue();

    // Role not in list
    expect($this->engine->evaluate('allowed_role', [
        'user' => ['role' => 'user', 'status' => 'active'],
    ]))->toBeFalse();

    // Status not active
    expect($this->engine->evaluate('allowed_role', [
        'user' => ['role' => 'admin', 'status' => 'suspended'],
    ]))->toBeFalse();
});

test('string operators in rules', function (): void {
    $this->engine->addRule(
        $this->engine->builder()
            ->name('corporate_email')
            ->when('email')->endsWith('@company.com')
            ->then()
            ->build()
    );

    expect($this->engine->evaluate('corporate_email', [
        'email' => 'john.doe@company.com',
    ]))->toBeTrue();

    expect($this->engine->evaluate('corporate_email', [
        'email' => 'john.doe@gmail.com',
    ]))->toBeFalse();
});

test('regex matching', function (): void {
    $this->engine->addRule(
        $this->engine->builder()
            ->name('valid_email')
            ->when('email')->matches('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/')
            ->then()
            ->build()
    );

    expect($this->engine->evaluate('valid_email', [
        'email' => 'test@example.com',
    ]))->toBeTrue();

    expect($this->engine->evaluate('valid_email', [
        'email' => 'invalid-email',
    ]))->toBeFalse();
});

test('dynamic rule addition and removal', function (): void {
    $rule = $this->engine->builder()
        ->name('test_rule')
        ->when('value')->equals(42)
        ->then()
        ->build();

    $this->engine->addRule($rule);
    expect($this->engine->hasRule('test_rule'))->toBeTrue();

    expect($this->engine->evaluate('test_rule', ['value' => 42]))->toBeTrue();

    $this->engine->removeRule('test_rule');
    expect($this->engine->hasRule('test_rule'))->toBeFalse();
});

test('complete workflow with multiple rules', function (): void {
    // Add discount eligibility rules
    $this->engine->addRules([
        $this->engine->builder()
            ->name('loyalty_discount')
            ->when('customer.loyaltyPoints')->greaterThanOrEqual(1000)
            ->then()
            ->meta('discount', 0.10)
            ->build(),

        $this->engine->builder()
            ->name('bulk_discount')
            ->when('order.quantity')->greaterThanOrEqual(50)
            ->then()
            ->meta('discount', 0.15)
            ->build(),

        $this->engine->builder()
            ->name('premium_discount')
            ->when('customer.tier')->equals('premium')
            ->then()
            ->meta('discount', 0.20)
            ->build(),
    ]);

    $context = [
        'customer' => [
            'loyaltyPoints' => 1500,
            'tier' => 'premium',
        ],
        'order' => [
            'quantity' => 60,
        ],
    ];

    // All three discounts should apply
    $passing = $this->engine->getPassingRules($context);
    expect($passing)->toHaveCount(3);

    // Calculate total discount (can be used in application logic)
    $totalDiscount = 0;
    foreach ($passing as $rule) {
        $totalDiscount += $rule->getMeta('discount', 0);
    }
    expect($totalDiscount)->toBe(0.45);
});
