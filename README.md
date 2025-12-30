# PHP Rule Engine

A flexible and extensible rule engine for PHP, supporting logical operators, mathematical operations, and set operations.

## Features

- **Fluent API** for intuitive rule construction
- **Logical operators**: AND, OR, NOT, XOR
- **Comparison operators**: =, !=, <, >, <=, >=, ===, !==
- **Mathematical operators**: +, -, *, /, %, ^ (power)
- **Set operators**: IN, NOT_IN, CONTAINS, SUBSET, UNION, INTERSECT, DIFF
- **String operators**: STARTS_WITH, ENDS_WITH, CONTAINS_STRING, MATCHES (regex), CONCAT
- **Nested variable access** with dot notation (e.g., `user.profile.age`)
- **Rule serialization** to JSON for storage and persistence
- **Extensible operator system** - easily add custom operators
- **Type-safe** with full PHPStan level 9 compliance

## Requirements

- PHP >=8.1

## Installation

```bash
composer require funkyoz/rule-engine
```

## Quick Start

```php
<?php

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

// Evaluate the rule
$result = $engine->evaluate('adult_user', [
    'user' => ['age' => 25]
]);

// $result = true
```

## Documentation

- [Operator Reference](docs/operators.md) - Complete guide to all available operators
- [Usage Examples](docs/examples.md) - Real-world examples and use cases

## Basic Usage

### Creating Rules

Rules are created using the fluent builder API:

```php
$engine = RuleEngine::create();

$rule = $engine->builder()
    ->name('premium_customer')
    ->when('customer.tier')->equals('premium')
    ->andWhen('customer.active')->equals(true)
    ->then()
    ->build();

$engine->addRule($rule);
```

### Evaluating Rules

Evaluate a single rule:

```php
$result = $engine->evaluate('premium_customer', [
    'customer' => [
        'tier' => 'premium',
        'active' => true
    ]
]);
// $result = true
```

Evaluate all rules:

```php
$allPass = $engine->evaluateAll($context);
$anyPass = $engine->evaluateAny($context);
```

Get passing and failing rules:

```php
$passingRules = $engine->getPassingRules($context);
$failingRules = $engine->getFailingRules($context);
```

### Adding Metadata

Rules can store metadata for actions or additional information:

```php
$rule = $engine->builder()
    ->name('loyalty_discount')
    ->when('customer.loyalty_points')->greaterThan(1000)
    ->then()
    ->meta('discount', 0.15)
    ->meta('message', '15% loyalty discount applied!')
    ->build();

// Access metadata from passing rules
$passingRules = $engine->getPassingRules($context);
foreach ($passingRules as $rule) {
    $discount = $rule->getMeta('discount', 0);
    $message = $rule->getMeta('message', '');
}
```

### Chaining Conditions

Multiple conditions on the same subject are chained with AND:

```php
$rule = $engine->builder()
    ->name('valid_age')
    ->when('age')->greaterThanOrEqual(18)
                 ->lessThanOrEqual(120)
    ->then()
    ->build();
```

Use `andWhen()` for different subjects:

```php
$rule = $engine->builder()
    ->name('eligible_user')
    ->when('user.age')->greaterThanOrEqual(18)
    ->andWhen('user.verified')->equals(true)
    ->andWhen('user.status')->notEquals('banned')
    ->then()
    ->build();
```

### Serialization

Rules can be serialized to JSON for storage:

```php
use RuleEngine\Serialization\RuleSerializer;
use RuleEngine\Serialization\RuleDeserializer;

$serializer = new RuleSerializer();
$deserializer = new RuleDeserializer($engine->getRegistry());

// Serialize
$rule = $engine->builder()
    ->name('my_rule')
    ->when('value')->greaterThan(10)
    ->then()
    ->build();

$json = $serializer->serializeRuleToJson($rule);
// Store in database...

// Deserialize
$loadedRule = $deserializer->deserializeRuleFromJson($json);
$engine->addRule($loadedRule);
```

### Custom Operators

Extend the engine with custom operators:

```php
use RuleEngine\Operator\OperatorInterface;

class IsEvenOperator implements OperatorInterface
{
    public function getName(): string
    {
        return 'IS_EVEN';
    }

    public function getArity(): int
    {
        return 1;
    }

    public function execute(array $operands): bool
    {
        $value = $operands[0];
        return is_int($value) && $value % 2 === 0;
    }
}

// Register the operator
$engine->registerOperator(new IsEvenOperator());
```

## Testing

Run the test suite:

```bash
composer test
```

Run static analysis:

```bash
composer analyse
```

Format code:

```bash
composer format
```

## License

MIT License - see [LICENSE](LICENSE) file for details.

## Author

Lorenzo Dessimoni - [lorenzo.dessimoni@gmail.com](mailto:lorenzo.dessimoni@gmail.com)
