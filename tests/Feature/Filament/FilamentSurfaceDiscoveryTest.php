<?php

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\Exports\Enums\Contracts\ExportFormat as ExportFormatContract;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Pages\Page as FilamentPage;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Component as SchemaComponent;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema as DatabaseSchema;
use Livewire\Component as LivewireComponent;

beforeEach(function (): void {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    config()->set('cache.default', 'array');
    config()->set('session.driver', 'array');

    DB::purge('sqlite');
    DB::reconnect('sqlite');

    DatabaseSchema::dropIfExists('bug_reports');
    DatabaseSchema::dropIfExists('customers');
    DatabaseSchema::dropIfExists('settings');
    DatabaseSchema::dropIfExists('users');

    DatabaseSchema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('name')->nullable();
        $table->string('email')->nullable();
        $table->string('password')->nullable();
        $table->string('role')->nullable();
        $table->timestamps();
    });

    DatabaseSchema::create('customers', function (Blueprint $table): void {
        $table->id();
        $table->string('name')->nullable();
        $table->string('username')->nullable();
        $table->string('email')->nullable();
        $table->string('phone')->nullable();
        $table->timestamps();
    });

    DatabaseSchema::create('bug_reports', function (Blueprint $table): void {
        $table->id();
        $table->string('title')->nullable();
        $table->timestamps();
    });

    DatabaseSchema::create('settings', function (Blueprint $table): void {
        $table->id();
        $table->string('key')->unique();
        $table->text('value')->nullable();
        $table->string('type')->default('text');
        $table->string('group')->default('general');
        $table->timestamps();
    });
});

dataset('filament-resource-classes', fn (): array => datasetValues(filamentResourceClasses()));
dataset('filament-standalone-page-classes', fn (): array => datasetValues(filamentStandalonePageClasses()));
dataset('filament-resource-page-classes', fn (): array => datasetValues(filamentResourcePageClasses()));
dataset('filament-widget-classes', fn (): array => datasetValues(filamentWidgetClasses()));
dataset('filament-exporter-classes', fn (): array => datasetValues(filamentExporterClasses()));
dataset('filament-importer-classes', fn (): array => datasetValues(filamentImporterClasses()));

function filamentResourceClasses(): array
{
    return concreteClasses(
        classNamesFromFiles(
            allPhpFiles(projectAppPath('Filament/Resources')),
            static fn (string $path): bool => str_ends_with($path, 'Resource.php'),
        ),
        Resource::class,
    );
}

function filamentStandalonePageClasses(): array
{
    return concreteClasses(
        classNamesFromFiles(allPhpFiles(projectAppPath('Filament/Pages'))),
        FilamentPage::class,
    );
}

function filamentResourcePageClasses(): array
{
    $files = collect(allPhpFiles(projectAppPath('Filament/Resources')))
        ->filter(fn ($file): bool => str_contains($file->getPathname(), DIRECTORY_SEPARATOR.'Pages'.DIRECTORY_SEPARATOR))
        ->all();

    return concreteClasses(
        classNamesFromFiles($files),
        FilamentPage::class,
    );
}

function filamentWidgetClasses(): array
{
    $files = collect(allPhpFiles(projectAppPath('Filament')))
        ->filter(fn ($file): bool => str_contains($file->getPathname(), DIRECTORY_SEPARATOR.'Widgets'.DIRECTORY_SEPARATOR))
        ->all();

    return concreteClasses(
        classNamesFromFiles($files),
        LivewireComponent::class,
    );
}

function filamentExporterClasses(): array
{
    return concreteClasses(
        classNamesFromFiles(
            allPhpFiles(projectAppPath('Filament')),
            static fn (string $path): bool => str_ends_with($path, 'Exporter.php'),
        ),
        Exporter::class,
    );
}

function filamentImporterClasses(): array
{
    return concreteClasses(
        classNamesFromFiles(
            allPhpFiles(projectAppPath('Filament')),
            static fn (string $path): bool => str_ends_with($path, 'Importer.php'),
        ),
        Importer::class,
    );
}

