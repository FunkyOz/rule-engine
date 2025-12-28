#!/usr/bin/env php
<?php

/**
 * Form Validation Example
 *
 * This example demonstrates using the rule engine for form validation.
 */

require_once __DIR__.'/../vendor/autoload.php';

use RuleEngine\RuleEngine;

echo "=== Form Validation Example ===\n\n";

$engine = RuleEngine::create();

// Define validation rules
$engine->addRule(
    $engine->builder()
        ->name('valid_email')
        ->when('email')->matches('/^[^\s@]+@[^\s@]+\.[^\s@]+$/')
        ->then()
        ->meta('field', 'email')
        ->meta('error', 'Invalid email format')
        ->build()
);

$engine->addRule(
    $engine->builder()
        ->name('valid_age')
        ->when('age')->greaterThanOrEqual(18)
        ->lessThanOrEqual(120)
        ->then()
        ->meta('field', 'age')
        ->meta('error', 'Age must be between 18 and 120')
        ->build()
);

$engine->addRule(
    $engine->builder()
        ->name('strong_password')
        ->when('password')->matches('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/')
        ->then()
        ->meta('field', 'password')
        ->meta('error', 'Password must be at least 8 characters with uppercase, lowercase, and number')
        ->build()
);

$engine->addRule(
    $engine->builder()
        ->name('valid_username')
        ->when('username')->matches('/^[a-zA-Z0-9_]{3,20}$/')
        ->then()
        ->meta('field', 'username')
        ->meta('error', 'Username must be 3-20 characters, alphanumeric and underscore only')
        ->build()
);

$engine->addRule(
    $engine->builder()
        ->name('valid_phone')
        ->when('phone')->matches('/^\+1-\d{3}-\d{3}-\d{4}$/')
        ->then()
        ->meta('field', 'phone')
        ->meta('error', 'Phone must be in format +1-555-123-4567')
        ->build()
);

// Test scenarios
$scenarios = [
    [
        'name' => 'Valid Form',
        'data' => [
            'email' => 'user@example.com',
            'age' => 25,
            'password' => 'MyP@ssw0rd',
            'username' => 'john_doe',
            'phone' => '+1-555-123-4567',
        ],
    ],
    [
        'name' => 'Invalid Email',
        'data' => [
            'email' => 'invalid-email',
            'age' => 30,
            'password' => 'ValidPass123',
            'username' => 'valid_user',
            'phone' => '+1-555-999-8888',
        ],
    ],
    [
        'name' => 'Weak Password',
        'data' => [
            'email' => 'test@example.com',
            'age' => 25,
            'password' => 'weak',
            'username' => 'testuser',
            'phone' => '+1-555-111-2222',
        ],
    ],
    [
        'name' => 'Multiple Errors',
        'data' => [
            'email' => 'bad-email',
            'age' => 15,
            'password' => '12345',
            'username' => 'ab',
            'phone' => '5551234567',
        ],
    ],
];

foreach ($scenarios as $scenario) {
    echo "Scenario: {$scenario['name']}\n";
    echo str_repeat('-', 50)."\n";

    $data = $scenario['data'];

    echo "Form Data:\n";
    foreach ($data as $field => $value) {
        if ($field === 'password') {
            echo "- {$field}: ".str_repeat('*', strlen($value))."\n";
        } else {
            echo "- {$field}: {$value}\n";
        }
    }
    echo "\n";

    $results = $engine->evaluateAllWithResults($data);

    $errors = [];
    foreach ($results as $result) {
        if ($result->failed()) {
            $rule = $engine->getRule($result->getRuleName());
            $field = $rule->getMeta('field', 'unknown');
            $error = $rule->getMeta('error', 'Validation failed');
            $errors[$field] = $error;
        }
    }

    if (empty($errors)) {
        echo "Validation: PASSED ✓\n";
        echo "Form is valid and ready to submit!\n";
    } else {
        echo "Validation: FAILED ✗\n";
        echo "Errors:\n";
        foreach ($errors as $field => $error) {
            echo "- {$field}: {$error}\n";
        }
    }

    echo "\n\n";
}

// Example: Interactive validation
echo "=== Interactive Validation ===\n";
echo "This shows how you might validate individual fields as users type.\n\n";

$testFields = [
    ['field' => 'email', 'value' => 'john@'],
    ['field' => 'email', 'value' => 'john@example'],
    ['field' => 'email', 'value' => 'john@example.com'],
];

foreach ($testFields as $test) {
    $field = $test['field'];
    $value = $test['value'];

    $result = $engine->evaluateWithResult('valid_email', [$field => $value]);

    echo "Input: '{$value}'\n";
    if ($result->passed()) {
        echo "Status: Valid ✓\n";
    } else {
        $rule = $engine->getRule('valid_email');
        echo "Status: Invalid ✗\n";
        echo "Error: {$rule->getMeta('error')}\n";
    }
    echo "\n";
}

echo "=== End of Form Validation Example ===\n";
