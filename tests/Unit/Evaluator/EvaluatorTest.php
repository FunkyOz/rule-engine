<?php

declare(strict_types=1);

use RuleEngine\Context\Context;
use RuleEngine\Evaluator\Evaluator;
use RuleEngine\Expression\LiteralExpression;
use RuleEngine\Expression\OperatorExpression;
use RuleEngine\Expression\VariableExpression;
use RuleEngine\Operator\OperatorInterface;

function createMockOperatorForEvaluator(string $name, int $arity, callable $executor): OperatorInterface
{
    return new class ($name, $arity, $executor) implements OperatorInterface {
        public function __construct(
            private string $name,
            private int $arity,
            private $executor
        ) {
        }

        public function getName(): string
        {
            return $this->name;
        }

        public function getArity(): int
        {
            return $this->arity;
        }

        public function execute(array $operands): mixed
        {
            return ($this->executor)($operands);
        }
    };
}

beforeEach(function (): void {
    $this->evaluator = new Evaluator();
});

test('evaluates literal expression', function (): void {
    $expression = new LiteralExpression(42);
    $context = new Context();

    $result = $this->evaluator->evaluate($expression, $context);

    expect($result)->toBe(42);
});

test('evaluates variable expression', function (): void {
    $expression = new VariableExpression('name');
    $context = Context::fromArray(['name' => 'John']);

    $result = $this->evaluator->evaluate($expression, $context);

    expect($result)->toBe('John');
});

test('evaluates operator expression', function (): void {
    $operator = createMockOperatorForEvaluator('ADD', 2, fn ($ops) => $ops[0] + $ops[1]);
    $expression = new OperatorExpression(
        $operator,
        [
            new LiteralExpression(10),
            new LiteralExpression(20),
        ]
    );
    $context = new Context();

    $result = $this->evaluator->evaluate($expression, $context);

    expect($result)->toBe(30);
});

test('evaluates nested operator expression', function (): void {
    $addOp = createMockOperatorForEvaluator('ADD', 2, fn ($ops) => $ops[0] + $ops[1]);
    $multiplyOp = createMockOperatorForEvaluator('MUL', 2, fn ($ops) => $ops[0] * $ops[1]);

    // Expression: (5 + 3) * 2 = 16
    $expression = new OperatorExpression(
        $multiplyOp,
        [
            new OperatorExpression(
                $addOp,
                [
                    new LiteralExpression(5),
                    new LiteralExpression(3),
                ]
            ),
            new LiteralExpression(2),
        ]
    );
    $context = new Context();

    $result = $this->evaluator->evaluate($expression, $context);

    expect($result)->toBe(16);
});

test('evaluate as boolean with true value', function (): void {
    $expression = new LiteralExpression(1);
    $context = new Context();

    $result = $this->evaluator->evaluateAsBoolean($expression, $context);

    expect($result)->toBeTrue();
});

test('evaluate as boolean with false value', function (): void {
    $expression = new LiteralExpression(0);
    $context = new Context();

    $result = $this->evaluator->evaluateAsBoolean($expression, $context);

    expect($result)->toBeFalse();
});

test('evaluate as boolean with truthy string', function (): void {
    $expression = new LiteralExpression('hello');
    $context = new Context();

    $result = $this->evaluator->evaluateAsBoolean($expression, $context);

    expect($result)->toBeTrue();
});

test('evaluate as boolean with empty string', function (): void {
    $expression = new LiteralExpression('');
    $context = new Context();

    $result = $this->evaluator->evaluateAsBoolean($expression, $context);

    expect($result)->toBeFalse();
});

test('evaluate all with multiple expressions', function (): void {
    $expressions = [
        new LiteralExpression(10),
        new LiteralExpression(20),
        new VariableExpression('value'),
    ];
    $context = Context::fromArray(['value' => 30]);

    $results = $this->evaluator->evaluateAll($expressions, $context);

    expect($results)->toBe([10, 20, 30]);
});

test('evaluate all with empty array', function (): void {
    $expressions = [];
    $context = new Context();

    $results = $this->evaluator->evaluateAll($expressions, $context);

    expect($results)->toBe([]);
});

test('evaluate all with operator expressions', function (): void {
    $addOp = createMockOperatorForEvaluator('ADD', 2, fn ($ops) => $ops[0] + $ops[1]);

    $expressions = [
        new OperatorExpression($addOp, [new LiteralExpression(1), new LiteralExpression(2)]),
        new OperatorExpression($addOp, [new LiteralExpression(10), new LiteralExpression(5)]),
    ];
    $context = new Context();

    $results = $this->evaluator->evaluateAll($expressions, $context);

    expect($results)->toBe([3, 15]);
});
