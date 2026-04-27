<?php

it('shows username when customer-like relation names are shown in filament tables', function (): void {
    $tableFiles = glob(base_path('app/Filament/Resources/*/Tables/*.php')) ?: [];
    $relations = ['customer', 'member', 'fromMember', 'sourceMember', 'sponsor', 'upline'];

    expect($tableFiles)->not->toBeEmpty();

    foreach ($tableFiles as $filePath) {
        $content = file_get_contents($filePath);

        if ($content === false) {
            continue;
        }

        foreach ($relations as $relation) {
            $hasNameColumn = str_contains($content, "TextColumn::make('{$relation}.name')");

            if (! $hasNameColumn) {
                continue;
            }

            $hasUsernameVisible = str_contains($content, "TextColumn::make('{$relation}.username')")
                || str_contains($content, "{$relation}?->username");

            expect($hasUsernameVisible, "{$filePath} menampilkan {$relation}.name tanpa username")
                ->toBeTrue();
        }
    }
});
