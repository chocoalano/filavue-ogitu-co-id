<?php

it('uses database queue default as single default queue name', function (): void {
    $configuredQueue = (string) config('queue.connections.database.queue');

    expect($configuredQueue)->toBe('default');
});
