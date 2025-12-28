#!/usr/bin/env php
<?php

/**
 * Custom Operators Example
 *
 * This example demonstrates how to create and register custom operators.
 */

require_once __DIR__.'/../vendor/autoload.php';

use RuleEngine\Expression\LiteralExpression;
use RuleEngine\Expression\OperatorExpression;
use RuleEngine\Expression\VariableExpression;
use RuleEngine\Operator\OperatorInterface;
use RuleEngine\RuleEngine;

echo "=== Custom Operators Example ===\n\n";

// Define custom operator: IS_EVEN
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

// Define custom operator: IS_PRIME
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

        if (! is_int($value) || $value < 2) {
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

// Define custom operator: BETWEEN
class BetweenOperator implements OperatorInterface
{
    public function getName(): string
    {
        return 'BETWEEN';
    }

    public function getArity(): int
    {
        return 3;
    }

    public function execute(array $operands): bool
    {
        $value = $operands[0];
        $min = $operands[1];
        $max = $operands[2];

        return $value >= $min && $value <= $max;
    }
}

// Define custom operator: IS_PALINDROME
class IsPalindromeOperator implements OperatorInterface
{
    public function getName(): string
    {
        return 'IS_PALINDROME';
    }

    public function getArity(): int
    {
        return 1;
    }

    public function execute(array $operands): bool
    {
        $value = (string) $operands[0];
        $cleaned = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $value));

        return $cleaned === strrev($cleaned);
    }
}

// Create engine and register custom operators
$engine = RuleEngine::create();
$engine->registerOperator(new IsEvenOperator());
$engine->registerOperator(new IsPrimeOperator());
$engine->registerOperator(new BetweenOperator());
$engine->registerOperator(new IsPalindromeOperator());

echo "Registered Custom Operators:\n";
echo "- IS_EVEN: Check if a number is even\n";
echo "- IS_PRIME: Check if a number is prime\n";
echo "- BETWEEN: Check if a value is between min and max\n";
echo "- IS_PALINDROME: Check if a string is a palindrome\n\n";

// Example 1: Using IS_EVEN operator
echo "Example 1: IS_EVEN Operator\n";
echo str_repeat('-', 50)."\n";

$evenExpression = new OperatorExpression(
    new IsEvenOperator(),
    [new VariableExpression('number')]
);

$testNumbers = [2, 3, 10, 15, 100];
foreach ($testNumbers as $num) {
    $result = $engine->evaluateExpression($evenExpression, ['number' => $num]);
    echo "Is {$num} even? ".($result ? 'Yes' : 'No')."\n";
}
echo "\n";

// Example 2: Using IS_PRIME operator
echo "Example 2: IS_PRIME Operator\n";
echo str_repeat('-', 50)."\n";

$primeExpression = new OperatorExpression(
    new IsPrimeOperator(),
    [new VariableExpression('number')]
);

$testNumbers = [2, 3, 4, 5, 11, 15, 17, 20];
foreach ($testNumbers as $num) {
    $result = $engine->evaluateExpression($primeExpression, ['number' => $num]);
    echo "Is {$num} prime? ".($result ? 'Yes' : 'No')."\n";
}
echo "\n";

// Example 3: Using BETWEEN operator
echo "Example 3: BETWEEN Operator\n";
echo str_repeat('-', 50)."\n";

$betweenExpression = new OperatorExpression(
    new BetweenOperator(),
    [
        new VariableExpression('score'),
        new LiteralExpression(60),
        new LiteralExpression(100),
    ]
);

$testScores = [45, 60, 75, 85, 100, 105];
foreach ($testScores as $score) {
    $result = $engine->evaluateExpression($betweenExpression, ['score' => $score]);
    echo "Is score {$score} between 60-100? ".($result ? 'Yes (Pass)' : 'No (Fail)')."\n";
}
echo "\n";

// Example 4: Using IS_PALINDROME operator
echo "Example 4: IS_PALINDROME Operator\n";
echo str_repeat('-', 50)."\n";

$palindromeExpression = new OperatorExpression(
    new IsPalindromeOperator(),
    [new VariableExpression('text')]
);

$testStrings = ['racecar', 'hello', 'A man a plan a canal Panama', 'test', 'madam'];
foreach ($testStrings as $str) {
    $result = $engine->evaluateExpression($palindromeExpression, ['text' => $str]);
    echo "Is '{$str}' a palindrome? ".($result ? 'Yes' : 'No')."\n";
}
echo "\n";

// Example 5: Combining custom operators in rules
echo "Example 5: Combining Custom Operators in Rules\n";
echo str_repeat('-', 50)."\n";

// Create a rule using custom operators directly
$primeInRangeCondition = new OperatorExpression(
    $engine->getRegistry()->get('AND'),
    [
        new OperatorExpression(
            new IsPrimeOperator(),
            [new VariableExpression('candidate')]
        ),
        new OperatorExpression(
            new BetweenOperator(),
            [
                new VariableExpression('candidate'),
                new LiteralExpression(10),
                new LiteralExpression(50),
            ]
        ),
    ]
);

$rule = $engine->builder()
    ->name('special_number')
    ->condition($primeInRangeCondition)
    ->meta('description', 'Prime number between 10 and 50')
    ->build();

$engine->addRule($rule);

$testCandidates = [7, 11, 15, 17, 20, 23, 29, 51];
echo "Finding special numbers (prime and between 10-50):\n";
foreach ($testCandidates as $candidate) {
    $result = $engine->evaluate('special_number', ['candidate' => $candidate]);
    if ($result) {
        echo "✓ {$candidate} is a special number\n";
    } else {
        echo "✗ {$candidate} is not a special number\n";
    }
}

echo "\n=== End of Custom Operators Example ===\n";
