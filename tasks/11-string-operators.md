---
title: String Operators
status: done
priority: Medium
description: Implement string operators for text matching and manipulation
---

## Objectives
- Implement string matching operators
- Support case-sensitive and case-insensitive options
- Implement regex matching

## Deliverables
1. `src/Operator/String/StartsWithOperator.php`
2. `src/Operator/String/EndsWithOperator.php`
3. `src/Operator/String/ContainsStringOperator.php`
4. `src/Operator/String/MatchesOperator.php`
5. `src/Exception/InvalidRegexException.php`

## Technical Details

### StartsWithOperator

Check if a string starts with a given prefix.

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Operator\String;

use RuleEngine\Operator\AbstractOperator;

final class StartsWithOperator extends AbstractOperator
{
    public function __construct(
        private readonly bool $caseSensitive = true
    ) {
        parent::__construct('STARTS_WITH', 2);
    }

    public function execute(array $operands): bool
    {
        $this->validateOperandCount($operands);

        $haystack = (string) $operands[0];
        $needle = (string) $operands[1];

        if ($this->caseSensitive) {
            return str_starts_with($haystack, $needle);
        }

        return str_starts_with(
            mb_strtolower($haystack),
            mb_strtolower($needle)
        );
    }
}
```

### EndsWithOperator

Check if a string ends with a given suffix.

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Operator\String;

use RuleEngine\Operator\AbstractOperator;

final class EndsWithOperator extends AbstractOperator
{
    public function __construct(
        private readonly bool $caseSensitive = true
    ) {
        parent::__construct('ENDS_WITH', 2);
    }

    public function execute(array $operands): bool
    {
        $this->validateOperandCount($operands);

        $haystack = (string) $operands[0];
        $needle = (string) $operands[1];

        if ($this->caseSensitive) {
            return str_ends_with($haystack, $needle);
        }

        return str_ends_with(
            mb_strtolower($haystack),
            mb_strtolower($needle)
        );
    }
}
```

### ContainsStringOperator

Check if a string contains a substring.

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Operator\String;

use RuleEngine\Operator\AbstractOperator;

final class ContainsStringOperator extends AbstractOperator
{
    public function __construct(
        private readonly bool $caseSensitive = true
    ) {
        parent::__construct('CONTAINS_STRING', 2);
    }

    public function execute(array $operands): bool
    {
        $this->validateOperandCount($operands);

        $haystack = (string) $operands[0];
        $needle = (string) $operands[1];

        if ($this->caseSensitive) {
            return str_contains($haystack, $needle);
        }

        return str_contains(
            mb_strtolower($haystack),
            mb_strtolower($needle)
        );
    }
}
```

### MatchesOperator

Check if a string matches a regular expression.

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Operator\String;

use RuleEngine\Exception\InvalidRegexException;
use RuleEngine\Operator\AbstractOperator;

final class MatchesOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('MATCHES', 2);
    }

    public function execute(array $operands): bool
    {
        $this->validateOperandCount($operands);

        $subject = (string) $operands[0];
        $pattern = (string) $operands[1];

        // Suppress errors and check for false return
        $result = @preg_match($pattern, $subject);

        if ($result === false) {
            throw new InvalidRegexException($pattern, preg_last_error_msg());
        }

        return $result === 1;
    }
}
```

### InvalidRegexException

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Exception;

final class InvalidRegexException extends RuleEngineException
{
    public function __construct(string $pattern, string $error)
    {
        parent::__construct("Invalid regular expression '{$pattern}': {$error}");
    }
}
```

## Operator Summary

| Operator | Symbol | Arity | Description |
|----------|--------|-------|-------------|
| StartsWith | `STARTS_WITH` | 2 | String starts with prefix |
| EndsWith | `ENDS_WITH` | 2 | String ends with suffix |
| ContainsString | `CONTAINS_STRING` | 2 | String contains substring |
| Matches | `MATCHES` | 2 | String matches regex pattern |

## Usage Example

```php
use RuleEngine\Context\Context;
use RuleEngine\Expression\LiteralExpression;
use RuleEngine\Expression\OperatorExpression;
use RuleEngine\Expression\VariableExpression;
use RuleEngine\Operator\String\StartsWithOperator;
use RuleEngine\Operator\String\MatchesOperator;

$context = Context::fromArray([
    'email' => 'john.doe@example.com',
    'phone' => '+1-555-123-4567',
]);

// Check if email starts with "john"
$startsWithJohn = new OperatorExpression(
    new StartsWithOperator(),
    [
        new VariableExpression('email'),
        new LiteralExpression('john'),
    ]
);

echo $startsWithJohn->evaluate($context); // true

// Check if phone matches pattern
$validPhone = new OperatorExpression(
    new MatchesOperator(),
    [
        new VariableExpression('phone'),
        new LiteralExpression('/^\+\d{1,3}-\d{3}-\d{3}-\d{4}$/'),
    ]
);

echo $validPhone->evaluate($context); // true
```

## Dependencies
- Task 05 - Expression Evaluator
- Task 06 - Operator Registry

## Estimated Complexity
**Low** - Straightforward string operations with PHP built-in functions

## Implementation Notes
- StartsWithOperator, EndsWithOperator, ContainsStringOperator support optional case sensitivity
- Case-insensitive comparisons use `mb_strtolower()` for proper Unicode support
- MatchesOperator validates regex and throws `InvalidRegexException` on invalid patterns
- All operators cast inputs to strings for safety
- `CONTAINS_STRING` is named differently from set `CONTAINS` to avoid confusion

## Acceptance Criteria
- [x] All 4 string operators implemented
- [x] STARTS_WITH correctly checks string prefix
- [x] ENDS_WITH correctly checks string suffix
- [x] CONTAINS_STRING correctly checks substring
- [x] MATCHES correctly evaluates regex patterns
- [x] Case-insensitive options work correctly
- [x] Invalid regex throws `InvalidRegexException`
- [x] Unicode strings handled correctly
- [x] PHPStan passes at level 8
- [x] Unit tests cover all operators and edge cases
