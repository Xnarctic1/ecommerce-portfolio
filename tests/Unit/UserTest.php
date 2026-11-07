<?php

use App\Models\User;

test('user initials use the first and last name', function (string $name, string $initials) {
    $user = new User(['name' => $name]);

    expect($user->initials())->toBe($initials);
})->with([
    ['Alice', 'A'],
    ['Alice Smith', 'AS'],
    ['Alice Mary Smith', 'AS'],
]);
