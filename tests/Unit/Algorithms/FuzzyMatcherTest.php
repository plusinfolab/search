<?php

use PlusInfoLab\Search\Algorithms\FuzzyMatcher;

it('finds fuzzy matches within threshold', function () {
    $algorithm = new FuzzyMatcher(['threshold' => 2]);

    $data = [
        ['title' => 'Laravel'],
        ['title' => 'Laravle'], // 1 edit distance
        ['title' => 'Laravell'], // 1 edit distance
        ['title' => 'Symfony'], // Too far
    ];

    $results = $algorithm->search('Laravel', $data, ['title']);

    expect($results)->toHaveCount(3);
});

it('scores closer matches higher', function () {
    $algorithm = new FuzzyMatcher(['threshold' => 2]);

    $data = [
        ['title' => 'Laravel'],
        ['title' => 'Laravle'],
    ];

    $results = $algorithm->search('Laravel', $data, ['title']);

    expect($results[0]['score'])->toBeGreaterThan($results[1]['score']);
});

it('handles word-level fuzzy matching', function () {
    $algorithm = new FuzzyMatcher(['threshold' => 2]);

    $data = [
        ['content' => 'The Laravel framework is great'],
    ];

    $results = $algorithm->search('Laravle', $data, ['content']);

    expect($results)->toHaveCount(1);
});
