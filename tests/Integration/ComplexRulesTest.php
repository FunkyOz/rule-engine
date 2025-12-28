<?php

declare(strict_types=1);

use RuleEngine\Context\Context;
use RuleEngine\Expression\LiteralExpression;
use RuleEngine\Expression\OperatorExpression;
use RuleEngine\Expression\VariableExpression;
use RuleEngine\Operator\Comparison\GreaterThanOperator;
use RuleEngine\Operator\Logical\AndOperator;
use RuleEngine\Operator\Logical\OrOperator;
use RuleEngine\Operator\Math\AddOperator;
use RuleEngine\Operator\Math\MultiplyOperator;
use RuleEngine\Operator\Set\InOperator;
use RuleEngine\Rule\Rule;

test('evaluates nested and or conditions', function (): void {
    // (age > 18 AND verified) OR role IN ['admin', 'moderator']
    $ageCheck = new OperatorExpression(
        new GreaterThanOperator(),
        [new VariableExpression('age'), new LiteralExpression(18)]
    );

    $verifiedCheck = new VariableExpression('verified');

    $ageAndVerified = new OperatorExpression(
        new AndOperator(),
        [$ageCheck, $verifiedCheck]
    );

    $roleCheck = new OperatorExpression(
        new InOperator(),
        [
            new VariableExpression('role'),
            new LiteralExpression(['admin', 'moderator']),
        ]
    );

    $condition = new OperatorExpression(
        new OrOperator(),
        [$ageAndVerified, $roleCheck]
    );

    $rule = new Rule('access', $condition);

    // Adult + verified = pass
    expect($rule->evaluate(Context::fromArray([
        'age' => 25,
        'verified' => true,
        'role' => 'user',
    ])))->toBeTrue();

    // Not adult but admin = pass
    expect($rule->evaluate(Context::fromArray([
        'age' => 16,
        'verified' => false,
        'role' => 'admin',
    ])))->toBeTrue();

    // Not adult, not verified, not admin = fail
    expect($rule->evaluate(Context::fromArray([
        'age' => 16,
        'verified' => false,
        'role' => 'user',
    ])))->toBeFalse();
});

test('evaluates math expressions in conditions', function (): void {
    // (price * quantity) + shipping > 100
    $subtotal = new OperatorExpression(
        new MultiplyOperator(),
        [
            new VariableExpression('price'),
            new VariableExpression('quantity'),
        ]
    );

    $total = new OperatorExpression(
        new AddOperator(),
        [$subtotal, new VariableExpression('shipping')]
    );

    $condition = new OperatorExpression(
        new GreaterThanOperator(),
        [$total, new LiteralExpression(100)]
    );

    $rule = new Rule('free_shipping_eligible', $condition);

    // 25*4 + 10 = 110 > 100
    expect($rule->evaluate(Context::fromArray([
        'price' => 25,
        'quantity' => 4,
        'shipping' => 10,
    ])))->toBeTrue();

    // 10*2 + 5 = 25 < 100
    expect($rule->evaluate(Context::fromArray([
        'price' => 10,
        'quantity' => 2,
        'shipping' => 5,
    ])))->toBeFalse();
});

