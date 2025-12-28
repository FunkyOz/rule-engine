# Usage Examples

This document provides real-world examples of using the PHP Rule Engine.

## Table of Contents

- [E-commerce Discount Rules](#e-commerce-discount-rules)
- [Access Control](#access-control)
- [Form Validation](#form-validation)
- [Content Filtering](#content-filtering)
- [User Eligibility](#user-eligibility)
- [Dynamic Pricing](#dynamic-pricing)
- [Storing Rules in Database](#storing-rules-in-database)

---

## E-commerce Discount Rules

A common use case is implementing discount rules based on customer data and order information.

```php
<?php

use RuleEngine\RuleEngine;

$engine = RuleEngine::create();

// Loyalty discount for premium members
$engine->addRule(
    $engine->builder()
        ->name('loyalty_discount')
        ->when('customer.tier')->in(['gold', 'platinum'])
        ->andWhen('order.total')->greaterThan(100)
        ->then()
        ->meta('discount', 0.15)
        ->meta('message', '15% loyalty discount applied!')
        ->build()
);

// First-time buyer discount
$engine->addRule(
    $engine->builder()
        ->name('new_customer_discount')
        ->when('customer.orders_count')->equals(0)
        ->andWhen('order.total')->greaterThan(50)
        ->then()
        ->meta('discount', 0.10)
        ->meta('message', 'Welcome! 10% first order discount!')
        ->build()
);

// Bulk order discount
$engine->addRule(
    $engine->builder()
        ->name('bulk_discount')
        ->when('order.items')->greaterThanOrEqual(10)
        ->then()
        ->meta('discount', 0.20)
        ->meta('message', '20% bulk order discount!')
        ->build()
);

// Seasonal promotion
$engine->addRule(
    $engine->builder()
        ->name('summer_sale')
        ->when('order.date')->greaterThanOrEqual('2024-06-01')
                            ->lessThanOrEqual('2024-08-31')
        ->andWhen('order.total')->greaterThan(75)
        ->then()
        ->meta('discount', 0.12)
        ->meta('message', 'Summer Sale: 12% off!')
        ->build()
);

// Evaluate and apply discounts
$context = [
    'customer' => [
        'tier' => 'gold',
        'orders_count' => 5,
    ],
    'order' => [
        'total' => 150,
        'items' => 3,
        'date' => '2024-07-15'
    ],
];

$passingRules = $engine->getPassingRules($context);
$totalDiscount = 0;
$messages = [];

foreach ($passingRules as $rule) {
    $discount = $rule->getMeta('discount', 0);
    $message = $rule->getMeta('message', '');

    $totalDiscount += $discount;
    $messages[] = $message;
}

echo "Total discount: " . ($totalDiscount * 100) . "%\n";
echo "Messages:\n";
foreach ($messages as $message) {
    echo "- $message\n";
}
```

**Output:**
```
Total discount: 27%
Messages:
- 15% loyalty discount applied!
- Summer Sale: 12% off!
```

---

## Access Control

Implement role-based access control with complex conditions.

```php
<?php

use RuleEngine\RuleEngine;

$engine = RuleEngine::create();

// Admins can do everything
$engine->addRule(
    $engine->builder()
        ->name('admin_full_access')
        ->when('user.role')->equals('admin')
        ->then()
        ->meta('permissions', ['read', 'write', 'delete', 'admin'])
        ->build()
);

// Editors can edit unpublished posts
$engine->addRule(
    $engine->builder()
        ->name('can_edit_post')
        ->when('user.role')->in(['admin', 'editor'])
        ->andWhen('post.status')->notEquals('published')
        ->then()
        ->meta('permissions', ['read', 'write'])
        ->build()
);

// Authors can edit their own drafts
$engine->addRule(
    $engine->builder()
        ->name('can_edit_own_draft')
        ->when('user.id')->equals('$post.author_id')
        ->andWhen('post.status')->equals('draft')
        ->then()
        ->meta('permissions', ['read', 'write'])
        ->build()
);

// Viewers can only read published content
$engine->addRule(
    $engine->builder()
        ->name('can_view_published')
        ->when('post.status')->equals('published')
        ->then()
        ->meta('permissions', ['read'])
        ->build()
);

// Check if user can edit the post
$context = [
    'user' => [
        'id' => 123,
        'role' => 'editor'
    ],
    'post' => [
        'id' => 456,
        'author_id' => 789,
        'status' => 'draft'
    ]
];

$canEdit = $engine->evaluate('can_edit_post', $context);
echo "Can edit: " . ($canEdit ? 'Yes' : 'No') . "\n";
// Output: Can edit: Yes

// Get all permissions
$passingRules = $engine->getPassingRules($context);
$permissions = [];
foreach ($passingRules as $rule) {
    $rulePermissions = $rule->getMeta('permissions', []);
    $permissions = array_unique(array_merge($permissions, $rulePermissions));
}

echo "Permissions: " . implode(', ', $permissions) . "\n";
// Output: Permissions: read, write
```

---

## Form Validation

Use the rule engine for complex form validation logic.

```php
<?php

use RuleEngine\RuleEngine;

$engine = RuleEngine::create();

// Email validation
$engine->addRule(
    $engine->builder()
        ->name('valid_email')
        ->when('email')->matches('/^[^\s@]+@[^\s@]+\.[^\s@]+$/')
        ->then()
        ->build()
);

// Age validation
$engine->addRule(
    $engine->builder()
        ->name('valid_age')
        ->when('age')->greaterThanOrEqual(18)
                     ->lessThanOrEqual(120)
        ->then()
        ->build()
);

// Password strength
$engine->addRule(
    $engine->builder()
        ->name('strong_password')
        ->when('password')->matches('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/')
        ->then()
        ->build()
);

// Username format
$engine->addRule(
    $engine->builder()
        ->name('valid_username')
        ->when('username')->matches('/^[a-zA-Z0-9_]{3,20}$/')
        ->then()
        ->build()
);

// Phone number format (US)
$engine->addRule(
    $engine->builder()
        ->name('valid_phone')
        ->when('phone')->matches('/^\+1-\d{3}-\d{3}-\d{4}$/')
        ->then()
        ->build()
);

// Validate form data
$formData = [
    'email' => 'user@example.com',
    'age' => 25,
    'password' => 'MyP@ssw0rd',
    'username' => 'john_doe',
    'phone' => '+1-555-123-4567'
];

$results = $engine->evaluateAllWithResults($formData);

$errors = [];
foreach ($results as $result) {
    if ($result->failed()) {
        $errors[] = $result->getRuleName();
    }
}

if (empty($errors)) {
    echo "Form is valid!\n";
} else {
    echo "Validation errors:\n";
    foreach ($errors as $error) {
        echo "- $error\n";
    }
}
```

---

## Content Filtering

Filter content based on tags, categories, and user preferences.

```php
<?php

use RuleEngine\RuleEngine;

$engine = RuleEngine::create();

// Featured content
$engine->addRule(
    $engine->builder()
        ->name('is_featured')
        ->when('tags')->contains('featured')
        ->andWhen('status')->equals('published')
        ->then()
        ->build()
);

// Premium content for subscribers
$engine->addRule(
    $engine->builder()
        ->name('premium_content')
        ->when('category')->equals('premium')
        ->andWhen('user.subscription')->equals('active')
        ->then()
        ->build()
);

// Age-appropriate content
$engine->addRule(
    $engine->builder()
        ->name('age_appropriate')
        ->when('content.age_rating')->lessThanOrEqual('$user.age')
        ->then()
        ->build()
);

// Trending content
$engine->addRule(
    $engine->builder()
        ->name('is_trending')
        ->when('views')->greaterThan(10000)
        ->andWhen('created_at')->greaterThan('$one_week_ago')
        ->then()
        ->build()
);

// Check content visibility
$context = [
    'tags' => ['tech', 'featured', 'news'],
    'status' => 'published',
    'category' => 'free',
    'content' => [
        'age_rating' => 13
    ],
    'views' => 15000,
    'created_at' => '2024-07-20',
    'user' => [
        'age' => 25,
        'subscription' => 'inactive'
    ],
    'one_week_ago' => '2024-07-14'
];

$passingRules = $engine->getPassingRules($context);

echo "Content matches:\n";
foreach ($passingRules as $rule) {
    echo "- " . $rule->getName() . "\n";
}
// Output:
// Content matches:
// - is_featured
// - age_appropriate
// - is_trending
```

---

## User Eligibility

Determine user eligibility for programs, features, or promotions.

```php
<?php

use RuleEngine\RuleEngine;

$engine = RuleEngine::create();

// Eligible for free shipping
$engine->addRule(
    $engine->builder()
        ->name('free_shipping')
        ->when('cart.total')->greaterThanOrEqual(50)
        ->then()
        ->meta('benefit', 'Free standard shipping')
        ->build()
);

// Eligible for premium trial
$engine->addRule(
    $engine->builder()
        ->name('premium_trial')
        ->when('user.registration_date')->greaterThan('$thirty_days_ago')
        ->andWhen('user.has_used_trial')->equals(false)
        ->then()
        ->meta('benefit', '30-day premium trial')
        ->build()
);

// Eligible for referral bonus
$engine->addRule(
    $engine->builder()
        ->name('referral_bonus')
        ->when('user.referrals')->greaterThanOrEqual(5)
        ->andWhen('user.verified')->equals(true)
        ->then()
        ->meta('benefit', '$50 referral bonus')
        ->build()
);

// Eligible for beta features
$engine->addRule(
    $engine->builder()
        ->name('beta_access')
        ->when('user.tier')->in(['gold', 'platinum'])
        ->andWhen('user.opted_in_beta')->equals(true)
        ->then()
        ->meta('benefit', 'Access to beta features')
        ->build()
);

$context = [
    'cart' => [
        'total' => 75
    ],
    'user' => [
        'registration_date' => '2024-07-01',
        'has_used_trial' => false,
        'referrals' => 7,
        'verified' => true,
        'tier' => 'gold',
        'opted_in_beta' => true
    ],
    'thirty_days_ago' => '2024-06-28'
];

$passingRules = $engine->getPassingRules($context);

echo "User is eligible for:\n";
foreach ($passingRules as $rule) {
    $benefit = $rule->getMeta('benefit', '');
    echo "- {$benefit}\n";
}
// Output:
// User is eligible for:
// - Free standard shipping
// - 30-day premium trial
// - $50 referral bonus
// - Access to beta features
```

---

## Dynamic Pricing

Calculate dynamic prices based on various factors.

```php
<?php

use RuleEngine\RuleEngine;

$engine = RuleEngine::create();

// Early bird pricing
$engine->addRule(
    $engine->builder()
        ->name('early_bird')
        ->when('booking.date')->lessThan('$event.start_date')
        ->andWhen('booking.days_before')->greaterThan(30)
        ->then()
        ->meta('multiplier', 0.8)
        ->meta('description', 'Early bird: 20% off')
        ->build()
);

// Last-minute pricing
$engine->addRule(
    $engine->builder()
        ->name('last_minute')
        ->when('booking.days_before')->lessThan(7)
        ->andWhen('event.seats_available')->greaterThan(10)
        ->then()
        ->meta('multiplier', 1.2)
        ->meta('description', 'Last minute: +20%')
        ->build()
);

// Group discount
$engine->addRule(
    $engine->builder()
        ->name('group_discount')
        ->when('booking.attendees')->greaterThanOrEqual(10)
        ->then()
        ->meta('multiplier', 0.85)
        ->meta('description', 'Group discount: 15% off')
        ->build()
);

// Member pricing
$engine->addRule(
    $engine->builder()
        ->name('member_pricing')
        ->when('user.is_member')->equals(true)
        ->then()
        ->meta('multiplier', 0.9)
        ->meta('description', 'Member discount: 10% off')
        ->build()
);

$basePrice = 100;
$context = [
    'booking' => [
        'date' => '2024-06-15',
        'days_before' => 45,
        'attendees' => 12
    ],
    'event' => [
        'start_date' => '2024-08-01',
        'seats_available' => 50
    ],
    'user' => [
        'is_member' => true
    ]
];

$passingRules = $engine->getPassingRules($context);
$finalMultiplier = 1.0;
$descriptions = [];

foreach ($passingRules as $rule) {
    $multiplier = $rule->getMeta('multiplier', 1.0);
    $description = $rule->getMeta('description', '');

    $finalMultiplier *= $multiplier;
    $descriptions[] = $description;
}

$finalPrice = $basePrice * $finalMultiplier;

echo "Base price: $" . number_format($basePrice, 2) . "\n";
echo "Applied discounts:\n";
foreach ($descriptions as $desc) {
    echo "- $desc\n";
}
echo "Final price: $" . number_format($finalPrice, 2) . "\n";

// Output:
// Base price: $100.00
// Applied discounts:
// - Early bird: 20% off
// - Group discount: 15% off
// - Member discount: 10% off
// Final price: $61.20
```

---

## Storing Rules in Database

Serialize and deserialize rules for database storage.

```php
<?php

use RuleEngine\RuleEngine;
use RuleEngine\Serialization\RuleSerializer;
use RuleEngine\Serialization\RuleDeserializer;

$engine = RuleEngine::create();
$serializer = new RuleSerializer();
$deserializer = new RuleDeserializer($engine->getRegistry());

// Create a rule
$rule = $engine->builder()
    ->name('vip_customer')
    ->when('customer.lifetime_value')->greaterThan(10000)
    ->andWhen('customer.active')->equals(true)
    ->then()
    ->meta('tier', 'VIP')
    ->meta('discount', 0.25)
    ->build();

// Serialize to JSON for storage
$json = $serializer->serializeRuleToJson($rule);

// Example: Store in database
// $pdo->prepare("INSERT INTO rules (name, definition) VALUES (?, ?)")
//     ->execute([$rule->getName(), $json]);

echo "Serialized rule:\n";
echo $json . "\n\n";

// Later, load from database
// $stmt = $pdo->prepare("SELECT definition FROM rules WHERE name = ?");
// $stmt->execute(['vip_customer']);
// $json = $stmt->fetchColumn();

// Deserialize and use
$loadedRule = $deserializer->deserializeRuleFromJson($json);
$engine->addRule($loadedRule);

// Evaluate the loaded rule
$result = $engine->evaluate('vip_customer', [
    'customer' => [
        'lifetime_value' => 15000,
        'active' => true
    ]
]);

echo "Rule evaluation result: " . ($result ? 'true' : 'false') . "\n";
// Output: Rule evaluation result: true
```

**Serialized rule output:**
```json
{
    "name": "vip_customer",
    "condition": {
        "operator": "AND",
        "arguments": [
            {
                "operator": ">",
                "arguments": [
                    {"type": "variable", "value": "customer.lifetime_value"},
                    {"type": "literal", "value": 10000}
                ]
            },
            {
                "operator": "=",
                "arguments": [
                    {"type": "variable", "value": "customer.active"},
                    {"type": "literal", "value": true}
                ]
            }
        ]
    },
    "metadata": {
        "tier": "VIP",
        "discount": 0.25
    }
}
```

---

## Advanced: Combining Multiple Rules

You can create complex logic by combining multiple rules:

```php
<?php

use RuleEngine\RuleEngine;

$engine = RuleEngine::create();

// Define multiple rules
$rules = [
    'has_account' => $engine->builder()
        ->name('has_account')
        ->when('user.id')->greaterThan(0)
        ->then()
        ->build(),

    'email_verified' => $engine->builder()
        ->name('email_verified')
        ->when('user.email_verified')->equals(true)
        ->then()
        ->build(),

    'profile_complete' => $engine->builder()
        ->name('profile_complete')
        ->when('user.profile.name')->notEquals('')
        ->andWhen('user.profile.avatar')->notEquals('')
        ->then()
        ->build(),
];

foreach ($rules as $rule) {
    $engine->addRule($rule);
}

$context = [
    'user' => [
        'id' => 123,
        'email_verified' => true,
        'profile' => [
            'name' => 'John Doe',
            'avatar' => 'avatar.jpg'
        ]
    ]
];

// Check if all setup rules pass
$allSetupComplete = $engine->evaluateAll($context);
echo "Setup complete: " . ($allSetupComplete ? 'Yes' : 'No') . "\n";

// Get incomplete steps
$failingRules = $engine->getFailingRules($context);
if (!empty($failingRules)) {
    echo "Incomplete steps:\n";
    foreach ($failingRules as $rule) {
        echo "- " . $rule->getName() . "\n";
    }
} else {
    echo "All steps completed!\n";
}
```

---

## Next Steps

- See [Operator Reference](operators.md) for a complete list of available operators
- Check the [README](../README.md) for installation and basic usage
- Explore the source code for more advanced use cases