function classNamesFromFiles(array $files, ?callable $filter = null): array
{
    return collect($files)
        ->map(fn ($file): string => $file->getPathname())
        ->filter(fn (string $path): bool => $filter ? $filter($path) : true)
        ->map(fn (string $path): string => classNameFromPath($path))
        ->filter(fn (string $class): bool => class_exists($class))
        ->unique()
        ->sort()
        ->values()
        ->all();
}

function allPhpFiles(string $path): array
{
    if (! is_dir($path)) {
        return [];
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
    );

    return collect(iterator_to_array($iterator))
        ->filter(fn ($file): bool => $file instanceof SplFileInfo)
        ->filter(fn (SplFileInfo $file): bool => $file->isFile() && ($file->getExtension() === 'php'))
        ->values()
        ->all();
}

function classNameFromPath(string $path): string
{
    $relativePath = str($path)
        ->after(projectAppPath().DIRECTORY_SEPARATOR)
        ->beforeLast('.php')
        ->replace(DIRECTORY_SEPARATOR, '\\')
        ->toString();

    return 'App\\'.$relativePath;
}

function projectRoot(): string
{
    return dirname(__DIR__, 3);
}

function projectAppPath(string $path = ''): string
{
    $appPath = projectRoot().DIRECTORY_SEPARATOR.'app';

    if ($path === '') {
        return $appPath;
    }

    return $appPath.DIRECTORY_SEPARATOR.ltrim($path, DIRECTORY_SEPARATOR);
}

function concreteClasses(array $classes, string $baseClass): array
{
    return collect($classes)
        ->filter(fn (string $class): bool => is_subclass_of($class, $baseClass))
        ->filter(function (string $class): bool {
            return ! (new ReflectionClass($class))->isAbstract();
        })
        ->values()
        ->all();
}

function datasetValues(array $classes): array
{
    return collect($classes)
        ->mapWithKeys(fn (string $class): array => [$class => [$class]])
        ->all();
}

function reflectionTypeName(?ReflectionType $reflectionType): ?string
{
    if (! $reflectionType instanceof ReflectionNamedType) {
        return null;
    }

    return $reflectionType->getName();
}

function assertMethodContract(string $class, string $method, string $parameterType, string $returnType): void
{
    expect(method_exists($class, $method))->toBeTrue();

    $reflection = new ReflectionMethod($class, $method);
    $parameter = $reflection->getParameters()[0] ?? null;

    expect($parameter)->not->toBeNull();

    $parameterReflectionType = $parameter->getType();
    $returnReflectionType = $reflection->getReturnType();

    expect($parameterReflectionType)->toBeInstanceOf(ReflectionNamedType::class)
        ->and(reflectionTypeName($parameterReflectionType))->toBe($parameterType)
        ->and($returnReflectionType)->toBeInstanceOf(ReflectionNamedType::class)
        ->and(reflectionTypeName($returnReflectionType))->toBe($returnType);
}

function assertReturnType(string $class, string $method, string $returnType): void
{
    expect(method_exists($class, $method))->toBeTrue();

    $reflection = new ReflectionMethod($class, $method);
    $returnReflectionType = $reflection->getReturnType();

    expect($returnReflectionType)->toBeInstanceOf(ReflectionNamedType::class)
        ->and(reflectionTypeName($returnReflectionType))->toBe($returnType);
}

function assertDeclaredViewExists(string $class): void
{
    $view = getDeclaredPropertyDefaultValue($class, 'view');

    if (! is_string($view) || ($view === '')) {
        return;
    }

    expect(view()->exists($view))->toBeTrue("Expected declared view [{$view}] for [{$class}] to exist.");
}

function assertRenderViewExistsIfLiteral(string $class): void
{
    if (! method_exists($class, 'render')) {
        return;
    }

    $reflection = new ReflectionMethod($class, 'render');
    $fileName = $reflection->getFileName();

    if ($fileName === false) {
        return;
    }

    $source = implode('', array_slice(
        file($fileName),
        $reflection->getStartLine() - 1,
        $reflection->getEndLine() - $reflection->getStartLine() + 1,
    ));

    preg_match("/view\\(['\\\"]([^'\\\"]+)['\\\"]/", $source, $matches);

    $view = $matches[1] ?? null;

    if (! is_string($view) || ($view === '')) {
        return;
    }

    expect(view()->exists($view))->toBeTrue("Expected render view [{$view}] for [{$class}] to exist.");
}

