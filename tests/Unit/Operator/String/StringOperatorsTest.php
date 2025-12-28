<?php

declare(strict_types=1);

use RuleEngine\Exception\InvalidRegexException;
use RuleEngine\Operator\String\ContainsStringOperator;
use RuleEngine\Operator\String\EndsWithOperator;
use RuleEngine\Operator\String\MatchesOperator;
use RuleEngine\Operator\String\StartsWithOperator;

// StartsWithOperator Tests
test('StartsWithOperator name', function (): void {
    $operator = new StartsWithOperator();
    expect($operator->getName())->toBe('STARTS_WITH');
});

test('StartsWithOperator is binary', function (): void {
    $operator = new StartsWithOperator();
    expect($operator->getArity())->toBe(2);
});

test('StartsWithOperator returns true when string starts with prefix', function (): void {
    $operator = new StartsWithOperator();
    expect($operator->execute(['hello world', 'hello']))->toBeTrue();
    expect($operator->execute(['test123', 'test']))->toBeTrue();
});

test('StartsWithOperator returns false when string does not start with prefix', function (): void {
    $operator = new StartsWithOperator();
    expect($operator->execute(['hello world', 'world']))->toBeFalse();
    expect($operator->execute(['test123', '123']))->toBeFalse();
});

test('StartsWithOperator is case sensitive by default', function (): void {
    $operator = new StartsWithOperator();
    expect($operator->execute(['Hello', 'Hello']))->toBeTrue();
    expect($operator->execute(['Hello', 'hello']))->toBeFalse();
});

test('StartsWithOperator case insensitive mode', function (): void {
    $operator = new StartsWithOperator(caseSensitive: false);
    expect($operator->execute(['Hello World', 'hello']))->toBeTrue();
    expect($operator->execute(['HELLO', 'hello']))->toBeTrue();
});

test('StartsWithOperator handles unicode', function (): void {
    $operator = new StartsWithOperator();
    expect($operator->execute(['Héllo world', 'Héllo']))->toBeTrue();

    $operatorInsensitive = new StartsWithOperator(caseSensitive: false);
    expect($operatorInsensitive->execute(['Héllo', 'héllo']))->toBeTrue();
});

test('StartsWithOperator casts to string', function (): void {
    $operator = new StartsWithOperator();
    expect($operator->execute([12345, '123']))->toBeTrue();
});

test('StartsWithOperator throws on invalid operand count', function (): void {
    $operator = new StartsWithOperator();
    $operator->execute(['hello']);
})->throws(InvalidArgumentException::class);

// EndsWithOperator Tests
test('EndsWithOperator name', function (): void {
    $operator = new EndsWithOperator();
    expect($operator->getName())->toBe('ENDS_WITH');
});

test('EndsWithOperator is binary', function (): void {
    $operator = new EndsWithOperator();
    expect($operator->getArity())->toBe(2);
});

test('EndsWithOperator returns true when string ends with suffix', function (): void {
    $operator = new EndsWithOperator();
    expect($operator->execute(['hello world', 'world']))->toBeTrue();
    expect($operator->execute(['test123', '123']))->toBeTrue();
});

test('EndsWithOperator returns false when string does not end with suffix', function (): void {
    $operator = new EndsWithOperator();
    expect($operator->execute(['hello world', 'hello']))->toBeFalse();
    expect($operator->execute(['test123', 'test']))->toBeFalse();
});

test('EndsWithOperator is case sensitive by default', function (): void {
    $operator = new EndsWithOperator();
    expect($operator->execute(['World', 'World']))->toBeTrue();
    expect($operator->execute(['World', 'world']))->toBeFalse();
});

test('EndsWithOperator case insensitive mode', function (): void {
    $operator = new EndsWithOperator(caseSensitive: false);
    expect($operator->execute(['Hello World', 'WORLD']))->toBeTrue();
    expect($operator->execute(['test', 'TEST']))->toBeTrue();
});

test('EndsWithOperator handles unicode', function (): void {
    $operator = new EndsWithOperator();
    expect($operator->execute(['hello wörld', 'wörld']))->toBeTrue();

    $operatorInsensitive = new EndsWithOperator(caseSensitive: false);
    expect($operatorInsensitive->execute(['wörld', 'WÖRLD']))->toBeTrue();
});

test('EndsWithOperator casts to string', function (): void {
    $operator = new EndsWithOperator();
    expect($operator->execute([12345, '345']))->toBeTrue();
});

test('EndsWithOperator throws on invalid operand count', function (): void {
    $operator = new EndsWithOperator();
    $operator->execute(['hello']);
})->throws(InvalidArgumentException::class);

// ContainsStringOperator Tests
test('ContainsStringOperator name', function (): void {
    $operator = new ContainsStringOperator();
    expect($operator->getName())->toBe('CONTAINS_STRING');
});

test('ContainsStringOperator is binary', function (): void {
    $operator = new ContainsStringOperator();
    expect($operator->getArity())->toBe(2);
});

