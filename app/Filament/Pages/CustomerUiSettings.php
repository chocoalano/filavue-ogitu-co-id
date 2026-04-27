<?php

namespace App\Filament\Pages;

use App\Support\CustomerUiSettingsConfig;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use UnitEnum;

class CustomerUiSettings extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static ?string $title = 'Pengaturan UI Customer';

    protected ?string $subheading = 'Atur kolom tabel, filter, dan bagian form customer.';

    protected static ?string $navigationLabel = 'UI Customer';

    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';

    protected string $view = 'filament.pages.customer-ui-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->getStoredState());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('customer-ui-settings-tabs')
                    ->id('customer-ui-settings-tabs')
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('Kolom Tabel')
                            ->icon('heroicon-m-table-cells')
                            ->schema([
                                Section::make('Visibilitas Kolom')
                                    ->description('Pilih kolom yang aktif ditampilkan di tabel customer.')
                                    ->schema([
                                        CheckboxList::make('columns_enabled')
                                            ->label('Kolom Aktif')
                                            ->options(CustomerUiSettingsConfig::tableColumnOptions())
                                            ->columns(2),
                                    ]),
                                Section::make('Kolom Hidden By Default')
                                    ->description('Kolom aktif yang dicentang di sini akan disembunyikan saat tabel pertama kali dibuka.')
                                    ->schema([
                                        CheckboxList::make('columns_hidden_by_default')
                                            ->label('Hidden By Default')
                                            ->options(CustomerUiSettingsConfig::tableColumnOptions())
                                            ->columns(2),
                                    ]),
                            ]),
                        Tab::make('Filter & Form')
                            ->icon('heroicon-m-funnel')
                            ->schema([
                                Section::make('Filter Tabel')
                                    ->description('Pilih filter yang aktif di halaman list customer.')
                                    ->schema([
                                        CheckboxList::make('filters_enabled')
                                            ->label('Filter Aktif')
                                            ->options(CustomerUiSettingsConfig::tableFilterOptions())
                                            ->columns(2),
                                    ]),
                                Section::make('Bagian Form')
                                    ->description('Pilih section form customer yang tetap dimuat pada halaman create/edit.')
                                    ->schema([
                                        CheckboxList::make('form_sections_enabled')
                                            ->label('Section Form Aktif')
                                            ->options(CustomerUiSettingsConfig::formSectionOptions())
                                            ->columns(2),
                                    ]),
                            ]),
                        Tab::make('Status')
                            ->icon('heroicon-m-tag')
                            ->schema([
                                Section::make('Label & Warna Status')
                                    ->description('Gunakan label dan warna ini untuk badge status di tabel serta opsi filter status.')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('status_labels.1')
                                            ->label('Label Status 1')
                                            ->required(),
                                        Select::make('status_colors.1')
                                            ->label('Warna Status 1')
                                            ->options(CustomerUiSettingsConfig::statusColorOptions())
                                            ->required()
                                            ->native(false),
                                        TextInput::make('status_labels.2')
                                            ->label('Label Status 2')
                                            ->required(),
                                        Select::make('status_colors.2')
                                            ->label('Warna Status 2')
                                            ->options(CustomerUiSettingsConfig::statusColorOptions())
                                            ->required()
                                            ->native(false),
                                        TextInput::make('status_labels.3')
                                            ->label('Label Status 3')
                                            ->required(),
                                        Select::make('status_colors.3')
                                            ->label('Warna Status 3')
                                            ->options(CustomerUiSettingsConfig::statusColorOptions())
                                            ->required()
                                            ->native(false),
                                    ]),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();
        CustomerUiSettingsConfig::writeState($this->normalizeStateForStorage($state));

        Notification::make()
            ->success()
            ->title('Tersimpan')
            ->body('Pengaturan UI customer berhasil diperbarui.')
            ->send();

        $this->form->fill($this->getStoredState());
    }

    public function resetForm(): void
    {
        $this->form->fill($this->getStoredState());

        Notification::make()
            ->info()
            ->title('Di-reset')
            ->body('Form dikembalikan ke data terakhir yang tersimpan.')
            ->send();
    }

    /**
     * @return array<string, mixed>
     */
    protected function getStoredState(): array
    {
        $state = CustomerUiSettingsConfig::getState();

        return [
            'columns_enabled' => collect($state['table']['columns'])
                ->filter(static fn (array $column): bool => (bool) ($column['enabled'] ?? false))
                ->keys()
                ->values()
                ->all(),
            'columns_hidden_by_default' => collect($state['table']['columns'])
                ->filter(static fn (array $column): bool => (bool) ($column['hidden_by_default'] ?? false))
                ->keys()
                ->values()
                ->all(),
            'filters_enabled' => collect($state['table']['filters'])
                ->filter(static fn (bool $enabled): bool => $enabled)
                ->keys()
                ->values()
                ->all(),
            'form_sections_enabled' => collect($state['form']['sections'])
                ->filter(static fn (bool $enabled): bool => $enabled)
                ->keys()
                ->values()
                ->all(),
            'status_labels' => $state['status']['labels'],
            'status_colors' => $state['status']['colors'],
        ];
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    protected function normalizeStateForStorage(array $state): array
    {
        $defaults = CustomerUiSettingsConfig::defaultState();
        $columnEnabled = $this->selectedKeys($state['columns_enabled'] ?? []);
        $columnsHidden = $this->selectedKeys($state['columns_hidden_by_default'] ?? []);
        $filtersEnabled = $this->selectedKeys($state['filters_enabled'] ?? []);
        $formSectionsEnabled = $this->selectedKeys($state['form_sections_enabled'] ?? []);

        $columns = [];
        foreach ($defaults['table']['columns'] as $columnKey => $defaultConfig) {
            $columns[$columnKey] = [
                'enabled' => in_array($columnKey, $columnEnabled, true),
                'hidden_by_default' => in_array($columnKey, $columnsHidden, true),
            ];
        }

        $filters = [];
        foreach ($defaults['table']['filters'] as $filterKey => $defaultEnabled) {
            $filters[$filterKey] = in_array($filterKey, $filtersEnabled, true);
        }

        $formSections = [];
        foreach ($defaults['form']['sections'] as $sectionKey => $defaultEnabled) {
            $formSections[$sectionKey] = in_array($sectionKey, $formSectionsEnabled, true);
        }

        $statusLabelsInput = is_array($state['status_labels'] ?? null) ? $state['status_labels'] : [];
        $statusColorsInput = is_array($state['status_colors'] ?? null) ? $state['status_colors'] : [];
        $statusLabels = [];
        $statusColors = [];
        $allowedColors = array_keys(CustomerUiSettingsConfig::statusColorOptions());

        foreach ($defaults['status']['labels'] as $status => $defaultLabel) {
            $rawLabel = $statusLabelsInput[$status] ?? null;
            $label = is_string($rawLabel) ? trim($rawLabel) : '';
            $statusLabels[$status] = $label !== '' ? $label : $defaultLabel;

            $rawColor = $statusColorsInput[$status] ?? null;
            $color = is_string($rawColor) ? trim($rawColor) : '';
            $statusColors[$status] = in_array($color, $allowedColors, true)
                ? $color
                : $defaults['status']['colors'][$status];
        }

        return [
            'table' => [
                'columns' => $columns,
                'filters' => $filters,
            ],
            'form' => [
                'sections' => $formSections,
            ],
            'status' => [
                'labels' => $statusLabels,
                'colors' => $statusColors,
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function selectedKeys(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return collect($value)
            ->filter(static fn (mixed $item): bool => is_string($item) || is_int($item))
            ->map(static fn (int|string $item): string => (string) $item)
            ->values()
            ->all();
    }
}