test('deeply nested expressions', function (): void {
    // ((a > 10 AND b > 20) OR (c > 30 AND d > 40)) AND e > 50
    $aCheck = new OperatorExpression(
        new GreaterThanOperator(),
        [new VariableExpression('a'), new LiteralExpression(10)]
    );

    $bCheck = new OperatorExpression(
        new GreaterThanOperator(),
        [new VariableExpression('b'), new LiteralExpression(20)]
    );

    $cCheck = new OperatorExpression(
        new GreaterThanOperator(),
        [new VariableExpression('c'), new LiteralExpression(30)]
    );

    $dCheck = new OperatorExpression(
        new GreaterThanOperator(),
        [new VariableExpression('d'), new LiteralExpression(40)]
    );

    $eCheck = new OperatorExpression(
        new GreaterThanOperator(),
        [new VariableExpression('e'), new LiteralExpression(50)]
    );

    $aAndB = new OperatorExpression(new AndOperator(), [$aCheck, $bCheck]);
    $cAndD = new OperatorExpression(new AndOperator(), [$cCheck, $dCheck]);
    $aAndBOrCAndD = new OperatorExpression(new OrOperator(), [$aAndB, $cAndD]);
    $finalCondition = new OperatorExpression(new AndOperator(), [$aAndBOrCAndD, $eCheck]);

    $rule = new Rule('complex', $finalCondition);

    // First group passes (a=15, b=25), e passes (e=60)
    expect($rule->evaluate(Context::fromArray([
        'a' => 15,
        'b' => 25,
        'c' => 5,
        'd' => 5,
        'e' => 60,
    ])))->toBeTrue();

    // Second group passes (c=35, d=45), e passes (e=60)
    expect($rule->evaluate(Context::fromArray([
        'a' => 5,
        'b' => 5,
        'c' => 35,
        'd' => 45,
        'e' => 60,
    ])))->toBeTrue();

    // First group passes but e fails
    expect($rule->evaluate(Context::fromArray([
        'a' => 15,
        'b' => 25,
        'c' => 5,
        'd' => 5,
        'e' => 40,
    ])))->toBeFalse();

    // All fail
    expect($rule->evaluate(Context::fromArray([
        'a' => 5,
        'b' => 5,
        'c' => 5,
        'd' => 5,
        'e' => 40,
    ])))->toBeFalse();
});

test('complex math expressions', function (): void {
    // ((base * rate) + bonus) - tax > threshold
    $baseTimesRate = new OperatorExpression(
        new MultiplyOperator(),
        [new VariableExpression('base'), new VariableExpression('rate')]
    );

    $plusBonus = new OperatorExpression(
        new AddOperator(),
        [$baseTimesRate, new VariableExpression('bonus')]
    );

    $minusTax = new OperatorExpression(
        new \RuleEngine\Operator\Math\SubtractOperator(),
        [$plusBonus, new VariableExpression('tax')]
    );

    $condition = new OperatorExpression(
        new GreaterThanOperator(),
        [$minusTax, new VariableExpression('threshold')]
    );

    $rule = new Rule('earnings_check', $condition);

    // (1000 * 1.5) + 200 - 150 > 1000
    // 1500 + 200 - 150 = 1550 > 1000 ✓
    expect($rule->evaluate(Context::fromArray([
        'base' => 1000,
        'rate' => 1.5,
        'bonus' => 200,
        'tax' => 150,
        'threshold' => 1000,
    ])))->toBeTrue();

    // (500 * 1.2) + 100 - 50 > 1000
    // 600 + 100 - 50 = 650 < 1000 ✗
    expect($rule->evaluate(Context::fromArray([
        'base' => 500,
        'rate' => 1.2,
        'bonus' => 100,
        'tax' => 50,
        'threshold' => 1000,
    ])))->toBeFalse();
});

test('set operations with complex conditions', function (): void {
    // role IN ['admin', 'moderator'] AND permissions CONTAINS 'write'
    $roleCheck = new OperatorExpression(
        new InOperator(),
        [
            new VariableExpression('role'),
            new LiteralExpression(['admin', 'moderator']),
        ]
    );

    $permissionCheck = new OperatorExpression(
        new \RuleEngine\Operator\Set\ContainsOperator(),
        [
            new VariableExpression('permissions'),
            new LiteralExpression('write'),
        ]
    );

    $condition = new OperatorExpression(
        new AndOperator(),
        [$roleCheck, $permissionCheck]
    );

    $rule = new Rule('can_write', $condition);

    // Both conditions pass
    expect($rule->evaluate(Context::fromArray([
        'role' => 'admin',
        'permissions' => ['read', 'write', 'delete'],
    ])))->toBeTrue();

    // Role not in list
    expect($rule->evaluate(Context::fromArray([
        'role' => 'user',
        'permissions' => ['read', 'write'],
    ])))->toBeFalse();

    // Permissions doesn't contain 'write'
    expect($rule->evaluate(Context::fromArray([
        'role' => 'admin',
        'permissions' => ['read', 'delete'],
    ])))->toBeFalse();
});

