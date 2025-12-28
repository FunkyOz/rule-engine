<?php

declare(strict_types=1);

namespace RuleEngine\Context;

use RuleEngine\Exception\VariableNotFoundException;

final class Context implements ContextInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        private array $data = []
    ) {
    }

    public function get(string $name): mixed
    {
        // Support dot notation: 'user.profile.name'
        if (str_contains($name, '.')) {
            return $this->getNestedValue($name);
        }

        if (! $this->has($name)) {
            throw new VariableNotFoundException($name);
        }

        return $this->data[$name];
    }

    public function has(string $name): bool
    {
        if (str_contains($name, '.')) {
            return $this->hasNestedValue($name);
        }

        return array_key_exists($name, $this->data);
    }

    public function set(string $name, mixed $value): void
    {
        if (str_contains($name, '.')) {
            $this->setNestedValue($name, $value);

            return;
        }

        $this->data[$name] = $value;
    }

    public function all(): array
    {
        return $this->data;
    }

    /**
     * Create a new context by merging with another.
     */
    public function merge(ContextInterface $other): self
    {
        return new self(array_merge($this->data, $other->all()));
    }

    /**
     * Create a context from an array.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    private function getNestedValue(string $path): mixed
    {
        $keys = explode('.', $path);
        $value = $this->data;

        foreach ($keys as $key) {
            if (is_array($value) && array_key_exists($key, $value)) {
                $value = $value[$key];
            } elseif (is_object($value) && property_exists($value, $key)) {
                $value = $value->{$key};
            } else {
                throw new VariableNotFoundException($path);
            }
        }

        return $value;
    }

    private function hasNestedValue(string $path): bool
    {
        try {
            $this->getNestedValue($path);

            return true;
        } catch (VariableNotFoundException) {
            return false;
        }
    }

    private function setNestedValue(string $path, mixed $value): void
    {
        $keys = explode('.', $path);
        $lastKey = array_pop($keys);
        $current = &$this->data;

        foreach ($keys as $key) {
            if (! isset($current[$key]) || ! is_array($current[$key])) {
                $current[$key] = [];
            }
            $current = &$current[$key];
        }

        $current[$lastKey] = $value;
    }
}
