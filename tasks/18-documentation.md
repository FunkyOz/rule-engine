---
title: Documentation & Examples
status: done
priority: Medium
description: Create comprehensive documentation and usage examples
---

## Objectives
- Create README with installation and quick start
- Document all operators and their usage
- Provide real-world examples
- Include API reference

## Deliverables
1. `README.md` - Main documentation
2. `docs/operators.md` - Operator reference
3. `docs/examples.md` - Usage examples
4. `examples/` - Example PHP files

## Technical Details

### README.md Structure

```markdown
# PHP Rule Engine

A flexible and extensible rule engine for PHP, supporting logical operators,
mathematical operations, and set operations.

## Features

- Fluent API for rule construction
- Support for logical operators (AND, OR, NOT, XOR)
- Comparison operators (=, !=, <, >, <=, >=, ===, !==)
- Mathematical operators (+, -, *, /, %, ^)
- Set operators (IN, NOT_IN, CONTAINS, SUBSET, UNION, INTERSECT, DIFF)
- String operators (STARTS_WITH, ENDS_WITH, CONTAINS_STRING, MATCHES)
- Nested variable access with dot notation
- Rule serialization to JSON
- Extensible operator system

## Requirements

- PHP 8.2+

## Installation

composer require funkyoz/rule-engine

## Quick Start

use RuleEngine\RuleEngine;

// Create the engine
$engine = RuleEngine::create();

// Build a rule
$engine->addRule(
    $engine->builder()
        ->name('adult_user')
        ->when('user.age')->greaterThanOrEqual(18)
        ->then()
        ->build()
);

// Evaluate
$result = $engine->evaluate('adult_user', [
    'user' => ['age' => 25]
]);

// $result = true

## Documentation

- [Operator Reference](docs/operators.md)
- [Usage Examples](docs/examples.md)

## License

MIT
```

### docs/operators.md

Document all operators with examples:

```markdown
# Operator Reference

## Comparison Operators

### Equal (=)
Loose equality comparison.
$builder->when('status')->equals('active')

### Not Equal (!=)
Loose inequality comparison.
$builder->when('status')->notEquals('deleted')

### Greater Than (>)
$builder->when('age')->greaterThan(18)

### Greater Than or Equal (>=)
$builder->when('age')->greaterThanOrEqual(18)

### Less Than (<)
$builder->when('price')->lessThan(100)

### Less Than or Equal (<=)
$builder->when('quantity')->lessThanOrEqual(10)

## Logical Operators

### AND
All conditions must be true.
$builder
    ->when('age')->greaterThanOrEqual(18)
    ->andWhen('verified')->equals(true)

### OR
At least one condition must be true.
// Use with RuleSet or nested expressions

### NOT
Negates the condition.
// Applied programmatically

### XOR
Exactly one condition must be true.

## Set Operators

### IN
Check if value is in array.
$builder->when('role')->in(['admin', 'moderator'])

### NOT IN
Check if value is not in array.
$builder->when('status')->notIn(['banned', 'suspended'])

### CONTAINS
Check if array contains value.
$builder->when('tags')->contains('featured')

### SUBSET
Check if first array is subset of second.

### UNION
Combine arrays, removing duplicates.

### INTERSECT
Get common elements.

### DIFF
Get elements in first but not second.

## Math Operators

### Add (+)
Sum of values.

### Subtract (-)
Difference of values.

### Multiply (*)
Product of values.

### Divide (/)
Quotient of values.

### Modulo (%)
Remainder of division.

### Power (^)
Exponentiation.

## String Operators

### STARTS_WITH
$builder->when('email')->startsWith('admin@')

### ENDS_WITH
$builder->when('email')->endsWith('@company.com')

### CONTAINS_STRING
$builder->when('description')->containsString('discount')

### MATCHES
Regular expression matching.
$builder->when('phone')->matches('/^\+\d{1,3}-\d{3}-\d{3}-\d{4}$/')
```

### docs/examples.md

Real-world usage examples:

```markdown
# Usage Examples

## E-commerce Discount Rules

$engine = RuleEngine::create();

// Loyalty discount for premium members
$engine->addRule(
    $engine->builder()
        ->name('loyalty_discount')
        ->when('customer.tier')->in(['gold', 'platinum'])
        ->andWhen('order.total')->greaterThan(100)
        ->then()
        ->meta('discount', 0.15)
        ->build()
);

// First-time buyer discount
$engine->addRule(
    $engine->builder()
        ->name('new_customer_discount')
        ->when('customer.orders_count')->equals(0)
        ->then()
        ->meta('discount', 0.10)
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
    ],
];

$passingRules = $engine->getPassingRules($context);
$totalDiscount = 0;

foreach ($passingRules as $rule) {
    $totalDiscount += $rule->getMeta('discount', 0);
}

## Access Control

$engine = RuleEngine::create();

$engine->addRule(
    $engine->builder()
        ->name('can_edit_post')
        ->when('user.role')->in(['admin', 'editor'])
        ->andWhen('post.status')->notEquals('published')
        ->then()
        ->build()
);

$canEdit = $engine->evaluate('can_edit_post', [
    'user' => ['role' => 'editor'],
    'post' => ['status' => 'draft'],
]);

## Form Validation

$engine = RuleEngine::create();

$engine->addRule(
    $engine->builder()
        ->name('valid_email')
        ->when('email')->matches('/^[^@]+@[^@]+\.[^@]+$/')
        ->then()
        ->build()
);

$engine->addRule(
    $engine->builder()
        ->name('valid_age')
        ->when('age')->greaterThanOrEqual(18)
        ->andWhen('age')->lessThanOrEqual(120)
        ->then()
        ->build()
);

$results = $engine->evaluateAllWithResults([
    'email' => 'test@example.com',
    'age' => 25,
]);

foreach ($results as $result) {
    if ($result->failed()) {
        echo "Validation failed: " . $result->getRuleName();
    }
}

## Storing Rules in Database

use RuleEngine\Serialization\RuleSerializer;
use RuleEngine\Serialization\RuleDeserializer;

$serializer = new RuleSerializer();

// Serialize to JSON for storage
$rule = $engine->builder()
    ->name('my_rule')
    ->when('value')->greaterThan(10)
    ->then()
    ->build();

$json = $serializer->serializeRuleToJson($rule);
// Store $json in database...

// Later, load from database
$deserializer = new RuleDeserializer($engine->getRegistry());
$loadedRule = $deserializer->deserializeRuleFromJson($json);

$engine->addRule($loadedRule);
```

### Example Files

Create `examples/` directory with runnable PHP files:

- `examples/basic-usage.php`
- `examples/e-commerce-discounts.php`
- `examples/access-control.php`
- `examples/form-validation.php`
- `examples/custom-operators.php`

## Dependencies
- All implementation tasks (01-17)

## Estimated Complexity
**Low** - Documentation with code examples

## Implementation Notes
- Keep examples simple and focused
- Use realistic scenarios
- Include both simple and advanced use cases
- Test all code examples to ensure they work

## Acceptance Criteria
- [x] README provides clear installation and quick start
- [x] All operators are documented with examples
- [x] Real-world examples are provided
- [x] Example files run without errors
- [x] Documentation is clear and well-organized