test('string operations with logical combinations', function (): void {
    // email ENDS_WITH '@company.com' AND name STARTS_WITH 'John'
    $emailCheck = new OperatorExpression(
        new \RuleEngine\Operator\String\EndsWithOperator(),
        [
            new VariableExpression('email'),
            new LiteralExpression('@company.com'),
        ]
    );

    $nameCheck = new OperatorExpression(
        new \RuleEngine\Operator\String\StartsWithOperator(),
        [
            new VariableExpression('name'),
            new LiteralExpression('John'),
        ]
    );

    $condition = new OperatorExpression(
        new AndOperator(),
        [$emailCheck, $nameCheck]
    );

    $rule = new Rule('specific_user', $condition);

    // Both pass
    expect($rule->evaluate(Context::fromArray([
        'email' => 'john.doe@company.com',
        'name' => 'John Doe',
    ])))->toBeTrue();

    // Email fails
    expect($rule->evaluate(Context::fromArray([
        'email' => 'john.doe@gmail.com',
        'name' => 'John Doe',
    ])))->toBeFalse();

    // Name fails
    expect($rule->evaluate(Context::fromArray([
        'email' => 'jane.smith@company.com',
        'name' => 'Jane Smith',
    ])))->toBeFalse();
});

test('mixed operator types', function (): void {
    // (age > 18 AND salary > 50000) AND role IN ['engineer', 'manager'] AND email ENDS_WITH '@tech.com'
    $ageCheck = new OperatorExpression(
        new GreaterThanOperator(),
        [new VariableExpression('age'), new LiteralExpression(18)]
    );

    $salaryCheck = new OperatorExpression(
        new GreaterThanOperator(),
        [new VariableExpression('salary'), new LiteralExpression(50000)]
    );

    $roleCheck = new OperatorExpression(
        new InOperator(),
        [
            new VariableExpression('role'),
            new LiteralExpression(['engineer', 'manager']),
        ]
    );

    $emailCheck = new OperatorExpression(
        new \RuleEngine\Operator\String\EndsWithOperator(),
        [
            new VariableExpression('email'),
            new LiteralExpression('@tech.com'),
        ]
    );

    $ageAndSalary = new OperatorExpression(new AndOperator(), [$ageCheck, $salaryCheck]);
    $ageAndSalaryAndRole = new OperatorExpression(new AndOperator(), [$ageAndSalary, $roleCheck]);
    $finalCondition = new OperatorExpression(new AndOperator(), [$ageAndSalaryAndRole, $emailCheck]);

    $rule = new Rule('qualified_candidate', $finalCondition);

    // All conditions pass
    expect($rule->evaluate(Context::fromArray([
        'age' => 30,
        'salary' => 75000,
        'role' => 'engineer',
        'email' => 'john@tech.com',
    ])))->toBeTrue();

    // Age fails
    expect($rule->evaluate(Context::fromArray([
        'age' => 17,
        'salary' => 75000,
        'role' => 'engineer',
        'email' => 'john@tech.com',
    ])))->toBeFalse();

    // Salary fails
    expect($rule->evaluate(Context::fromArray([
        'age' => 30,
        'salary' => 40000,
        'role' => 'engineer',
        'email' => 'john@tech.com',
    ])))->toBeFalse();

    // Role fails
    expect($rule->evaluate(Context::fromArray([
        'age' => 30,
        'salary' => 75000,
        'role' => 'intern',
        'email' => 'john@tech.com',
    ])))->toBeFalse();

    // Email fails
    expect($rule->evaluate(Context::fromArray([
        'age' => 30,
        'salary' => 75000,
        'role' => 'engineer',
        'email' => 'john@gmail.com',
    ])))->toBeFalse();
});
