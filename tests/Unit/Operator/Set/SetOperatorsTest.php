<?php

declare(strict_types=1);

use RuleEngine\Operator\Set\ContainsOperator;
use RuleEngine\Operator\Set\DiffOperator;
use RuleEngine\Operator\Set\InOperator;
use RuleEngine\Operator\Set\IntersectOperator;
use RuleEngine\Operator\Set\NotInOperator;
use RuleEngine\Operator\Set\SubsetOperator;
use RuleEngine\Operator\Set\UnionOperator;

// InOperator Tests
test('InOperator name', function (): void {
    $operator = new InOperator();
    expect($operator->getName())->toBe('IN');
});

test('InOperator is binary', function (): void {
    $operator = new InOperator();
    expect($operator->getArity())->toBe(2);
});

test('InOperator returns true when value is in array', function (): void {
    $operator = new InOperator();
    expect($operator->execute([5, [1, 2, 3, 4, 5]]))->toBeTrue();
    expect($operator->execute(['apple', ['apple', 'banana', 'orange']]))->toBeTrue();
});

test('InOperator returns false when value is not in array', function (): void {
    $operator = new InOperator();
    expect($operator->execute([10, [1, 2, 3, 4, 5]]))->toBeFalse();
    expect($operator->execute(['grape', ['apple', 'banana', 'orange']]))->toBeFalse();
});

test('InOperator uses strict comparison', function (): void {
    $operator = new InOperator();
    expect($operator->execute(['5', [1, 2, 3, 4, 5]]))->toBeFalse();
    expect($operator->execute([5, ['1', '2', '3', '4', '5']]))->toBeFalse();
});

test('InOperator returns false for non-array haystack', function (): void {
    $operator = new InOperator();
    expect($operator->execute([5, 'not an array']))->toBeFalse();
    expect($operator->execute([5, null]))->toBeFalse();
});

test('InOperator throws on invalid operand count', function (): void {
    $operator = new InOperator();
    $operator->execute([5]);
})->throws(InvalidArgumentException::class);

// NotInOperator Tests
test('NotInOperator name', function (): void {
    $operator = new NotInOperator();
    expect($operator->getName())->toBe('NOT_IN');
});

test('NotInOperator is binary', function (): void {
    $operator = new NotInOperator();
    expect($operator->getArity())->toBe(2);
});

test('NotInOperator returns true when value is not in array', function (): void {
    $operator = new NotInOperator();
    expect($operator->execute([10, [1, 2, 3, 4, 5]]))->toBeTrue();
    expect($operator->execute(['grape', ['apple', 'banana', 'orange']]))->toBeTrue();
});

test('NotInOperator returns false when value is in array', function (): void {
    $operator = new NotInOperator();
    expect($operator->execute([5, [1, 2, 3, 4, 5]]))->toBeFalse();
    expect($operator->execute(['apple', ['apple', 'banana', 'orange']]))->toBeFalse();
});

test('NotInOperator uses strict comparison', function (): void {
    $operator = new NotInOperator();
    expect($operator->execute(['5', [1, 2, 3, 4, 5]]))->toBeTrue();
});

test('NotInOperator returns true for non-array haystack', function (): void {
    $operator = new NotInOperator();
    expect($operator->execute([5, 'not an array']))->toBeTrue();
    expect($operator->execute([5, null]))->toBeTrue();
});

test('NotInOperator throws on invalid operand count', function (): void {
    $operator = new NotInOperator();
    $operator->execute([5]);
})->throws(InvalidArgumentException::class);

// ContainsOperator Tests
test('ContainsOperator name', function (): void {
    $operator = new ContainsOperator();
    expect($operator->getName())->toBe('CONTAINS');
});

test('ContainsOperator is binary', function (): void {
    $operator = new ContainsOperator();
    expect($operator->getArity())->toBe(2);
});

test('ContainsOperator returns true when array contains value', function (): void {
    $operator = new ContainsOperator();
    expect($operator->execute([[1, 2, 3, 4, 5], 5]))->toBeTrue();
    expect($operator->execute([['apple', 'banana', 'orange'], 'apple']))->toBeTrue();
});

test('ContainsOperator returns false when array does not contain value', function (): void {
    $operator = new ContainsOperator();
    expect($operator->execute([[1, 2, 3, 4, 5], 10]))->toBeFalse();
    expect($operator->execute([['apple', 'banana', 'orange'], 'grape']))->toBeFalse();
});

