<?php

use App\Support\NotificationRecipients;

test('production environment resolves the full ngm team', function () {
    app()->instance('env', 'production');

    expect(NotificationRecipients::all())->toBe([
        'ngmcleaning2026@gmail.com',
        'jnguillaume4@gmail.com',
        'jldajeune@gmail.com',
    ]);
});

test('non production environments resolve a single inbox', function () {
    app()->instance('env', 'staging');

    expect(NotificationRecipients::all())->toBe(['ngmcleaning2026@gmail.com']);
});
