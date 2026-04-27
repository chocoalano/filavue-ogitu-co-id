<?php

namespace App\Filament\Pages;

use App\Models\CustomerPackage;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class PaketMemberSettings extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static ?string $title = 'Pengaturan Paket Member';
    protected ?string $subheading = 'Pengaturan toko untuk mengelola informasi paket member.';
    protected static ?string $navigationLabel = 'Pengaturan Paket Member';
    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';

    protected string $view = 'filament.pages.paket-member-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->getStoredState());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Paket Member')
                    ->id('paket-member-settings-tabs')
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('Daftar Paket')
                            ->icon('heroicon-m-rectangle-stack')
                            ->schema([
                                Section::make('Master Paket Member')
                                    ->description('Kelola paket member berdasarkan omset/benefit. Perubahan akan mempengaruhi komponen bonus yang berlaku.')
                                    ->schema([
                                        Repeater::make('packages')
                                            ->label('Paket')
                                            ->defaultItems(0)
                                            ->addActionLabel('Tambah Paket')
                                            ->reorderable()
                                            ->collapsible()
                                            ->itemLabel(function (array $state): string {
                                                $name = trim((string) ($state['name'] ?? ''));
                                                $alias = trim((string) ($state['alias'] ?? ''));
                                                return $name !== '' ? $name : ($alias !== '' ? $alias : 'Paket');
                                            })
                                            ->schema([
                                                Hidden::make('id'),

                                                TextInput::make('name')
                                                    ->label('Nama Paket')
                                                    ->helperText('Nama paket yang tampil di sistem.')
                                                    ->required()
                                                    ->maxLength(120)
                                                    ->columnSpan(6),

                                                TextInput::make('alias')
                                                    ->label('Alias')
                                                    ->helperText('Singkatan paket (opsional). Contoh: SILVER, GOLD.')
                                                    ->maxLength(50)
                                                    ->columnSpan(3),

                                                TextInput::make('price')
                                                    ->label('Harga')
                                                    ->helperText('Harga paket (decimal). Kosong = 0.')
                                                    ->numeric()
                                                    ->default(0)
                                                    ->columnSpan(3),

                                                TextInput::make('pv')
                                                    ->label('PV')
                                                    ->helperText('Point Value (integer). Kosong = 0.')
                                                    ->numeric()
                                                    ->default(0)
                                                    ->columnSpan(3),

                                                TextInput::make('pr')
                                                    ->label('PR')
                                                    ->helperText('Point Referral (integer). Kosong = 0.')
                                                    ->numeric()
                                                    ->default(0)
                                                    ->columnSpan(3),

                                                TextInput::make('sponsor')
                                                    ->label('Bonus Sponsor')
                                                    ->helperText('Komponen bonus sponsor (decimal). Kosong = 0.')
                                                    ->numeric()
                                                    ->default(0)
                                                    ->columnSpan(3),

                                                TextInput::make('pairing')
                                                    ->label('Bonus Pairing')
                                                    ->helperText('Komponen bonus pairing (decimal). Kosong = 0.')
                                                    ->numeric()
                                                    ->default(0)
                                                    ->columnSpan(3),

                                                TextInput::make('matching')
                                                    ->label('Bonus Matching')
                                                    ->helperText('Komponen bonus matching (decimal). Kosong = 0.')
                                                    ->numeric()
                                                    ->default(0)
                                                    ->columnSpan(3),

                                                TextInput::make('flush_out')
                                                    ->label('Flush Out')
                                                    ->helperText('Komponen flush out (decimal). Kosong = 0.')
                                                    ->numeric()
                                                    ->default(0)
                                                    ->columnSpan(3),
                                            ])
                                            ->columns(12),
                                    ]),
                            ]),

                        Tab::make('Info')
                            ->icon('heroicon-m-information-circle')
                            ->schema([
                                Section::make('Catatan')
                                    ->schema([
                                        \Filament\Forms\Components\Placeholder::make('note')
                                            ->content('Paket yang masih dipakai oleh customer tidak boleh dihapus (akan muncul notifikasi jika ada yang terblokir).')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan')
                ->icon('heroicon-m-check')
                ->color('primary')
                ->action('save'),

            Action::make('reset')
                ->label('Reset')
                ->icon('heroicon-m-arrow-path')
                ->color('gray')
                ->requiresConfirmation()
                ->action('resetForm'),
        ];
    }

    public function save(): void
    {
        $state = $this->form->getState();
        $items = (array) ($state['packages'] ?? []);

        $blockedDeletes = [];

        DB::transaction(function () use ($items, &$blockedDeletes) {
            $existingIds = CustomerPackage::withoutGlobalScopes()
                ->pluck('id')
                ->map(fn ($v) => (int) $v)
                ->all();

            $keptIds = [];

            foreach ($items as $row) {
                $id = Arr::get($row, 'id');
                $id = is_numeric($id) ? (int) $id : null;

                $payload = [
                    'name' => trim((string) ($row['name'] ?? '')),
                    'alias' => $this->nullIfBlank($row['alias'] ?? null),
                    'price' => $this->toFloatOrZero($row['price'] ?? null),
                    'pv' => $this->toIntOrZero($row['pv'] ?? null),
                    'pr' => $this->toIntOrZero($row['pr'] ?? null),
                    'sponsor' => $this->toFloatOrZero($row['sponsor'] ?? null),
                    'pairing' => $this->toFloatOrZero($row['pairing'] ?? null),
                    'matching' => $this->toFloatOrZero($row['matching'] ?? null),
                    'flush_out' => $this->toFloatOrZero($row['flush_out'] ?? null),
                ];

                // Guard: name wajib (kalau kosong, biar Filament yang validasi)
                if ($id) {
                    CustomerPackage::withoutGlobalScopes()
                        ->whereKey($id)
                        ->update($payload);

                    $keptIds[] = $id;
                } else {
                    $created = CustomerPackage::withoutGlobalScopes()->create($payload);
                    $keptIds[] = (int) $created->id;
                }
            }

            // Handle delete: yang hilang dari repeater
            $removedIds = array_values(array_diff($existingIds, $keptIds));

            if (! empty($removedIds)) {
                $packages = CustomerPackage::withoutGlobalScopes()
                    ->withCount('customers')
                    ->whereIn('id', $removedIds)
                    ->get();

                $deletable = $packages->where('customers_count', 0)->pluck('id')->all();
                $blocked = $packages->where('customers_count', '>', 0);

                if (! empty($deletable)) {
                    CustomerPackage::withoutGlobalScopes()
                        ->whereIn('id', $deletable)
                        ->delete();
                }

                if ($blocked->isNotEmpty()) {
                    $blockedDeletes = $blocked
                        ->map(fn ($p) => ($p->name ?? 'Paket') . " (dipakai {$p->customers_count} customer)")
                        ->values()
                        ->all();
                }
            }
        });

        Notification::make()
            ->success()
            ->title('Tersimpan')
            ->body('Pengaturan paket member berhasil diperbarui.')
            ->send();

        if (! empty($blockedDeletes)) {
            Notification::make()
                ->warning()
                ->title('Sebagian paket tidak bisa dihapus')
                ->body('Karena masih dipakai customer: ' . implode(', ', array_slice($blockedDeletes, 0, 6)) . (count($blockedDeletes) > 6 ? '…' : ''))
                ->send();
        }

        $this->form->fill($this->getStoredState());
    }

    public function resetForm(): void
    {
        $this->form->fill($this->getStoredState());

        Notification::make()
            ->info()
            ->title('Di-reset')
            ->body('Form dikembalikan ke data yang tersimpan.')
            ->send();
    }

    protected function getStoredState(): array
    {
        $rows = CustomerPackage::withoutGlobalScopes()
            ->orderBy('id')
            ->get();

        return [
            'packages' => $rows->map(function (CustomerPackage $p) {
                return [
                    'id' => (int) $p->id,
                    'name' => (string) ($p->name ?? ''),
                    'alias' => $p->alias,
                    'price' => $this->toFloatOrZero($p->price),
                    'pv' => $this->toIntOrZero($p->pv),
                    'pr' => $this->toIntOrZero($p->pr),
                    'sponsor' => $this->toFloatOrZero($p->sponsor),
                    'pairing' => $this->toFloatOrZero($p->pairing),
                    'matching' => $this->toFloatOrZero($p->matching),
                    'flush_out' => $this->toFloatOrZero($p->flush_out),
                ];
            })->all(),
        ];
    }

    // -------------------------
    // Helpers (null safe)
    // -------------------------
    protected function nullIfBlank(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    protected function toFloatOrZero(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }
        return (float) $value;
    }

    protected function toIntOrZero(mixed $value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }
        return (int) $value;
    }
}