test('ContainsOperator uses strict comparison', function (): void {
    $operator = new ContainsOperator();
    expect($operator->execute([[1, 2, 3, 4, 5], '5']))->toBeFalse();
});

test('ContainsOperator returns false for non-array haystack', function (): void {
    $operator = new ContainsOperator();
    expect($operator->execute(['not an array', 5]))->toBeFalse();
    expect($operator->execute([null, 5]))->toBeFalse();
});

test('ContainsOperator throws on invalid operand count', function (): void {
    $operator = new ContainsOperator();
    $operator->execute([[1, 2, 3]]);
})->throws(InvalidArgumentException::class);

// SubsetOperator Tests
test('SubsetOperator name', function (): void {
    $operator = new SubsetOperator();
    expect($operator->getName())->toBe('SUBSET');
});

test('SubsetOperator is binary', function (): void {
    $operator = new SubsetOperator();
    expect($operator->getArity())->toBe(2);
});

test('SubsetOperator returns true when first is subset of second', function (): void {
    $operator = new SubsetOperator();
    expect($operator->execute([[1, 2], [1, 2, 3, 4, 5]]))->toBeTrue();
    expect($operator->execute([[], [1, 2, 3]]))->toBeTrue();
    expect($operator->execute([[1, 2, 3], [1, 2, 3]]))->toBeTrue();
});

test('SubsetOperator returns false when first is not subset of second', function (): void {
    $operator = new SubsetOperator();
    expect($operator->execute([[1, 2, 6], [1, 2, 3, 4, 5]]))->toBeFalse();
    expect($operator->execute([['apple', 'grape'], ['apple', 'banana']]))->toBeFalse();
});

test('SubsetOperator returns false for non-arrays', function (): void {
    $operator = new SubsetOperator();
    expect($operator->execute(['not an array', [1, 2, 3]]))->toBeFalse();
    expect($operator->execute([[1, 2, 3], 'not an array']))->toBeFalse();
    expect($operator->execute(['not an array', 'also not an array']))->toBeFalse();
});

test('SubsetOperator throws on invalid operand count', function (): void {
    $operator = new SubsetOperator();
    $operator->execute([[1, 2]]);
})->throws(InvalidArgumentException::class);

// UnionOperator Tests
test('UnionOperator name', function (): void {
    $operator = new UnionOperator();
    expect($operator->getName())->toBe('UNION');
});

test('UnionOperator is variadic', function (): void {
    $operator = new UnionOperator();
    expect($operator->getArity())->toBe(-1);
});

test('UnionOperator combines arrays', function (): void {
    $operator = new UnionOperator();
    $result = $operator->execute([[1, 2, 3], [4, 5, 6]]);
    expect($result)->toBe([1, 2, 3, 4, 5, 6]);
});

test('UnionOperator removes duplicates', function (): void {
    $operator = new UnionOperator();
    $result = $operator->execute([[1, 2, 3], [2, 3, 4]]);
    expect($result)->toBe([1, 2, 3, 4]);
});

test('UnionOperator works with multiple arrays', function (): void {
    $operator = new UnionOperator();
    $result = $operator->execute([[1, 2], [3, 4], [5, 6]]);
    expect($result)->toBe([1, 2, 3, 4, 5, 6]);
});

test('UnionOperator handles empty arrays', function (): void {
    $operator = new UnionOperator();
    $result = $operator->execute([[], [1, 2], []]);
    expect($result)->toBe([1, 2]);
});

test('UnionOperator ignores non-arrays', function (): void {
    $operator = new UnionOperator();
    $result = $operator->execute([[1, 2], 'not an array', [3, 4]]);
    expect($result)->toBe([1, 2, 3, 4]);
});

test('UnionOperator throws on empty operands', function (): void {
    $operator = new UnionOperator();
    $operator->execute([]);
})->throws(InvalidArgumentException::class);

// IntersectOperator Tests
test('IntersectOperator name', function (): void {
    $operator = new IntersectOperator();
    expect($operator->getName())->toBe('INTERSECT');
});

test('IntersectOperator is variadic', function (): void {
    $operator = new IntersectOperator();
    expect($operator->getArity())->toBe(-1);
});

