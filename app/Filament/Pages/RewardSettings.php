<?php

namespace App\Filament\Pages;

use App\Models\Reward;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class RewardSettings extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static ?string $title = 'Pengaturan Periodik Reward';
    protected ?string $subheading = 'Pengaturan toko untuk mengelola informasi periodik reward.';
    protected static ?string $navigationLabel = 'Pengaturan Periodik Reward';
    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';

    protected string $view = 'filament.pages.reward-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->getStoredState());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Reward Settings')
                    ->id('reward-settings-tabs')
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('Daftar Reward')
                            ->icon('heroicon-m-gift')
                            ->schema([
                                Section::make('Master Reward')
                                    ->description('Kelola daftar reward yang bisa diraih member (periode / permanen).')
                                    ->schema([
                                        Repeater::make('rewards')
                                            ->label('Rewards')
                                            ->defaultItems(0)
                                            ->addActionLabel('Tambah Reward')
                                            ->reorderable()
                                            ->collapsible()
                                            ->itemLabel(function (array $state): string {
                                                $name = trim((string) ($state['name'] ?? ''));
                                                $code = trim((string) ($state['code'] ?? ''));
                                                return $name !== '' ? $name : ($code !== '' ? $code : 'Reward');
                                            })
                                            ->schema([
                                                Hidden::make('id'),

                                                TextInput::make('code')
                                                    ->label('Kode')
                                                    ->helperText('Kode unik reward (opsional). Contoh: RW-001')
                                                    ->maxLength(50)
                                                    ->columnSpan(3),

                                                TextInput::make('name')
                                                    ->label('Nama Reward')
                                                    ->helperText('Nama yang tampil di aplikasi.')
                                                    ->required()
                                                    ->maxLength(150)
                                                    ->columnSpan(3),

                                                Toggle::make('status')
                                                    ->label('Aktif')
                                                    ->helperText('Aktifkan/nonaktifkan reward.')
                                                    ->default(true)
                                                    ->columnSpan(3),

                                                Select::make('type')
                                                    ->label('Tipe')
                                                    ->helperText('Periode = berlaku rentang tanggal. Permanen = lifetime.')
                                                    ->options([
                                                        0 => 'Periode',
                                                        1 => 'Permanen',
                                                    ])
                                                    ->required()
                                                    ->default(0)
                                                    ->live()
                                                    ->afterStateUpdated(function (Set $set, $state): void {
                                                        if ((int) $state === 1) {
                                                            $set('start', null);
                                                            $set('end', null);
                                                        }
                                                    })
                                                    ->columnSpan(3),

                                                Textarea::make('reward')
                                                    ->label('Deskripsi Hadiah')
                                                    ->helperText('Deskripsi singkat hadiah/benefit (opsional).')
                                                    ->rows(3)
                                                    ->columnSpan(12),

                                                TextInput::make('value')
                                                    ->label('Nilai Reward')
                                                    ->helperText('Nilai reward (decimal). Kosong = 0.')
                                                    ->numeric()
                                                    ->default(0)
                                                    ->columnSpan(3),

                                                TextInput::make('bv')
                                                    ->label('Syarat BV')
                                                    ->helperText('Business Volume minimal. Kosong = 0.')
                                                    ->numeric()
                                                    ->default(0)
                                                    ->columnSpan(3),

                                                DatePicker::make('start')
                                                    ->label('Tanggal Mulai')
                                                    ->helperText('Wajib untuk tipe Periode.')
                                                    ->native(false)
                                                    ->visible(fn (Get $get) => (int) ($get('type') ?? 0) === 0)
                                                    ->required(fn (Get $get) => (int) ($get('type') ?? 0) === 0)
                                                    ->columnSpan(3),

                                                DatePicker::make('end')
                                                    ->label('Tanggal Selesai')
                                                    ->helperText('Wajib untuk tipe Periode.')
                                                    ->native(false)
                                                    ->visible(fn (Get $get) => (int) ($get('type') ?? 0) === 0)
                                                    ->required(fn (Get $get) => (int) ($get('type') ?? 0) === 0)
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
                                            ->content('Reward permanen disimpan tanpa start/end. Item yang dihapus dari form akan dinonaktifkan (status=0) agar aman.')
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
        $items = (array) ($state['rewards'] ?? []);

        DB::transaction(function () use ($items) {
            // ✅ FIX: withoutGlobalScopes() sudah builder, jangan ->query() lagi
            $existingIds = Reward::withoutGlobalScopes()
                ->pluck('id')
                ->map(fn ($v) => (int) $v)
                ->all();

            $keptIds = [];

            foreach ($items as $row) {
                $id = Arr::get($row, 'id');
                $id = is_numeric($id) ? (int) $id : null;

                $type = (int) ($row['type'] ?? 0);
                $status = (int) ((bool) ($row['status'] ?? true));

                $payload = [
                    'code'   => $this->nullIfBlank($row['code'] ?? null),
                    'name'   => trim((string) ($row['name'] ?? '')),
                    'reward' => $this->nullIfBlank($row['reward'] ?? null),
                    'value'  => $this->toFloatOrZero($row['value'] ?? null),
                    'bv'     => $this->toFloatOrZero($row['bv'] ?? null),
                    'type'   => $type,
                    'status' => $status,
                    'start'  => $type === 0 ? $this->nullIfBlank($row['start'] ?? null) : null,
                    'end'    => $type === 0 ? $this->nullIfBlank($row['end'] ?? null) : null,
                ];

                if ($id) {
                    Reward::withoutGlobalScopes()
                        ->whereKey($id)
                        ->update($payload);

                    $keptIds[] = $id;
                } else {
                    // model kamu timestamps=false, jadi set created_at manual
                    $payload['created_at'] = Carbon::now();
                    $created = Reward::withoutGlobalScopes()->create($payload);

                    $keptIds[] = (int) $created->id;
                }
            }

            // ✅ nonaktifkan yang tidak ada di form
            $removedIds = array_values(array_diff($existingIds, $keptIds));
            if (! empty($removedIds)) {
                Reward::withoutGlobalScopes()
                    ->whereIn('id', $removedIds)
                    ->update(['status' => 0]);
            }
        });

        Notification::make()
            ->success()
            ->title('Tersimpan')
            ->body('Pengaturan reward berhasil diperbarui.')
            ->send();

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
        // ✅ FIX: withoutGlobalScopes() sudah builder, jangan ->query()
        $rows = Reward::withoutGlobalScopes()
            ->orderBy('id')
            ->get();

        return [
            'rewards' => $rows->map(function (Reward $r) {
                return [
                    'id' => (int) $r->id,
                    'code' => $r->code,
                    'name' => (string) ($r->name ?? ''),
                    'reward' => $r->reward,
                    'value' => $this->toFloatOrZero($r->value),
                    'bv' => $this->toFloatOrZero($r->bv),
                    'type' => (int) ($r->type ?? 0),
                    'status' => (bool) ($r->status ?? 0),
                    'start' => $r->start?->toDateString(),
                    'end' => $r->end?->toDateString(),
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
}