test('ContainsStringOperator returns true when string contains substring', function (): void {
    $operator = new ContainsStringOperator();
    expect($operator->execute(['hello world', 'lo wo']))->toBeTrue();
    expect($operator->execute(['test123', '12']))->toBeTrue();
});

test('ContainsStringOperator returns false when string does not contain substring', function (): void {
    $operator = new ContainsStringOperator();
    expect($operator->execute(['hello world', 'xyz']))->toBeFalse();
    expect($operator->execute(['test123', 'abc']))->toBeFalse();
});

test('ContainsStringOperator is case sensitive by default', function (): void {
    $operator = new ContainsStringOperator();
    expect($operator->execute(['Hello World', 'Hello']))->toBeTrue();
    expect($operator->execute(['Hello World', 'hello']))->toBeFalse();
});

test('ContainsStringOperator case insensitive mode', function (): void {
    $operator = new ContainsStringOperator(caseSensitive: false);
    expect($operator->execute(['Hello World', 'hello']))->toBeTrue();
    expect($operator->execute(['HELLO WORLD', 'world']))->toBeTrue();
});

test('ContainsStringOperator handles unicode', function (): void {
    $operator = new ContainsStringOperator();
    expect($operator->execute(['hello wörld', 'wör']))->toBeTrue();

    $operatorInsensitive = new ContainsStringOperator(caseSensitive: false);
    expect($operatorInsensitive->execute(['Héllo Wörld', 'héllo']))->toBeTrue();
});

test('ContainsStringOperator casts to string', function (): void {
    $operator = new ContainsStringOperator();
    expect($operator->execute([12345, '234']))->toBeTrue();
});

test('ContainsStringOperator throws on invalid operand count', function (): void {
    $operator = new ContainsStringOperator();
    $operator->execute(['hello']);
})->throws(InvalidArgumentException::class);

// MatchesOperator Tests
test('MatchesOperator name', function (): void {
    $operator = new MatchesOperator();
    expect($operator->getName())->toBe('MATCHES');
});

test('MatchesOperator is binary', function (): void {
    $operator = new MatchesOperator();
    expect($operator->getArity())->toBe(2);
});

test('MatchesOperator returns true when string matches pattern', function (): void {
    $operator = new MatchesOperator();
    expect($operator->execute(['hello', '/^hello$/']))->toBeTrue();
    expect($operator->execute(['test123', '/^test\d+$/']))->toBeTrue();
    expect($operator->execute(['email@example.com', '/^[\w.]+@[\w.]+\.\w+$/']))->toBeTrue();
});

test('MatchesOperator returns false when string does not match pattern', function (): void {
    $operator = new MatchesOperator();
    expect($operator->execute(['hello', '/^world$/']))->toBeFalse();
    expect($operator->execute(['test123', '/^\d+$/']))->toBeFalse();
});

test('MatchesOperator supports complex patterns', function (): void {
    $operator = new MatchesOperator();

    // Email validation
    expect($operator->execute(['john.doe@example.com', '/^[a-z.]+@[a-z.]+\.[a-z]+$/']))->toBeTrue();

    // Phone number
    expect($operator->execute(['+1-555-123-4567', '/^\+\d{1,3}-\d{3}-\d{3}-\d{4}$/']))->toBeTrue();

    // URL
    expect($operator->execute(['https://example.com', '/^https?:\/\/[\w.-]+\.\w+$/']))->toBeTrue();
});

test('MatchesOperator supports modifiers', function (): void {
    $operator = new MatchesOperator();

    // Case-insensitive modifier
    expect($operator->execute(['Hello', '/hello/i']))->toBeTrue();

    // Multiline modifier
    expect($operator->execute(["line1\nline2", '/^line2$/m']))->toBeTrue();
});

test('MatchesOperator throws on invalid regex', function (): void {
    $operator = new MatchesOperator();
    $operator->execute(['hello', '/[/']);
})->throws(InvalidRegexException::class, 'Invalid regular expression');

test('MatchesOperator casts to string', function (): void {
    $operator = new MatchesOperator();
    expect($operator->execute([12345, '/^\d+$/']))->toBeTrue();
});

test('MatchesOperator throws on invalid operand count', function (): void {
    $operator = new MatchesOperator();
    $operator->execute(['hello']);
})->throws(InvalidArgumentException::class);

// Edge Cases
test('string operators handle empty strings', function (): void {
    $startsWith = new StartsWithOperator();
    $endsWith = new EndsWithOperator();
    $contains = new ContainsStringOperator();

    // Empty needle
    expect($startsWith->execute(['hello', '']))->toBeTrue();
    expect($endsWith->execute(['hello', '']))->toBeTrue();
    expect($contains->execute(['hello', '']))->toBeTrue();

    // Empty haystack
    expect($startsWith->execute(['', 'hello']))->toBeFalse();
    expect($endsWith->execute(['', 'hello']))->toBeFalse();
    expect($contains->execute(['', 'hello']))->toBeFalse();

    // Both empty
    expect($startsWith->execute(['', '']))->toBeTrue();
    expect($endsWith->execute(['', '']))->toBeTrue();
    expect($contains->execute(['', '']))->toBeTrue();
});