function getDeclaredPropertyDefaultValue(string $class, string $property): mixed
{
    $reflection = new ReflectionClass($class);

    if (! $reflection->hasProperty($property)) {
        return null;
    }

    $reflectionProperty = $reflection->getProperty($property);

    if ($reflectionProperty->getDeclaringClass()->getName() !== $class) {
        return null;
    }

    return $reflection->getDefaultProperties()[$property] ?? null;
}

function makeSchemaHost(): HasSchemas
{
    return new class extends LivewireComponent implements HasSchemas
    {
        use InteractsWithSchemas;

        public ?array $data = [];
    };
}

function makeTableHost(): HasSchemas&HasTable
{
    return new class extends LivewireComponent implements HasSchemas, HasTable
    {
        use InteractsWithSchemas;
        use InteractsWithTable;

        public ?array $data = [];
    };
}

it('loads filament discovery datasets', function (): void {
    expect(filamentResourceClasses())->not->toBeEmpty()
        ->and(filamentStandalonePageClasses())->not->toBeEmpty()
        ->and(filamentResourcePageClasses())->not->toBeEmpty()
        ->and(filamentWidgetClasses())->not->toBeEmpty()
        ->and(filamentExporterClasses())->not->toBeEmpty()
        ->and(filamentImporterClasses())->not->toBeEmpty();
});

it('keeps every filament resource registration valid', function (string $resourceClass): void {
    expect(is_subclass_of($resourceClass, Resource::class))->toBeTrue();

    $modelClass = $resourceClass::getModel();

    expect(class_exists($modelClass))->toBeTrue()
        ->and(is_subclass_of($modelClass, Model::class))->toBeTrue();

    assertMethodContract($resourceClass, 'form', Schema::class, Schema::class);
    assertMethodContract($resourceClass, 'infolist', Schema::class, Schema::class);
    assertMethodContract($resourceClass, 'table', Table::class, Table::class);

    expect($resourceClass::form(Schema::make(makeSchemaHost())))->toBeInstanceOf(Schema::class);
    expect($resourceClass::infolist(Schema::make(makeSchemaHost())))->toBeInstanceOf(Schema::class);
    expect($resourceClass::table(Table::make(makeTableHost())))->toBeInstanceOf(Table::class);

    foreach ($resourceClass::getRelations() as $relationManagerClass) {
        expect(class_exists($relationManagerClass))->toBeTrue()
            ->and(is_subclass_of($relationManagerClass, RelationManager::class))->toBeTrue();
    }

    $pages = $resourceClass::getPages();

    expect($pages)->toBeArray()->not->toBeEmpty();

    foreach ($pages as $pageName => $pageRegistration) {
        expect($pageName)->toBeString()
            ->and($pageRegistration)->toBeInstanceOf(PageRegistration::class);

        $pageClass = $pageRegistration->getPage();

        expect(class_exists($pageClass))->toBeTrue()
            ->and(is_subclass_of($pageClass, FilamentPage::class))->toBeTrue();

        $mappedResource = getDeclaredPropertyDefaultValue($pageClass, 'resource');

        if (is_string($mappedResource)) {
            expect($mappedResource)->toBe($resourceClass);
        }

        assertDeclaredViewExists($pageClass);
    }
})->with('filament-resource-classes');

it('keeps every standalone filament page valid', function (string $pageClass): void {
    expect(is_subclass_of($pageClass, FilamentPage::class))->toBeTrue();

    assertDeclaredViewExists($pageClass);

    $page = app($pageClass);

    expect($page)->toBeInstanceOf($pageClass);

    if (is_subclass_of($pageClass, HasSchemas::class) && method_exists($pageClass, 'form')) {
        assertMethodContract($pageClass, 'form', Schema::class, Schema::class);
        expect($page->form(Schema::make($page)))->toBeInstanceOf(Schema::class);
    }

    if (is_subclass_of($pageClass, HasTable::class) && method_exists($pageClass, 'table')) {
        assertMethodContract($pageClass, 'table', Table::class, Table::class);
        expect($page->table(Table::make($page)))->toBeInstanceOf(Table::class);
    }

    if (method_exists($pageClass, 'content')) {
        assertMethodContract($pageClass, 'content', Schema::class, Schema::class);
        expect($page->content(Schema::make($page instanceof HasSchemas ? $page : null)))->toBeInstanceOf(Schema::class);
    }
})->with('filament-standalone-page-classes');

