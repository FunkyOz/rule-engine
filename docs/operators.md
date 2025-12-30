# Operator Reference

This document provides a comprehensive reference of all operators available in the PHP Rule Engine.

## Table of Contents

- [Comparison Operators](#comparison-operators)
- [Logical Operators](#logical-operators)
- [Set Operators](#set-operators)
- [Mathematical Operators](#mathematical-operators)
- [String Operators](#string-operators)

---

## Comparison Operators

### Equal (=)

Performs loose equality comparison.

**Fluent API:**
```php
$builder->when('status')->equals('active')
```

**Direct usage:**
```php
use RuleEngine\Operator\Comparison\EqualOperator;
use RuleEngine\Expression\OperatorExpression;
use RuleEngine\Expression\VariableExpression;
use RuleEngine\Expression\LiteralExpression;

$expr = new OperatorExpression(
    new EqualOperator(),
    [new VariableExpression('status'), new LiteralExpression('active')]
);
```

**Examples:**
- `5 = "5"` → `true` (loose comparison)
- `"active" = "active"` → `true`
- `null = false` → `true` (loose)

---

### Not Equal (!=)

Performs loose inequality comparison.

**Fluent API:**
```php
$builder->when('status')->notEquals('deleted')
```

**Examples:**
- `5 != "6"` → `true`
- `"active" != "inactive"` → `true`
- `1 != true` → `false` (loose comparison)

---

### Strict Equal (===)

Performs strict equality comparison (type and value).

**Note:** Not directly available in fluent API. Use via OperatorExpression or custom implementation.

**Examples:**
- `5 === 5` → `true`
- `5 === "5"` → `false` (different types)
- `null === null` → `true`

---

### Strict Not Equal (!==)

Performs strict inequality comparison (type and value).

**Note:** Not directly available in fluent API. Use via OperatorExpression or custom implementation.

**Examples:**
- `5 !== "5"` → `true` (different types)
- `5 !== 5` → `false`

---

### Greater Than (>)

Checks if left value is greater than right value.

**Fluent API:**
```php
$builder->when('age')->greaterThan(18)
```

**Examples:**
- `25 > 18` → `true`
- `10 > 10` → `false`
- `"b" > "a"` → `true` (string comparison)

---

### Greater Than or Equal (>=)

Checks if left value is greater than or equal to right value.

**Fluent API:**
```php
$builder->when('age')->greaterThanOrEqual(18)
```

**Examples:**
- `18 >= 18` → `true`
- `25 >= 18` → `true`
- `17 >= 18` → `false`

---

### Less Than (<)

Checks if left value is less than right value.

**Fluent API:**
```php
$builder->when('price')->lessThan(100)
```

**Examples:**
- `50 < 100` → `true`
- `100 < 100` → `false`

---

### Less Than or Equal (<=)

Checks if left value is less than or equal to right value.

**Fluent API:**
```php
$builder->when('quantity')->lessThanOrEqual(10)
```

**Examples:**
- `10 <= 10` → `true`
- `5 <= 10` → `true`
- `15 <= 10` → `false`

---

## Logical Operators

### AND

Returns true only if all operands are true.

**Fluent API (chaining on same subject):**
```php
$builder
    ->when('age')->greaterThanOrEqual(18)
                 ->lessThanOrEqual(65)
```

**Fluent API (different subjects):**
```php
$builder
    ->when('age')->greaterThanOrEqual(18)
    ->andWhen('verified')->equals(true)
    ->andWhen('status')->notEquals('banned')
```

**Examples:**
- `true AND true` → `true`
- `true AND false` → `false`
- `false AND false` → `false`

---

### OR

Returns true if at least one operand is true.

**Fluent API (with callback):**
```php
$builder
    ->when('role')->equals('admin')
    ->or(fn($b) => $b->when('role')->equals('moderator'))
```

**Examples:**
- `true OR false` → `true`
- `false OR false` → `false`
- `true OR true` → `true`

---

### NOT

Negates the boolean value.

**Direct usage:**
```php
use RuleEngine\Operator\Logical\NotOperator;
use RuleEngine\Expression\OperatorExpression;

$expr = new OperatorExpression(
    new NotOperator(),
    [$someExpression]
);
```

**Examples:**
- `NOT true` → `false`
- `NOT false` → `true`

---

### XOR

Returns true if exactly one operand is true.

**Direct usage:**
```php
use RuleEngine\Operator\Logical\XorOperator;

// Use via OperatorExpression
```

**Examples:**
- `true XOR false` → `true`
- `true XOR true` → `false`
- `false XOR false` → `false`

---

## Set Operators

### IN

Checks if a value exists in an array.

**Fluent API:**
```php
$builder->when('role')->in(['admin', 'moderator', 'editor'])
```

**Examples:**
```php
// Context: ['role' => 'admin']
->when('role')->in(['admin', 'moderator']) // true

// Context: ['role' => 'user']
->when('role')->in(['admin', 'moderator']) // false
```

---

### NOT_IN

Checks if a value does not exist in an array.

**Fluent API:**
```php
$builder->when('status')->notIn(['banned', 'suspended', 'deleted'])
```

**Examples:**
```php
// Context: ['status' => 'active']
->when('status')->notIn(['banned', 'suspended']) // true

// Context: ['status' => 'banned']
->when('status')->notIn(['banned', 'suspended']) // false
```

---

### CONTAINS

Checks if an array contains a specific value.

**Fluent API:**
```php
$builder->when('tags')->contains('featured')
```

**Examples:**
```php
// Context: ['tags' => ['featured', 'popular', 'new']]
->when('tags')->contains('featured') // true

// Context: ['tags' => ['popular', 'new']]
->when('tags')->contains('featured') // false
```

---

### SUBSET

Checks if the first array is a subset of the second array.

**Direct usage:**
```php
use RuleEngine\Operator\Set\SubsetOperator;

// [1, 2] SUBSET [1, 2, 3, 4] → true
// [1, 5] SUBSET [1, 2, 3, 4] → false
```

**Examples:**
- `[1, 2] ⊆ [1, 2, 3]` → `true`
- `[1, 5] ⊆ [1, 2, 3]` → `false`
- `[] ⊆ [1, 2, 3]` → `true`

---

### UNION

Combines two arrays, removing duplicates.

**Direct usage:**
```php
use RuleEngine\Operator\Set\UnionOperator;

// [1, 2] UNION [2, 3] → [1, 2, 3]
```

**Examples:**
- `[1, 2] ∪ [2, 3]` → `[1, 2, 3]`
- `[1, 2] ∪ [3, 4]` → `[1, 2, 3, 4]`

---

### INTERSECT

Gets the common elements between two arrays.

**Direct usage:**
```php
use RuleEngine\Operator\Set\IntersectOperator;

// [1, 2, 3] INTERSECT [2, 3, 4] → [2, 3]
```

**Examples:**
- `[1, 2, 3] ∩ [2, 3, 4]` → `[2, 3]`
- `[1, 2] ∩ [3, 4]` → `[]`

---

### DIFF

Gets elements in the first array that are not in the second.

**Direct usage:**
```php
use RuleEngine\Operator\Set\DiffOperator;

// [1, 2, 3] DIFF [2, 3, 4] → [1]
```

**Examples:**
- `[1, 2, 3] \ [2, 3, 4]` → `[1]`
- `[1, 2, 3] \ [4, 5]` → `[1, 2, 3]`

---

## Mathematical Operators

### Add (+)

Adds two numbers.

**Direct usage:**
```php
use RuleEngine\Operator\Math\AddOperator;

// 5 + 3 → 8
```

**Examples:**
- `5 + 3` → `8`
- `10.5 + 2.3` → `12.8`
- `-5 + 3` → `-2`

---

### Subtract (-)

Subtracts the second number from the first.

**Direct usage:**
```php
use RuleEngine\Operator\Math\SubtractOperator;

// 10 - 3 → 7
```

**Examples:**
- `10 - 3` → `7`
- `5 - 10` → `-5`
- `10.5 - 2.3` → `8.2`

---

### Multiply (*)

Multiplies two numbers.

**Direct usage:**
```php
use RuleEngine\Operator\Math\MultiplyOperator;

// 5 * 3 → 15
```

**Examples:**
- `5 * 3` → `15`
- `2.5 * 4` → `10.0`
- `-3 * 4` → `-12`

---

### Divide (/)

Divides the first number by the second.

**Direct usage:**
```php
use RuleEngine\Operator\Math\DivideOperator;

// 10 / 2 → 5
```

**Examples:**
- `10 / 2` → `5`
- `15 / 4` → `3.75`
- `10 / 0` → throws `DivisionByZeroException`

**Note:** Division by zero throws a `DivisionByZeroException`.

---

### Modulo (%)

Returns the remainder of division.

**Direct usage:**
```php
use RuleEngine\Operator\Math\ModuloOperator;

// 10 % 3 → 1
```

**Examples:**
- `10 % 3` → `1`
- `15 % 5` → `0`
- `7 % 2` → `1`

---

### Power (^)

Raises the first number to the power of the second.

**Direct usage:**
```php
use RuleEngine\Operator\Math\PowerOperator;

// 2 ^ 3 → 8
```

**Examples:**
- `2 ^ 3` → `8`
- `10 ^ 2` → `100`
- `5 ^ 0` → `1`
- `2 ^ -1` → `0.5`

---

## String Operators

### STARTS_WITH

Checks if a string starts with a given prefix.

**Fluent API:**
```php
$builder->when('email')->startsWith('admin@')
```

**Examples:**
```php
// Context: ['email' => 'admin@example.com']
->when('email')->startsWith('admin@') // true

// Context: ['email' => 'user@example.com']
->when('email')->startsWith('admin@') // false
```

---

### ENDS_WITH

Checks if a string ends with a given suffix.

**Fluent API:**
```php
$builder->when('email')->endsWith('@company.com')
```

**Examples:**
```php
// Context: ['email' => 'john@company.com']
->when('email')->endsWith('@company.com') // true

// Context: ['email' => 'john@other.com']
->when('email')->endsWith('@company.com') // false
```

---

### CONTAINS_STRING

Checks if a string contains a substring.

**Fluent API:**
```php
$builder->when('description')->containsString('discount')
```

**Examples:**
```php
// Context: ['description' => 'Special discount offer']
->when('description')->containsString('discount') // true

// Context: ['description' => 'Regular price']
->when('description')->containsString('discount') // false
```

**Note:** Case-sensitive comparison.

---

### MATCHES

Checks if a string matches a regular expression pattern.

**Fluent API:**
```php
$builder->when('phone')->matches('/^\+\d{1,3}-\d{3}-\d{3}-\d{4}$/')
```

**Examples:**
```php
// Context: ['email' => 'test@example.com']
->when('email')->matches('/^[^@]+@[^@]+\.[^@]+$/') // true

// Context: ['phone' => '+1-555-123-4567']
->when('phone')->matches('/^\+\d{1,3}-\d{3}-\d{3}-\d{4}$/') // true

// Context: ['code' => 'ABC123']
->when('code')->matches('/^[A-Z]{3}\d{3}$/') // true
```

**Note:** Throws `InvalidRegexException` if the pattern is invalid.

---

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

// Type coercion - all operands are cast to strings
// Context: ['orderPrefix' => 'ORD', 'orderId' => 123]
$builder->when('orderPrefix')->concat('#', '$orderId')->equals('ORD#123')
// 'ORD' + '#' + '123' = 'ORD#123'
```

**Note:** All operands are automatically cast to strings. Numbers, booleans, and null values are converted before concatenation.

---

## Using Operators Directly

While the fluent API is recommended, you can use operators directly:

```php
use RuleEngine\Expression\OperatorExpression;
use RuleEngine\Expression\VariableExpression;
use RuleEngine\Expression\LiteralExpression;
use RuleEngine\Operator\Comparison\GreaterThanOperator;

$expression = new OperatorExpression(
    new GreaterThanOperator(),
    [
        new VariableExpression('age'),
        new LiteralExpression(18)
    ]
);

$result = $engine->evaluateExpression($expression, ['age' => 25]);
// $result = true
```

## Creating Custom Operators

You can extend the engine with custom operators:

```php
use RuleEngine\Operator\OperatorInterface;

class IsPrimeOperator implements OperatorInterface
{
    public function getName(): string
    {
        return 'IS_PRIME';
    }

    public function getArity(): int
    {
        return 1;
    }

    public function execute(array $operands): bool
    {
        $value = $operands[0];

        if (!is_int($value) || $value < 2) {
            return false;
        }

        for ($i = 2; $i <= sqrt($value); $i++) {
            if ($value % $i === 0) {
                return false;
            }
        }

        return true;
    }
}

// Register the operator
$engine->registerOperator(new IsPrimeOperator());
```

---

## Operator Precedence

When using operators directly (not through the fluent API), be aware of evaluation order:

1. Mathematical operators are evaluated first
2. Comparison operators are evaluated next
3. Logical operators are evaluated last (NOT, AND, OR, XOR)

When in doubt, use explicit parentheses by nesting OperatorExpressions.
