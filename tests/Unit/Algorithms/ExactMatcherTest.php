<?php

use PlusInfoLab\Search\Algorithms\ExactMatcher;

it('finds exact matches', function () {
    $algorithm = new ExactMatcher(['case_sensitive' => false]);

    $data = [
        ['title' => 'Laravel Framework', 'content' => 'PHP framework'],
        ['title' => 'laravel framework', 'content' => 'Best PHP framework'],
        ['title' => 'Symfony', 'content' => 'Another framework'],
    ];

    $results = $algorithm->search('laravel framework', $data, ['title']);

    expect($results)->toHaveCount(2);
    expect($results[0]['score'])->toBe(100);
});

it('respects case sensitivity', function () {
    $algorithm = new ExactMatcher(['case_sensitive' => true]);

    $data = [
        ['title' => 'Laravel Framework'],
        ['title' => 'laravel framework'],
    ];

    $results = $algorithm->search('Laravel Framework', $data, ['title']);

    expect($results)->toHaveCount(1);
    expect($results[0]['index'])->toBe(0);
});