it('keeps every resource-scoped filament page valid', function (string $pageClass): void {
    expect(is_subclass_of($pageClass, FilamentPage::class))->toBeTrue();

    $resourceClass = getDeclaredPropertyDefaultValue($pageClass, 'resource');

    if (is_string($resourceClass)) {
        expect(class_exists($resourceClass))->toBeTrue()
            ->and(is_subclass_of($resourceClass, Resource::class))->toBeTrue();
    }

    assertDeclaredViewExists($pageClass);

    if (method_exists($pageClass, 'content')) {
        assertMethodContract($pageClass, 'content', Schema::class, Schema::class);
    }

    foreach (['getHeaderWidgets', 'getFooterWidgets'] as $method) {
        if (! method_exists($pageClass, $method)) {
            continue;
        }

        assertReturnType($pageClass, $method, 'array');
    }
})->with('filament-resource-page-classes');

it('keeps every filament widget valid', function (string $widgetClass): void {
    expect(
        is_subclass_of($widgetClass, Widget::class)
        || is_subclass_of($widgetClass, LivewireComponent::class)
    )->toBeTrue();

    assertDeclaredViewExists($widgetClass);
    assertRenderViewExistsIfLiteral($widgetClass);

    if (is_subclass_of($widgetClass, TableWidget::class)) {
        assertMethodContract($widgetClass, 'table', Table::class, Table::class);
    }
})->with('filament-widget-classes');

it('keeps every filament exporter valid', function (string $exporterClass): void {
    expect(is_subclass_of($exporterClass, Exporter::class))->toBeTrue();

    $modelClass = $exporterClass::getModel();
    $columns = $exporterClass::getColumns();

    expect(class_exists($modelClass))->toBeTrue()
        ->and(is_subclass_of($modelClass, Model::class))->toBeTrue()
        ->and($columns)->toBeArray()->not->toBeEmpty();

    foreach ($columns as $column) {
        expect($column)->toBeInstanceOf(ExportColumn::class);
    }

    foreach ($exporterClass::getOptionsFormComponents() as $component) {
        expect(
            $component instanceof SchemaComponent
            || $component instanceof Action
            || $component instanceof ActionGroup
        )->toBeTrue();
    }

    $columnMap = collect($columns)
        ->mapWithKeys(fn (ExportColumn $column): array => [$column->getName() => $column->getName()])
        ->all();

    $exporter = new $exporterClass(new Export, $columnMap, []);

    expect($exporter->getCachedColumns())->toHaveCount(count($columns))
        ->and($exporter->getFormats())->not->toBeEmpty();

    foreach ($exporter->getFormats() as $format) {
        expect($format)->toBeInstanceOf(ExportFormatContract::class);
    }
})->with('filament-exporter-classes');

it('keeps every filament importer valid', function (string $importerClass): void {
    expect(is_subclass_of($importerClass, Importer::class))->toBeTrue();

    $modelClass = $importerClass::getModel();
    $columns = $importerClass::getColumns();

    expect(class_exists($modelClass))->toBeTrue()
        ->and(is_subclass_of($modelClass, Model::class))->toBeTrue()
        ->and($columns)->toBeArray()->not->toBeEmpty();

    foreach ($columns as $column) {
        expect($column)->toBeInstanceOf(ImportColumn::class);
    }

    $columnMap = collect($columns)
        ->mapWithKeys(fn (ImportColumn $column): array => [$column->getName() => $column->getName()])
        ->all();

    $importer = new $importerClass(new Import, $columnMap, []);

    expect($importer->getCachedColumns())->toHaveCount(count($columns))
        ->and($importer->getValidationRules())->toBeArray();
})->with('filament-importer-classes');
