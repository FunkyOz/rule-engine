#!/usr/bin/env php
<?php

/**
 * Basic Usage Example
 *
 * This example demonstrates the fundamental features of the PHP Rule Engine.
 */

require_once __DIR__.'/../vendor/autoload.php';

use RuleEngine\RuleEngine;

echo "=== Basic Usage Example ===\n\n";

// Create the engine
$engine = RuleEngine::create();

// Example 1: Simple comparison
echo "Example 1: Age verification\n";
$engine->addRule(
    $engine->builder()
        ->name('is_adult')
        ->when('age')->greaterThanOrEqual(18)
        ->then()
        ->build()
);

$context1 = ['age' => 25];
$result1 = $engine->evaluate('is_adult', $context1);
echo "Age: {$context1['age']}, Is adult: ".($result1 ? 'Yes' : 'No')."\n\n";

// Example 2: Multiple conditions
echo "Example 2: Premium member check\n";
$engine->addRule(
    $engine->builder()
        ->name('premium_member')
        ->when('user.tier')->equals('premium')
        ->andWhen('user.active')->equals(true)
        ->then()
        ->build()
);

$context2 = [
    'user' => [
        'tier' => 'premium',
        'active' => true,
    ],
];

$result2 = $engine->evaluate('premium_member', $context2);
echo "Tier: {$context2['user']['tier']}, Active: ".($context2['user']['active'] ? 'Yes' : 'No')."\n";
echo 'Is premium member: '.($result2 ? 'Yes' : 'No')."\n\n";

// Example 3: Using IN operator
echo "Example 3: Role-based access\n";
$engine->addRule(
    $engine->builder()
        ->name('has_admin_access')
        ->when('role')->in(['admin', 'superadmin'])
        ->then()
        ->build()
);

$context3 = ['role' => 'admin'];
$result3 = $engine->evaluate('has_admin_access', $context3);
echo "Role: {$context3['role']}, Has admin access: ".($result3 ? 'Yes' : 'No')."\n\n";

// Example 4: String operations
echo "Example 4: Email domain validation\n";
$engine->addRule(
    $engine->builder()
        ->name('company_email')
        ->when('email')->endsWith('@company.com')
        ->then()
        ->build()
);

$context4 = ['email' => 'john@company.com'];
$result4 = $engine->evaluate('company_email', $context4);
echo "Email: {$context4['email']}, Is company email: ".($result4 ? 'Yes' : 'No')."\n\n";

// Example 5: Using metadata
echo "Example 5: Discount rules with metadata\n";
$engine->addRule(
    $engine->builder()
        ->name('loyalty_discount')
        ->when('customer.points')->greaterThan(1000)
        ->then()
        ->meta('discount', 0.15)
        ->meta('message', '15% loyalty discount applied!')
        ->build()
);

$context5 = [
    'customer' => [
        'points' => 1500,
    ],
];

$result5 = $engine->evaluateWithResult('loyalty_discount', $context5);
if ($result5->passed()) {
    $rule = $engine->getRule('loyalty_discount');
    $discount = $rule->getMeta('discount');
    $message = $rule->getMeta('message');

    echo "Customer points: {$context5['customer']['points']}\n";
    echo 'Discount: '.($discount * 100)."%\n";
    echo "Message: {$message}\n";
}
echo "\n";

// Example 6: Evaluating multiple rules
echo "Example 6: Evaluating all rules\n";
// Create a new engine instance to avoid conflicts with previous rules
$validationEngine = RuleEngine::create();

$validationEngine->addRule(
    $validationEngine->builder()
        ->name('email_required')
        ->when('email')->notEquals('')
        ->then()
        ->build()
);

$validationEngine->addRule(
    $validationEngine->builder()
        ->name('age_valid')
        ->when('age')->greaterThanOrEqual(0)
        ->lessThanOrEqual(150)
        ->then()
        ->build()
);

$context6 = [
    'email' => 'test@example.com',
    'age' => 25,
];

$allPass = $validationEngine->evaluateAll($context6);
echo 'All validation rules pass: '.($allPass ? 'Yes' : 'No')."\n";

$passingRules = $validationEngine->getPassingRules($context6);
echo "Passing rules:\n";
foreach ($passingRules as $rule) {
    echo "- {$rule->getName()}\n";
}

echo "\n=== End of Basic Usage Example ===\n";
