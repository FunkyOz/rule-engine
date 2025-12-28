<?php

declare(strict_types=1);

namespace RuleEngine\Context;

use RuleEngine\Exception\VariableNotFoundException;

interface ContextInterface
{
    /**
     * Get a value by name from the context.
     *
     * @throws VariableNotFoundException
     */
    public function get(string $name): mixed;

    /**
     * Check if a variable exists in the context.
     */
    public function has(string $name): bool;

    /**
     * Set a value in the context.
     */
    public function set(string $name, mixed $value): void;

    /**
     * Get all variables as an array.
     *
     * @return array<string, mixed>
     */
    public function all(): array;
}
