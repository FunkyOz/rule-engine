#!/usr/bin/env php
<?php

/**
 * Access Control Example
 *
 * This example demonstrates using the rule engine for role-based access control.
 */

require_once __DIR__.'/../vendor/autoload.php';

use RuleEngine\RuleEngine;

echo "=== Access Control Example ===\n\n";

$engine = RuleEngine::create();

// Define access control rules
$engine->addRule(
    $engine->builder()
        ->name('admin_full_access')
        ->when('user.role')->equals('admin')
        ->then()
        ->meta('permissions', ['create', 'read', 'update', 'delete', 'admin'])
        ->build()
);

$engine->addRule(
    $engine->builder()
        ->name('can_edit_post')
        ->when('user.role')->in(['admin', 'editor'])
        ->andWhen('post.status')->notEquals('published')
        ->then()
        ->meta('permissions', ['read', 'update'])
        ->build()
);

$engine->addRule(
    $engine->builder()
        ->name('can_edit_own_draft')
        ->when('user.id')->equals('$post.author_id')
        ->andWhen('post.status')->equals('draft')
        ->then()
        ->meta('permissions', ['read', 'update'])
        ->build()
);

$engine->addRule(
    $engine->builder()
        ->name('can_view_published')
        ->when('post.status')->equals('published')
        ->then()
        ->meta('permissions', ['read'])
        ->build()
);

$engine->addRule(
    $engine->builder()
        ->name('can_delete_own_post')
        ->when('user.id')->equals('$post.author_id')
        ->then()
        ->meta('permissions', ['delete'])
        ->build()
);

// Test scenarios
$scenarios = [
    [
        'name' => 'Admin accessing any post',
        'user' => ['id' => 1, 'role' => 'admin'],
        'post' => ['id' => 100, 'author_id' => 50, 'status' => 'published'],
    ],
    [
        'name' => 'Editor accessing draft post',
        'user' => ['id' => 2, 'role' => 'editor'],
        'post' => ['id' => 101, 'author_id' => 30, 'status' => 'draft'],
    ],
    [
        'name' => 'Author accessing own draft',
        'user' => ['id' => 3, 'role' => 'author'],
        'post' => ['id' => 102, 'author_id' => 3, 'status' => 'draft'],
    ],
    [
        'name' => 'Regular user viewing published post',
        'user' => ['id' => 4, 'role' => 'user'],
        'post' => ['id' => 103, 'author_id' => 10, 'status' => 'published'],
    ],
    [
        'name' => 'Editor trying to edit published post',
        'user' => ['id' => 5, 'role' => 'editor'],
        'post' => ['id' => 104, 'author_id' => 20, 'status' => 'published'],
    ],
];

foreach ($scenarios as $scenario) {
    echo "Scenario: {$scenario['name']}\n";
    echo str_repeat('-', 50)."\n";

    $context = [
        'user' => $scenario['user'],
        'post' => $scenario['post'],
    ];

    echo "User: ID={$context['user']['id']}, Role={$context['user']['role']}\n";
    echo "Post: ID={$context['post']['id']}, Author={$context['post']['author_id']}, Status={$context['post']['status']}\n\n";

    $passingRules = $engine->getPassingRules($context);

    if (empty($passingRules)) {
        echo "Access: DENIED - No permissions granted\n\n\n";

        continue;
    }

    // Collect all unique permissions
    $permissions = [];
    foreach ($passingRules as $rule) {
        $rulePermissions = $rule->getMeta('permissions', []);
        $permissions = array_unique(array_merge($permissions, $rulePermissions));
    }

    echo "Access: GRANTED\n";
    echo "Matching Rules:\n";
    foreach ($passingRules as $rule) {
        echo "- {$rule->getName()}\n";
    }

    echo "\nPermissions:\n";
    foreach ($permissions as $permission) {
        echo '- '.strtoupper($permission)."\n";
    }

    // Check specific actions
    echo "\nCan perform:\n";
    echo '- Create: '.(in_array('create', $permissions) ? 'Yes' : 'No')."\n";
    echo '- Read: '.(in_array('read', $permissions) ? 'Yes' : 'No')."\n";
    echo '- Update: '.(in_array('update', $permissions) ? 'Yes' : 'No')."\n";
    echo '- Delete: '.(in_array('delete', $permissions) ? 'Yes' : 'No')."\n";

    echo "\n\n";
}

echo "=== End of Access Control Example ===\n";
