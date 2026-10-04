<?php

test('directories the deploy caches rely on are present in the repository', function (string $path) {
    expect(is_dir(base_path($path)))->toBeTrue();
})->with([
    'resources/views',
    'storage/framework/views',
    'storage/framework/cache/data',
    'bootstrap/cache',
]);
