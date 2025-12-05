<?php

use PlusInfoLab\Search\Tests\Fixtures\Post;

beforeEach(function () {
    // Create test posts
    Post::create(['title' => 'Laravel Framework Guide', 'content' => 'Learn Laravel']);
    Post::create(['title' => 'PHP Best Practices', 'content' => 'Laravel tips']);
    Post::create(['title' => 'Symfony Tutorial', 'content' => 'Symfony framework']);
});

it('can search using scope', function () {
    $results = Post::search('Laravel')->get();

    expect($results)->toHaveCount(2);
});

it('can use different algorithms', function () {
    $results = Post::search('Laravle', 'fuzzy')->get();

    expect($results)->not->toBeEmpty();
});

it('returns empty results for no matches', function () {
    $results = Post::search('NonExistent')->get();

    expect($results)->toBeEmpty();
});

it('can search with searchQuery method', function () {
    $results = Post::searchQuery('Laravel');

    expect($results)->toBeInstanceOf(\PlusInfoLab\Search\Results\SearchResultCollection::class);
    expect($results->count())->toBeGreaterThan(0);
});
