<?php

test('bcadd produces exact decimal result', function () {
    $a = '1000.12345678';
    $b = '2500.87654322';
    $result = bcadd($a, $b, 8);
    expect($result)->toBe('3501.00000000');
});

test('bcsub produces exact decimal result', function () {
    $a = '5000.00000000';
    $b = '1234.56789012';
    $result = bcsub($a, $b, 8);
    expect($result)->toBe('3765.43210988');
});

test('bcmul produces exact decimal result', function () {
    $a = '100.00000000';
    $b = '0.50000000';
    $result = bcmul($a, $b, 8);
    expect($result)->toBe('50.00000000');
});

test('bcdiv produces exact decimal result', function () {
    $a = '1000.00000000';
    $b = '3.00000000';
    $result = bcdiv($a, $b, 8);
    expect($result)->toBe('333.33333333');
});

test('bccomp correctly compares amounts', function () {
    expect(bccomp('1000.00000001', '1000.00000000', 8))->toBe(1);
    expect(bccomp('999.99999999', '1000.00000000', 8))->toBe(-1);
    expect(bccomp('1000.00000000', '1000.00000000', 8))->toBe(0);
});

test('no floating point errors in money operations', function () {
    $result = bcadd('0.10000000', '0.20000000', 8);
    expect($result)->toBe('0.30000000');

    $result2 = bcmul('19.99', '100', 8);
    expect($result2)->toBe('1999.00');
});
