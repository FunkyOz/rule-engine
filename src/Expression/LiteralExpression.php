<?php

declare(strict_types=1);

namespace RuleEngine\Expression;

use RuleEngine\Context\ContextInterface;

final class LiteralExpression implements ExpressionInterface
{
    public function __construct(
        private readonly mixed $value
    ) {
    }

    public function evaluate(ContextInterface $context): mixed
    {
        return $this->value;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return match (true) {
            is_null($this->value) => 'null',
            is_bool($this->value) => $this->value ? 'true' : 'false',
            is_string($this->value) => '"'.addslashes($this->value).'"',
            is_array($this->value) => '['.implode(', ', array_map(
                fn ($v) => (new self($v))->__toString(),
                $this->value
            )).']',
            default => (string) $this->value,
        };
    }
}