test('IntersectOperator returns common elements', function (): void {
    $operator = new IntersectOperator();
    $result = $operator->execute([[1, 2, 3, 4], [2, 3, 4, 5]]);
    expect($result)->toBe([2, 3, 4]);
});

test('IntersectOperator works with multiple arrays', function (): void {
    $operator = new IntersectOperator();
    $result = $operator->execute([[1, 2, 3, 4], [2, 3, 4, 5], [3, 4, 5, 6]]);
    expect($result)->toBe([3, 4]);
});

test('IntersectOperator returns empty array when no common elements', function (): void {
    $operator = new IntersectOperator();
    $result = $operator->execute([[1, 2], [3, 4]]);
    expect($result)->toBe([]);
});

test('IntersectOperator returns single array when only one provided', function (): void {
    $operator = new IntersectOperator();
    $result = $operator->execute([[1, 2, 3]]);
    expect($result)->toBe([1, 2, 3]);
});

test('IntersectOperator ignores non-arrays', function (): void {
    $operator = new IntersectOperator();
    $result = $operator->execute([[1, 2, 3], 'not an array', [2, 3, 4]]);
    expect($result)->toBe([2, 3]);
});

test('IntersectOperator returns empty array when all operands are non-arrays', function (): void {
    $operator = new IntersectOperator();
    $result = $operator->execute(['not an array', 123, null]);
    expect($result)->toBe([]);
});

test('IntersectOperator throws on empty operands', function (): void {
    $operator = new IntersectOperator();
    $operator->execute([]);
})->throws(InvalidArgumentException::class);

// DiffOperator Tests
test('DiffOperator name', function (): void {
    $operator = new DiffOperator();
    expect($operator->getName())->toBe('DIFF');
});

test('DiffOperator is binary', function (): void {
    $operator = new DiffOperator();
    expect($operator->getArity())->toBe(2);
});

test('DiffOperator returns elements in first but not second', function (): void {
    $operator = new DiffOperator();
    $result = $operator->execute([[1, 2, 3, 4, 5], [3, 4, 5, 6, 7]]);
    expect($result)->toBe([1, 2]);
});

test('DiffOperator returns empty array when first is subset of second', function (): void {
    $operator = new DiffOperator();
    $result = $operator->execute([[1, 2], [1, 2, 3, 4]]);
    expect($result)->toBe([]);
});

test('DiffOperator returns first array when no common elements', function (): void {
    $operator = new DiffOperator();
    $result = $operator->execute([[1, 2, 3], [4, 5, 6]]);
    expect($result)->toBe([1, 2, 3]);
});

test('DiffOperator returns empty array when first is not array', function (): void {
    $operator = new DiffOperator();
    $result = $operator->execute(['not an array', [1, 2, 3]]);
    expect($result)->toBe([]);
});

test('DiffOperator returns first array when second is not array', function (): void {
    $operator = new DiffOperator();
    $result = $operator->execute([[1, 2, 3], 'not an array']);
    expect($result)->toBe([1, 2, 3]);
});

test('DiffOperator throws on invalid operand count', function (): void {
    $operator = new DiffOperator();
    $operator->execute([[1, 2, 3]]);
})->throws(InvalidArgumentException::class);

// Edge Cases
test('set operators handle string arrays', function (): void {
    $in = new InOperator();
    $union = new UnionOperator();
    $intersect = new IntersectOperator();

    expect($in->execute(['apple', ['apple', 'banana', 'orange']]))->toBeTrue();

    $result = $union->execute([['apple', 'banana'], ['banana', 'orange']]);
    expect($result)->toBe(['apple', 'banana', 'orange']);

    $result = $intersect->execute([['apple', 'banana'], ['banana', 'orange']]);
    expect($result)->toBe(['banana']);
});

test('set operators reindex results', function (): void {
    $diff = new DiffOperator();
    $intersect = new IntersectOperator();

    // Results should be re-indexed starting from 0
    $result = $diff->execute([[1, 2, 3, 4, 5], [2, 4]]);
    expect($result)->toBe([1, 3, 5]);
    expect(array_keys($result))->toBe([0, 1, 2]);

    $result = $intersect->execute([[1, 2, 3, 4], [2, 3]]);
    expect($result)->toBe([2, 3]);
    expect(array_keys($result))->toBe([0, 1]);
});
