---
title: Update Documentation
status: done
priority: Medium
description: Document the new CONCAT operator in user-facing documentation
---

## Objectives
- Add `CONCAT` operator to the operator reference documentation
- Update README.md with concatenation examples
- Include usage examples showing both direct and fluent API usage
- Follow existing documentation style and format
- Ensure documentation is clear and helpful for users

## Deliverables
1. Updated `docs/operators.md` with CONCAT operator section
2. Updated `README.md` with string concatenation in features list
3. Example usage in appropriate documentation files
4. Consistent formatting with existing documentation

## Technical Details

### Files to Update

#### 1. docs/operators.md

Add a new section in the "String Operators" section (after `MATCHES`):

```markdown
### CONCAT

Concatenates two or more strings together.

**Fluent API:**
```php
$builder->when('firstName')->concat(' ', '$lastName')->equals('John Doe')
```

**Direct usage:**
```php
use RuleEngine\Operator\String\ConcatOperator;
use RuleEngine\Expression\OperatorExpression;
use RuleEngine\Expression\VariableExpression;
use RuleEngine\Expression\LiteralExpression;

$expr = new OperatorExpression(
    new ConcatOperator(),
    [
        new VariableExpression('firstName'),
        new LiteralExpression(' '),
        new VariableExpression('lastName')
    ]
);
```

**Examples:**
```php
// Concatenate with literal strings
$builder->when('firstName')->concat(' ', '$lastName')->equals('John Doe')

// Context: ['firstName' => 'John', 'lastName' => 'Doe']
// Result: 'John Doe'

// Multiple concatenations
$builder->when('title')->concat(': ', '$description')->containsString('Important')

// With variable references
$builder->when('firstName')
    ->concat(' ', '$middleInitial', '. ', '$lastName')
    ->equals('John Q. Public')

// Concatenate multiple values
'Hello' CONCAT ' ' CONCAT 'World' CONCAT '!' → 'Hello World!'

// Type coercion
'Order #' CONCAT 123 → 'Order #123'
'Price: $' CONCAT 99.99 → 'Price: $99.99'
```

**Note:** All operands are automatically cast to strings. Numbers, booleans, and null values are converted before concatenation.

---
```

#### 2. README.md

Update the features section (around line 12):

```markdown
- **String operators**: STARTS_WITH, ENDS_WITH, CONTAINS_STRING, MATCHES (regex), CONCAT
```

Add an example in the "Basic Usage" section or create a new section:

```markdown
### String Concatenation

Concatenate strings and variables:

```php
$rule = $engine->builder()
    ->name('full_name_check')
    ->when('user.firstName')->concat(' ', '$user.lastName')->equals('Jane Doe')
    ->then()
    ->build();

$engine->addRule($rule);

$result = $engine->evaluate('full_name_check', [
    'user' => [
        'firstName' => 'Jane',
        'lastName' => 'Doe'
    ]
]);
// $result = true
```

Concatenate with type coercion:

```php
$rule = $engine->builder()
    ->name('order_format')
    ->when('orderPrefix')->concat('#', '$orderId')->equals('ORD#12345')
    ->then()
    ->build();

// Context: ['orderPrefix' => 'ORD', 'orderId' => 12345]
// Concatenation: 'ORD' + '#' + '12345' = 'ORD#12345'
```
```

#### 3. examples/ Directory (Optional)

If examples are needed, create `examples/string_concatenation.php`:

```php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use RuleEngine\RuleEngine;

$engine = RuleEngine::create();

// Example 1: Concatenate first and last name
$rule = $engine->builder()
    ->name('full_name')
    ->when('firstName')->concat(' ', '$lastName')->equals('John Smith')
    ->then()
    ->meta('message', 'Welcome John Smith!')
    ->build();

$engine->addRule($rule);

$result = $engine->evaluate('full_name', [
    'firstName' => 'John',
    'lastName' => 'Smith'
]);

var_dump($result); // true

// Example 2: Build email address
$rule2 = $engine->builder()
    ->name('email_match')
    ->when('username')->concat('@', '$domain')->endsWith('.com')
    ->then()
    ->build();

$engine->addRule($rule2);

$result2 = $engine->evaluate('email_match', [
    'username' => 'john.doe',
    'domain' => 'example.com'
]);

var_dump($result2); // true

// Example 3: Format order number with type coercion
$rule3 = $engine->builder()
    ->name('order_format')
    ->when('prefix')->concat('-', '$year', '-', '$id')->matches('/^ORD-\d{4}-\d+$/')
    ->then()
    ->build();

$engine->addRule($rule3);

$result3 = $engine->evaluate('order_format', [
    'prefix' => 'ORD',
    'year' => 2024,
    'id' => 12345
]);

var_dump($result3); // true
```

### Documentation Style Guidelines

1. **Consistency**: Match the style and format of existing operator documentation
2. **Clear Examples**: Provide practical, real-world examples
3. **Code Blocks**: Use proper PHP syntax highlighting
4. **Completeness**: Cover both fluent API and direct usage
5. **Notes**: Include important information about type coercion

### Sections to Check

- [ ] `docs/operators.md` - String Operators section
- [ ] `README.md` - Features list
- [ ] `README.md` - Usage examples (if applicable)
- [ ] `examples/` directory (if examples exist)
- [ ] `CHANGELOG.md` - Add entry for new feature (optional)

## Dependencies
- Task 20 - Create ConcatOperator
- Task 21 - Register ConcatOperator in Engine
- Task 22 - Add Fluent API Support
- Task 23 - Write Unit Tests

## Estimated Complexity
**Low** - Straightforward documentation updates following existing patterns

## Implementation Notes
- Review existing operator documentation for style consistency
- Ensure code examples are tested and accurate
- Include practical use cases that users might encounter
- Highlight the automatic type coercion feature
- Consider adding a note about performance (concatenation is efficient with implode)
- Keep examples simple and focused on demonstrating the feature

## Acceptance Criteria
- [x] `docs/operators.md` updated with CONCAT section
- [x] CONCAT listed in string operators section
- [x] Fluent API usage documented with examples
- [x] Direct usage documented with examples
- [x] Type coercion behavior explained
- [x] README.md features list includes CONCAT
- [x] At least one practical example added to README
- [x] Documentation follows existing style and format
- [x] All code examples are valid and tested
- [x] Markdown formatting is correct
- [x] Links and cross-references work correctly
- [x] Examples directory updated (if applicable)
