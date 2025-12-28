#!/usr/bin/env php
<?php

/**
 * E-commerce Discount Rules Example
 *
 * This example demonstrates how to use the rule engine for e-commerce discount logic.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use RuleEngine\RuleEngine;

echo "=== E-commerce Discount Rules Example ===\n\n";

$engine = RuleEngine::create();

// Define discount rules
$engine->addRule(
    $engine->builder()
        ->name('loyalty_discount')
        ->when('customer.tier')->in(['gold', 'platinum'])
        ->andWhen('order.total')->greaterThan(100)
        ->then()
        ->meta('discount', 0.15)
        ->meta('priority', 1)
        ->meta('message', '15% loyalty discount for premium members')
        ->build()
);

$engine->addRule(
    $engine->builder()
        ->name('new_customer_discount')
        ->when('customer.orders_count')->equals(0)
        ->andWhen('order.total')->greaterThan(50)
        ->then()
        ->meta('discount', 0.10)
        ->meta('priority', 2)
        ->meta('message', 'Welcome! 10% first order discount')
        ->build()
);

$engine->addRule(
    $engine->builder()
        ->name('bulk_discount')
        ->when('order.items')->greaterThanOrEqual(10)
        ->then()
        ->meta('discount', 0.20)
        ->meta('priority', 1)
        ->meta('message', '20% bulk order discount')
        ->build()
);

$engine->addRule(
    $engine->builder()
        ->name('seasonal_sale')
        ->when('order.total')->greaterThan(75)
        ->then()
        ->meta('discount', 0.08)
        ->meta('priority', 3)
        ->meta('message', 'Seasonal sale: 8% off orders over $75')
        ->build()
);

$engine->addRule(
    $engine->builder()
        ->name('free_shipping')
        ->when('order.total')->greaterThanOrEqual(100)
        ->then()
        ->meta('discount', 0)
        ->meta('free_shipping', true)
        ->meta('message', 'Free shipping on orders $100+')
        ->build()
);

// Test scenarios
$scenarios = [
    [
        'name' => 'Gold Member Large Order',
        'context' => [
            'customer' => [
                'tier' => 'gold',
                'orders_count' => 5,
            ],
            'order' => [
                'total' => 150,
                'items' => 3,
            ],
        ],
    ],
    [
        'name' => 'New Customer',
        'context' => [
            'customer' => [
                'tier' => 'standard',
                'orders_count' => 0,
            ],
            'order' => [
                'total' => 75,
                'items' => 2,
            ],
        ],
    ],
    [
        'name' => 'Bulk Order',
        'context' => [
            'customer' => [
                'tier' => 'standard',
                'orders_count' => 3,
            ],
            'order' => [
                'total' => 200,
                'items' => 15,
            ],
        ],
    ],
];

foreach ($scenarios as $scenario) {
    echo "Scenario: {$scenario['name']}\n";
    echo str_repeat('-', 50) . "\n";

    $context = $scenario['context'];
    $orderTotal = $context['order']['total'];

    echo "Customer Tier: {$context['customer']['tier']}\n";
    echo "Previous Orders: {$context['customer']['orders_count']}\n";
    echo "Order Total: \${$orderTotal}\n";
    echo "Items: {$context['order']['items']}\n\n";

    $passingRules = $engine->getPassingRules($context);

    if (empty($passingRules)) {
        echo "No discounts apply.\n";
        echo "Final Total: \${$orderTotal}\n\n";
        continue;
    }

    $totalDiscount = 0;
    $hasFreeShipping = false;

    echo "Applied Discounts:\n";
    foreach ($passingRules as $rule) {
        $discount = $rule->getMeta('discount', 0);
        $message = $rule->getMeta('message', '');

        if ($rule->getMeta('free_shipping', false)) {
            $hasFreeShipping = true;
        }

        $totalDiscount += $discount;
        echo "- {$message}\n";
    }

    $discountAmount = $orderTotal * $totalDiscount;
    $finalTotal = $orderTotal - $discountAmount;

    echo "\nCalculation:\n";
    echo "Subtotal: \${$orderTotal}\n";
    echo 'Total Discount: ' . ($totalDiscount * 100) . "%\n";
    echo 'Discount Amount: -$' . number_format($discountAmount, 2) . "\n";
    echo 'Final Total: $' . number_format($finalTotal, 2) . "\n";

    if ($hasFreeShipping) {
        echo "Shipping: FREE\n";
    }

    echo "\n\n";
}

echo "=== End of E-commerce Example ===\n";
