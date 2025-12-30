---
title: Add Fluent API Support
status: done
priority: High
description: Add concat() method to ConditionBuilder for fluent API usage
---

## Objectives
- Add `concat()` method to `ConditionBuilder` class
- Support chaining string concatenation in fluent rule building
- Accept multiple string arguments for concatenation
- Follow existing fluent API patterns
- Enable intuitive usage like `->when('firstName')->concat(' ', 'lastName')`

## Deliverables
1. New `concat()` method in `src/Rule/ConditionBuilder.php`
2. Fluent API support for string concatenation
3. Ability to chain with other condition methods

## Technical Details

### File to Modify
```
src/Rule/ConditionBuilder.php
```

### Method Signature

Add the `concat()` method in the "String operators" section (after `matches()` method):

```php
/**
 * Concatenate the subject with one or more strings.
 *
 * @param mixed ...$values Values to concatenate with the subject
 */
public function concat(mixed ...$values): self
{
    $expressions = [$this->subject];

    foreach ($values as $value) {
        $expressions[] = $this->toExpression($value);
    }

    $operator = $this->registry->get('CONCAT');
    $this->subject = new OperatorExpression($operator, $expressions);

    return $this;
}
```

### Design Considerations

1. **Variadic Parameters**: Use `mixed ...$values` to accept multiple values
2. **Expression Building**: Create an array of expressions starting with `$this->subject`
3. **Subject Update**: Update `$this->subject` to be the concatenation expression
4. **Chaining**: Return `$this` for method chaining
5. **Type Flexibility**: Accept `mixed` to support both literals and variable references

### Usage Examples

After implementation, users should be able to:

```php
// Basic concatenation
$builder->when('firstName')->concat(' ', 'lastName')->equals('John Doe')

// Concatenate with literal
$builder->when('name')->concat(' Jr.')->startsWith('John')

// Multiple concatenations
$builder->when('firstName')
    ->concat(' ')
    ->concat('middleName')
    ->concat(' ')
    ->concat('lastName')
    ->equals('John Q Public')

// With variable references
$builder->when('firstName')->concat(' ', '$lastName')->equals('Jane Doe')

// Use in rules
$rule = $engine->builder()
    ->name('full_name_check')
    ->when('user.firstName')->concat(' ', 'user.lastName')->equals('Jane Doe')
    ->then()
    ->build();
```

### Placement in File

Insert the method after line 114 (after `matches()` method) in the "String operators" section.

## Dependencies
- Task 20 - Create ConcatOperator
- Task 21 - Register ConcatOperator in Engine

## Estimated Complexity
**Medium** - Requires understanding expression building and subject manipulation

## Implementation Notes
- Unlike comparison methods (`equals()`, `greaterThan()`), `concat()` modifies the subject rather than creating a condition
- This is similar to how mathematical operations might work if exposed in the fluent API
- The method should be placed with string operators for discoverability
- Use `$this->toExpression()` to handle both literals and variable references (`$varName`)
- Ensure proper type handling with the existing `toExpression()` method

## Acceptance Criteria
- [x] `concat()` method added to `ConditionBuilder` class
- [x] Method accepts variadic parameters (`mixed ...$values`)
- [x] Method returns `self` for chaining
- [x] Method properly creates `OperatorExpression` with `CONCAT` operator
- [x] Subject is updated to the concatenation expression
- [x] Method uses `toExpression()` for proper type handling
- [x] Method is placed in the "String operators" section
- [x] Code follows PSR-12 standards (verified by Pint)
- [x] PHPStan level 9 passes with no errors
- [x] Proper PHPDoc comments added
- [x] Method works with variable references (using `$varName` syntax)
