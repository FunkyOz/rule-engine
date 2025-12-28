<?php

declare(strict_types=1);

namespace RuleEngine\Registry;

use RuleEngine\Exception\OperatorNotFoundException;
use RuleEngine\Operator\OperatorInterface;

final class OperatorRegistry implements OperatorRegistryInterface
{
    /**
     * @var array<string, OperatorInterface>
     */
    private array $operators = [];

    public function register(OperatorInterface $operator): void
    {
        $this->operators[$operator->getName()] = $operator;
    }

    /**
     * Register multiple operators at once.
     *
     * @param array<OperatorInterface> $operators
     */
    public function registerMany(array $operators): void
    {
        foreach ($operators as $operator) {
            $this->register($operator);
        }
    }

    public function get(string $name): OperatorInterface
    {
        if (!$this->has($name)) {
            throw new OperatorNotFoundException($name);
        }

        return $this->operators[$name];
    }

    public function has(string $name): bool
    {
        return isset($this->operators[$name]);
    }

    public function names(): array
    {
        return array_keys($this->operators);
    }

    /**
     * Get all registered operators.
     *
     * @return array<string, OperatorInterface>
     */
    public function all(): array
    {
        return $this->operators;
    }

    /**
     * Create a registry with default operators pre-registered.
     */
    public static function withDefaults(): self
    {
        $registry = new self();

        // Register all default operators
        // This will be populated as operators are implemented

        return $registry;
    }
}
