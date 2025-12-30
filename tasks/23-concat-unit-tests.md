---
title: Write Unit Tests
status: done
priority: High
description: Create comprehensive unit tests for ConcatOperator
---

## Objectives
- Create comprehensive test suite for `ConcatOperator`
- Test operator in isolation and through fluent API
- Cover edge cases and type coercion
- Ensure 100% type coverage (project requirement)
- Follow existing test patterns

## Deliverables
1. Unit tests for `ConcatOperator` in `tests/Unit/Operator/String/StringOperatorsTest.php`
2. Integration tests for fluent API in `tests/Integration/RuleBuilderTest.php` or similar
3. All tests passing with 100% type coverage
4. Edge case coverage

## Technical Details

### Test File Location
Add tests to the existing file:
```
tests/Unit/Operator/String/StringOperatorsTest.php
```

### Test Cases to Implement

#### 1. Basic Operator Tests
```php
test('ConcatOperator name', function (): void {
    $operator = new ConcatOperator();
    expect($operator->getName())->toBe('CONCAT');
});

test('ConcatOperator is variadic', function (): void {
    $operator = new ConcatOperator();
    expect($operator->getArity())->toBe(-1);
});

test('ConcatOperator concatenates two strings', function (): void {
    $operator = new ConcatOperator();
    expect($operator->execute(['Hello', ' World']))->toBe('Hello World');
});

test('ConcatOperator concatenates multiple strings', function (): void {
    $operator = new ConcatOperator();
    expect($operator->execute(['Hello', ' ', 'World', '!']))->toBe('Hello World!');
});
```

#### 2. Type Coercion Tests
```php
test('ConcatOperator casts integers to strings', function (): void {
    $operator = new ConcatOperator();
    expect($operator->execute([123, 456]))->toBe('123456');
    expect($operator->execute(['Order #', 42]))->toBe('Order #42');
});

test('ConcatOperator casts floats to strings', function (): void {
    $operator = new ConcatOperator();
    expect($operator->execute([3.14, ' is pi']))->toBe('3.14 is pi');
});

test('ConcatOperator casts booleans to strings', function (): void {
    $operator = new ConcatOperator();
    expect($operator->execute(['Value: ', true]))->toBe('Value: 1');
    expect($operator->execute(['Value: ', false]))->toBe('Value: ');
});

test('ConcatOperator casts null to empty string', function (): void {
    $operator = new ConcatOperator();
    expect($operator->execute(['Hello', null, 'World']))->toBe('HelloWorld');
});

test('ConcatOperator handles mixed types', function (): void {
    $operator = new ConcatOperator();
    expect($operator->execute(['Order ', '#', 123, ': $', 99.99]))
        ->toBe('Order #123: $99.99');
});
```

#### 3. Edge Cases
```php
test('ConcatOperator concatenates empty strings', function (): void {
    $operator = new ConcatOperator();
    expect($operator->execute(['', '', '']))->toBe('');
});

test('ConcatOperator handles single operand', function (): void {
    $operator = new ConcatOperator();
    expect($operator->execute(['Hello']))->toBe('Hello');
});

test('ConcatOperator handles empty array', function (): void {
    $operator = new ConcatOperator();
    expect($operator->execute([]))->toBe('');
});

test('ConcatOperator handles unicode strings', function (): void {
    $operator = new ConcatOperator();
    expect($operator->execute(['Héllo', ' ', 'Wörld', '!']))->toBe('Héllo Wörld!');
    expect($operator->execute(['こんにちは', '世界']))->toBe('こんにちは世界');
});

test('ConcatOperator handles special characters', function (): void {
    $operator = new ConcatOperator();
    expect($operator->execute(['Line1\n', 'Line2']))->toBe("Line1\nLine2");
    expect($operator->execute(['Tab\t', 'Space ']))->toBe("Tab\tSpace ");
});
```

#### 4. Fluent API Integration Tests
Add to `tests/Integration/` or create new test file:

```php
test('fluent API concatenates strings', function (): void {
    $engine = RuleEngine::create();

    $rule = $engine->builder()
        ->name('concat_test')
        ->when('firstName')->concat(' ', '$lastName')->equals('John Doe')
        ->then()
        ->build();

    $engine->addRule($rule);

    expect($engine->evaluate('concat_test', [
        'firstName' => 'John',
        'lastName' => 'Doe'
    ]))->toBeTrue();
});

test('fluent API concatenates with multiple variables', function (): void {
    $engine = RuleEngine::create();

    $rule = $engine->builder()
        ->name('full_name')
        ->when('firstName')->concat(' ', '$middleName', ' ', '$lastName')
            ->equals('John Q Public')
        ->then()
        ->build();

    $engine->addRule($rule);

    expect($engine->evaluate('full_name', [
        'firstName' => 'John',
        'middleName' => 'Q',
        'lastName' => 'Public'
    ]))->toBeTrue();
});

test('fluent API chains concat with other string operators', function (): void {
    $engine = RuleEngine::create();

    $rule = $engine->builder()
        ->name('chain_test')
        ->when('firstName')->concat(' ', '$lastName')->startsWith('John')
        ->then()
        ->build();

    $engine->addRule($rule);

    expect($engine->evaluate('chain_test', [
        'firstName' => 'John',
        'lastName' => 'Doe'
    ]))->toBeTrue();
});
```

#### 5. Direct Expression Usage Tests
```php
test('ConcatOperator works via direct expression', function (): void {
    $engine = RuleEngine::create();

    $expression = new OperatorExpression(
        new ConcatOperator(),
        [
            new VariableExpression('firstName'),
            new LiteralExpression(' '),
            new VariableExpression('lastName')
        ]
    );

    $context = new Context(['firstName' => 'Jane', 'lastName' => 'Smith']);
    $result = (new Evaluator())->evaluate($expression, $context);

    expect($result)->toBe('Jane Smith');
});
```

### Required Imports
```php
use RuleEngine\Operator\String\ConcatOperator;
use RuleEngine\Expression\OperatorExpression;
use RuleEngine\Expression\VariableExpression;
use RuleEngine\Expression\LiteralExpression;
use RuleEngine\Context\Context;
use RuleEngine\Evaluator\Evaluator;
```

## Dependencies
- Task 20 - Create ConcatOperator
- Task 21 - Register ConcatOperator in Engine
- Task 22 - Add Fluent API Support

## Estimated Complexity
**Medium** - Comprehensive test coverage requires multiple scenarios and edge cases

## Implementation Notes
- Follow existing test patterns in `StringOperatorsTest.php`
- Use Pest syntax (`test()`, `expect()`)
- Ensure tests cover both unit (operator in isolation) and integration (fluent API)
- Test type coercion thoroughly as strings will be concatenated with various types
- Include unicode and special character tests
- Verify 100% type coverage requirement: `composer tests:type-coverage`
- Run full test suite: `composer tests`

## Acceptance Criteria
- [x] All basic operator tests implemented and passing
- [x] Type coercion tests cover int, float, bool, null
- [x] Edge cases tested (empty strings, unicode, special chars)
- [x] Fluent API integration tests passing
- [x] Direct expression usage tests passing
- [x] All tests use proper Pest syntax
- [x] Code follows PSR-12 standards
- [x] 100% type coverage maintained (`composer tests:type-coverage`)
- [x] PHPStan level 9 passes (`composer tests:types`)
- [x] All tests pass (`composer tests:unit`)
- [x] No test failures in integration suite
