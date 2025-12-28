<?php

declare(strict_types=1);

namespace RuleEngine\Registry;

use RuleEngine\Exception\OperatorNotFoundException;
use RuleEngine\Operator\OperatorInterface;

interface OperatorRegistryInterface
{
    /**
     * Register an operator.
     */
    public function register(OperatorInterface $operator): void;

    /**
     * Get an operator by name.
     *
     * @throws OperatorNotFoundException
     */
    public function get(string $name): OperatorInterface;

    /**
     * Check if an operator is registered.
     */
    public function has(string $name): bool;

    /**
     * Get all registered operator names.
     *
     * @return array<string>
     */
    public function names(): array;
}
