---
title: Create ConcatOperator
status: done
priority: Critical
description: Implement the string concatenation operator class
---

## Objectives
- Create a new `ConcatOperator` class that implements `OperatorInterface`
- Support concatenating 2 or more strings (variadic operator)
- Follow existing operator patterns and conventions
- Cast operands to strings before concatenation
- Ensure type safety and PHPStan level 9 compliance

## Deliverables
1. New file: `src/Operator/String/ConcatOperator.php`
2. Operator extends `AbstractOperator`
3. Operator name: `CONCAT`
4. Variadic arity (accepts 2+ operands)
5. Returns concatenated string

## Technical Details

### File Location
```
src/Operator/String/ConcatOperator.php
```

### Class Structure
The operator should follow the same pattern as existing string operators:

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Operator\String;

use RuleEngine\Operator\AbstractOperator;

final class ConcatOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('CONCAT', -1); // Variadic: accepts 2+ operands
    }

    public function execute(array $operands): string
    {
        $this->validateOperandCount($operands);

        // Cast all operands to strings and concatenate
        return implode('', array_map(
            fn($operand) => (string) $operand,
            $operands
        ));
    }
}
```

### Design Considerations

1. **Variadic Arity**: Like `AddOperator`, use `-1` for arity to accept multiple operands
2. **Type Casting**: Cast each operand to string using `(string)` for consistency
3. **Return Type**: Return `string` (not `mixed`) for type safety
4. **Validation**: `validateOperandCount()` is inherited from `AbstractOperator`
5. **Naming**: Follow existing pattern (e.g., `StartsWithOperator`, `EndsWithOperator`)

### Consistency with Existing Operators

Reference these similar implementations:
- `src/Operator/String/ContainsStringOperator.php` - string handling pattern
- `src/Operator/Math/AddOperator.php` - variadic operator pattern
- `src/Operator/String/StartsWithOperator.php` - string operator structure

## Dependencies
None - this is a standalone operator class

## Estimated Complexity
**Low** - Straightforward implementation following existing patterns

## Implementation Notes
- Use `implode()` for efficient string concatenation
- All operands will be cast to string, so numbers, booleans, etc. will work
- Empty operand arrays should return empty string (handled by `implode()`)
- No need for case sensitivity option (unlike `ContainsStringOperator`)

## Acceptance Criteria
- [x] File `src/Operator/String/ConcatOperator.php` created
- [x] Class extends `AbstractOperator`
- [x] Operator name is `CONCAT`
- [x] Arity is `-1` (variadic)
- [x] `execute()` method returns `string`
- [x] All operands are cast to string before concatenation
- [x] Code follows PSR-12 standards (verified by Pint)
- [x] PHPStan level 9 passes with no errors
- [x] Proper type declarations (`declare(strict_types=1)`)
