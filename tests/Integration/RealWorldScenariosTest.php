<?php

declare(strict_types=1);

use RuleEngine\RuleEngine;

test('ecommerce discount rules', function (): void {
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
    expect($passing)->toHaveKey('loyalty_discount');
    expect($passing['loyalty_discount']->getMeta('discount'))->toBe(0.15);
});

test('access control rules', function (): void {
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

test('validation rules', function (): void {
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

test('pricing rules', function (): void {
    $engine = RuleEngine::create();

    // Standard pricing
    $engine->addRule(
        $engine->builder()
            ->name('standard_price')
            ->when('customer.type')->equals('regular')
            ->then()
            ->meta('price_multiplier', 1.0)
            ->build()
    );

    // Volume discount
    $engine->addRule(
        $engine->builder()
            ->name('volume_discount')
            ->when('order.quantity')->greaterThanOrEqual(100)
            ->then()
            ->meta('discount', 0.10)
            ->meta('reason', 'Volume discount')
            ->build()
    );

    // Premium customer discount
    $engine->addRule(
        $engine->builder()
            ->name('premium_customer')
            ->when('customer.type')->equals('premium')
            ->then()
            ->meta('discount', 0.20)
            ->meta('reason', 'Premium customer')
            ->build()
    );

    // Enterprise customer gets both premium and volume discounts
    $context = [
        'customer' => ['type' => 'premium'],
        'order' => ['quantity' => 150],
    ];

    $passing = $engine->getPassingRules($context);
    expect($passing)->toHaveCount(2); // volume_discount + premium_customer

    $totalDiscount = 0;
    foreach ($passing as $rule) {
        $totalDiscount += $rule->getMeta('discount', 0);
    }
    expect($totalDiscount)->toEqualWithDelta(0.30, 0.0001);
});

test('feature flag rules', function (): void {
    $engine = RuleEngine::create();

    $engine->addRule(
        $engine->builder()
            ->name('new_ui_feature')
            ->when('user.betaTester')->equals(true)
            ->then()
            ->meta('feature', 'new_ui')
            ->meta('enabled', true)
            ->build()
    );

    $engine->addRule(
        $engine->builder()
            ->name('experimental_feature')
            ->when('user.role')->equals('admin')
            ->andWhen('environment')->equals('staging')
            ->then()
            ->meta('feature', 'experimental')
            ->meta('enabled', true)
            ->build()
    );

    // Beta tester gets new UI
    $betaUser = ['user' => ['betaTester' => true, 'role' => 'user'], 'environment' => 'production'];
    expect($engine->evaluate('new_ui_feature', $betaUser))->toBeTrue();

    // Admin in staging gets experimental feature
    $adminStaging = ['user' => ['betaTester' => false, 'role' => 'admin'], 'environment' => 'staging'];
    expect($engine->evaluate('experimental_feature', $adminStaging))->toBeTrue();

    // Admin in production doesn't get experimental feature
    $adminProd = ['user' => ['betaTester' => false, 'role' => 'admin'], 'environment' => 'production'];
    expect($engine->evaluate('experimental_feature', $adminProd))->toBeFalse();
});

test('content moderation rules', function (): void {
    $engine = RuleEngine::create();

    $engine->addRule(
        $engine->builder()
            ->name('auto_approve')
            ->when('user.reputation')->greaterThanOrEqual(1000)
            ->andWhen('content.length')->lessThan(5000)
            ->then()
            ->meta('action', 'approve')
            ->build()
    );

    $engine->addRule(
        $engine->builder()
            ->name('requires_review')
            ->when('user.reputation')->lessThan(100)
            ->then()
            ->meta('action', 'review')
            ->build()
    );

    $engine->addRule(
        $engine->builder()
            ->name('spam_check')
            ->when('content.links')->greaterThan(5)
            ->then()
            ->meta('action', 'flag_spam')
            ->build()
    );

    // Trusted user with short content
    $trustedUser = [
        'user' => ['reputation' => 1500],
        'content' => ['length' => 500, 'links' => 1],
    ];
    $passing = $engine->getPassingRules($trustedUser);
    expect($passing)->toHaveKey('auto_approve');

    // New user requires review
    $newUser = [
        'user' => ['reputation' => 50],
        'content' => ['length' => 500, 'links' => 1],
    ];
    $passing = $engine->getPassingRules($newUser);
    expect($passing)->toHaveKey('requires_review');

    // Too many links triggers spam check
    $spamContent = [
        'user' => ['reputation' => 500],
        'content' => ['length' => 500, 'links' => 10],
    ];
    $passing = $engine->getPassingRules($spamContent);
    expect($passing)->toHaveKey('spam_check');
});

test('shipping rules', function (): void {
    $engine = RuleEngine::create();

    $engine->addRule(
        $engine->builder()
            ->name('free_shipping')
            ->when('order.total')->greaterThanOrEqual(50)
            ->then()
            ->meta('shipping_cost', 0)
            ->meta('message', 'Free shipping!')
            ->build()
    );

    $engine->addRule(
        $engine->builder()
            ->name('express_available')
            ->when('order.total')->greaterThanOrEqual(25)
            ->andWhen('delivery.zip')->startsWith('9')
            ->then()
            ->meta('express_available', true)
            ->build()
    );

    $engine->addRule(
        $engine->builder()
            ->name('international_shipping')
            ->when('delivery.country')->notEquals('USA')
            ->then()
            ->meta('shipping_cost', 25)
            ->meta('message', 'International shipping')
            ->build()
    );

    // Qualifies for free shipping
    $domesticOrder = [
        'order' => ['total' => 75],
        'delivery' => ['country' => 'USA', 'zip' => '90210'],
    ];
    $passing = $engine->getPassingRules($domesticOrder);
    expect($passing)->toHaveKey('free_shipping');
    expect($passing)->toHaveKey('express_available');

    // International shipping
    $internationalOrder = [
        'order' => ['total' => 75],
        'delivery' => ['country' => 'Canada', 'zip' => 'M5V'],
    ];
    $passing = $engine->getPassingRules($internationalOrder);
    expect($passing)->toHaveKey('international_shipping');
});

test('subscription eligibility rules', function (): void {
    $engine = RuleEngine::create();

    $engine->addRule(
        $engine->builder()
            ->name('student_discount')
            ->when('user.type')->equals('student')
            ->andWhen('user.verified')->equals(true)
            ->then()
            ->meta('discount', 0.50)
            ->meta('plan', 'student')
            ->build()
    );

    $engine->addRule(
        $engine->builder()
            ->name('enterprise_plan')
            ->when('company.employees')->greaterThanOrEqual(100)
            ->then()
            ->meta('plan', 'enterprise')
            ->meta('features', ['sso', 'advanced_analytics', 'priority_support'])
            ->build()
    );

    $engine->addRule(
        $engine->builder()
            ->name('trial_eligible')
            ->when('user.previousSubscriber')->equals(false)
            ->then()
            ->meta('trial_days', 30)
            ->build()
    );

    // Verified student
    $student = [
        'user' => ['type' => 'student', 'verified' => true, 'previousSubscriber' => false],
        'company' => ['employees' => 10],
    ];
    $passing = $engine->getPassingRules($student);
    expect($passing)->toHaveKey('student_discount');
    expect($passing)->toHaveKey('trial_eligible');
    expect($passing)->not->toHaveKey('enterprise_plan');

    // Large company
    $enterprise = [
        'company' => ['employees' => 500],
        'user' => ['type' => 'business', 'verified' => true, 'previousSubscriber' => false],
    ];
    $passing = $engine->getPassingRules($enterprise);
    expect($passing)->toHaveKey('enterprise_plan');
    expect($passing)->toHaveKey('trial_eligible');
});

test('complex business workflow', function (): void {
    $engine = RuleEngine::create();

    // Loan approval workflow
    $engine->addRules([
        $engine->builder()
            ->name('income_check')
            ->when('applicant.income')->greaterThanOrEqual(50000)
            ->then()
            ->meta('score', 30)
            ->build(),

        $engine->builder()
            ->name('credit_score_check')
            ->when('applicant.creditScore')->greaterThanOrEqual(700)
            ->then()
            ->meta('score', 40)
            ->build(),

        $engine->builder()
            ->name('employment_check')
            ->when('applicant.employmentYears')->greaterThanOrEqual(2)
            ->then()
            ->meta('score', 20)
            ->build(),

        $engine->builder()
            ->name('debt_ratio_check')
            ->when('applicant.debtRatio')->lessThan(0.4)
            ->then()
            ->meta('score', 10)
            ->build(),
    ]);

    $goodApplicant = [
        'applicant' => [
            'income' => 75000,
            'creditScore' => 750,
            'employmentYears' => 5,
            'debtRatio' => 0.25,
        ],
    ];

    $passing = $engine->getPassingRules($goodApplicant);
    expect($passing)->toHaveCount(4);

    // Calculate total score
    $totalScore = 0;
    foreach ($passing as $rule) {
        $totalScore += $rule->getMeta('score', 0);
    }
    expect($totalScore)->toBe(100);

    // Poor applicant
    $poorApplicant = [
        'applicant' => [
            'income' => 30000,
            'creditScore' => 600,
            'employmentYears' => 0.5,
            'debtRatio' => 0.6,
        ],
    ];

    $passing = $engine->getPassingRules($poorApplicant);
    expect($passing)->toHaveCount(0);
});
