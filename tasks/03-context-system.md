---
title: Context & Variable System
status: done
priority: Critical
description: Implement the context system for variable storage and resolution with dot notation support
---

## Objectives
- Implement `Context` class with dot notation variable access
- Create `VariableNotFoundException` for missing variables
- Support nested array/object access
- Enable context cloning and merging

## Deliverables
1. `src/Context/Context.php`
2. `src/Exception/VariableNotFoundException.php`

## Technical Details

### Context Implementation

```php
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
    ) {}

    public function get(string $name): mixed
    {
        // Support dot notation: 'user.profile.name'
        if (str_contains($name, '.')) {
            return $this->getNestedValue($name);
        }

        if (!$this->has($name)) {
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
     * @param array<string, mixed> $data
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
            if (!isset($current[$key]) || !is_array($current[$key])) {
                $current[$key] = [];
            }
            $current = &$current[$key];
        }

        $current[$lastKey] = $value;
    }
}
```

### VariableNotFoundException

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Exception;

final class VariableNotFoundException extends RuleEngineException
{
    public function __construct(string $name)
    {
        parent::__construct("Variable '{$name}' not found in context");
    }
}
```

## Dependencies
- Task 02 - Core Interfaces

## Estimated Complexity
**Medium** - Dot notation parsing and nested access require careful implementation

## Implementation Notes
- Use references for nested value setting to modify in place
- Support both arrays and objects for nested access
- The context should be immutable-friendly (merge returns new instance)
- Exception lives in `src/Exception/` folder with other exceptions

## Acceptance Criteria
- [x] `Context` implements `ContextInterface`
- [x] Dot notation access works: `$context->get('user.name')`
- [x] Nested setting works: `$context->set('user.email', 'test@example.com')`
- [x] `VariableNotFoundException` thrown for missing variables
- [x] Works with both arrays and objects
- [x] `merge()` creates new context without modifying originals
- [x] PHPStan passes at level 8
- [x] Unit tests cover all functionality
