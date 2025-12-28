<?php

declare(strict_types=1);

use RuleEngine\Context\Context;
use RuleEngine\Expression\LiteralExpression;
use RuleEngine\Expression\OperatorExpression;
use RuleEngine\Expression\VariableExpression;
use RuleEngine\Operator\OperatorInterface;

function createMockOperatorForExpression(string $name, int $arity, callable $executor): OperatorInterface
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

test('evaluates binary operator', function (): void {
    $operator = createMockOperatorForExpression('ADD', 2, fn ($ops) => $ops[0] + $ops[1]);
    $expression = new OperatorExpression(
        $operator,
        [
            new LiteralExpression(5),
            new LiteralExpression(3),
        ]
    );
    $context = new Context();

    $result = $expression->evaluate($context);

    expect($result)->toBe(8);
});

test('evaluates unary operator', function (): void {
    $operator = createMockOperatorForExpression('NOT', 1, fn ($ops) => ! $ops[0]);
    $expression = new OperatorExpression(
        $operator,
        [new LiteralExpression(false)]
    );
    $context = new Context();

    $result = $expression->evaluate($context);

    expect($result)->toBeTrue();
});

test('evaluates variadic operator', function (): void {
    $operator = createMockOperatorForExpression('SUM', -1, fn ($ops) => array_sum($ops));
    $expression = new OperatorExpression(
        $operator,
        [
            new LiteralExpression(1),
            new LiteralExpression(2),
            new LiteralExpression(3),
            new LiteralExpression(4),
        ]
    );
    $context = new Context();

    $result = $expression->evaluate($context);

    expect($result)->toBe(10);
});

test('evaluates operands before execution', function (): void {
    $operator = createMockOperatorForExpression('MULTIPLY', 2, fn ($ops) => $ops[0] * $ops[1]);
    $expression = new OperatorExpression(
        $operator,
        [
            new VariableExpression('x'),
            new VariableExpression('y'),
        ]
    );
    $context = Context::fromArray(['x' => 6, 'y' => 7]);

    $result = $expression->evaluate($context);

    expect($result)->toBe(42);
});

test('evaluates nested operator expressions', function (): void {
    $addOp = createMockOperatorForExpression('ADD', 2, fn ($ops) => $ops[0] + $ops[1]);
    $multiplyOp = createMockOperatorForExpression('MUL', 2, fn ($ops) => $ops[0] * $ops[1]);

    // Expression: (2 + 3) * (4 + 1) = 5 * 5 = 25
    $expression = new OperatorExpression(
        $multiplyOp,
        [
            new OperatorExpression(
                $addOp,
                [
                    new LiteralExpression(2),
                    new LiteralExpression(3),
                ]
            ),
            new OperatorExpression(
                $addOp,
                [
                    new LiteralExpression(4),
                    new LiteralExpression(1),
                ]
            ),
        ]
    );
    $context = new Context();

    $result = $expression->evaluate($context);

    expect($result)->toBe(25);
});

test('get operator returns operator', function (): void {
    $operator = createMockOperatorForExpression('TEST', 2, fn ($ops) => null);
    $expression = new OperatorExpression($operator, []);

    expect($expression->getOperator())->toBe($operator);
});

test('get operands returns operands', function (): void {
    $operator = createMockOperatorForExpression('TEST', 2, fn ($ops) => null);
    $operands = [
        new LiteralExpression(1),
        new LiteralExpression(2),
    ];
    $expression = new OperatorExpression($operator, $operands);

    expect($expression->getOperands())->toBe($operands);
});

test('to string with unary operator', function (): void {
    $operator = createMockOperatorForExpression('NOT', 1, fn ($ops) => ! $ops[0]);
    $expression = new OperatorExpression(
        $operator,
        [new LiteralExpression(true)]
    );

    expect((string) $expression)->toBe('NOT(true)');
});

test('to string with binary operator', function (): void {
    $operator = createMockOperatorForExpression('ADD', 2, fn ($ops) => $ops[0] + $ops[1]);
    $expression = new OperatorExpression(
        $operator,
        [
            new LiteralExpression(5),
            new LiteralExpression(3),
        ]
    );

    expect((string) $expression)->toBe('(5 ADD 3)');
});

test('to string with variadic operator', function (): void {
    $operator = createMockOperatorForExpression('SUM', -1, fn ($ops) => array_sum($ops));
    $expression = new OperatorExpression(
        $operator,
        [
            new LiteralExpression(1),
            new LiteralExpression(2),
            new LiteralExpression(3),
        ]
    );

    expect((string) $expression)->toBe('SUM(1, 2, 3)');
});

test('to string with nested expression', function (): void {
    $addOp = createMockOperatorForExpression('ADD', 2, fn ($ops) => $ops[0] + $ops[1]);
    $mulOp = createMockOperatorForExpression('MUL', 2, fn ($ops) => $ops[0] * $ops[1]);

    // Expression: (2 + 3) * 4
    $expression = new OperatorExpression(
        $mulOp,
        [
            new OperatorExpression(
                $addOp,
                [
                    new LiteralExpression(2),
                    new LiteralExpression(3),
                ]
            ),
            new LiteralExpression(4),
        ]
    );

    expect((string) $expression)->toBe('((2 ADD 3) MUL 4)');
});

test('to string with variable operands', function (): void {
    $operator = createMockOperatorForExpression('EQ', 2, fn ($ops) => $ops[0] === $ops[1]);
    $expression = new OperatorExpression(
        $operator,
        [
            new VariableExpression('user.name'),
            new LiteralExpression('John'),
        ]
    );

    expect((string) $expression)->toBe('($user.name EQ "John")');
});
