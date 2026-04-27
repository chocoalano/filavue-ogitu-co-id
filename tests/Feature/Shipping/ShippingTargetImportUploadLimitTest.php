<?php

use App\Filament\Resources\ShippingTargets\Pages\ManageShippingTargets;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;

it('uses a 15mb limit for temporary livewire uploads', function (): void {
    expect(config('livewire.temporary_file_upload.rules'))
        ->toBe(['required', 'file', 'max:15360']);
});

it('uses a 15mb limit for shipping target upload action', function (): void {
    $page = app(ManageShippingTargets::class);

    $method = new \ReflectionMethod($page, 'getHeaderActions');
    $method->setAccessible(true);

    $actions = $method->invoke($page);

    $importAction = collect($actions)->first(
        fn (mixed $action): bool => $action instanceof Action && $action->getName() === 'import_shipping_targets',
    );

    expect($importAction)->toBeInstanceOf(Action::class);

    /** @var Action $importAction */
    $schema = $importAction->getSchema(Schema::make());
    $components = $schema?->getComponents() ?? [];

    expect($components)->toHaveCount(1)
        ->and($components[0])->toBeInstanceOf(FileUpload::class);

    /** @var FileUpload $fileUpload */
    $fileUpload = $components[0];

    $validationRules = $fileUpload->getValidationRules();
    $fileValidationClosure = collect($validationRules)
        ->first(fn (mixed $rule): bool => $rule instanceof \Closure);
    expect($fileValidationClosure)->toBeInstanceOf(\Closure::class);

    $closureReflection = new \ReflectionFunction($fileValidationClosure);
    $staticVariables = $closureReflection->getStaticVariables();
    $fileRules = $staticVariables['fileRules'] ?? [];

    expect($fileUpload->getName())->toBe('file')
        ->and($fileRules)->toContain('max:15360');
});
