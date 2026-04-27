<?php

namespace App\Filament\Pages;

use App\Models\Reward;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\SelectColumn;

use Filament\Tables\Filters\SelectFilter;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use UnitEnum;

class LifetimeCashRewardSettings extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $title = 'Pengaturan Reward';
    protected ?string $subheading = 'Pengaturan toko untuk mengelola informasi reward.';
    protected static ?string $navigationLabel = 'Pengaturan Reward';
    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';

    protected string $view = 'filament.pages.lifetime-cash-reward-settings';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getBaseQuery())
            ->defaultSort('created_at', 'desc')
            ->paginated([15, 25, 50, 100])
            ->defaultPaginationPageOption(15)
            ->emptyStateHeading('Belum ada reward lifetime')
            ->emptyStateDescription('Klik "Tambah Reward" untuk membuat reward baru.')
            ->columns([
                TextInputColumn::make('code')
                    ->label('Kode')
                    ->rules(['nullable', 'string', 'max:50'])
                    ->searchable()
                    ->sortable()
                    ->afterStateUpdated(function (Reward $record, $state): void {
                        $normalized = $this->nullIfBlank($state);

                        // rapikan null / blank
                        if ($record->code !== $normalized) {
                            $record->forceFill(['code' => $normalized])->save();
                        }
                    }),

                TextInputColumn::make('name')
                    ->label('Nama')
                    ->rules(['required', 'string', 'max:150'])
                    ->searchable()
                    ->sortable()
                    ->afterStateUpdated(function (Reward $record, $state): void {
                        $normalized = trim((string) ($state ?? ''));

                        if ($record->name !== $normalized) {
                            $record->forceFill(['name' => $normalized])->save();
                        }
                    }),

                TextInputColumn::make('reward')
                    ->label('Deskripsi')
                    ->rules(['nullable', 'string'])
                    ->afterStateUpdated(function (Reward $record, $state): void {
                        $normalized = $this->nullIfBlank($state);

                        if ($record->reward !== $normalized) {
                            $record->forceFill(['reward' => $normalized])->save();
                        }
                    }),

                TextInputColumn::make('value')
                    ->label('Value')
                    ->type('number')
                    ->inputMode('decimal')
                    ->step('any')
                    ->rules(['numeric', 'min:0'])
                    ->sortable()
                    ->afterStateUpdated(function (Reward $record, $state): void {
                        $normalized = $this->toFloatOrZero($state);

                        // record cast decimal:2 → bisa string, bandingin float
                        if ((float) $record->value !== (float) $normalized) {
                            $record->forceFill(['value' => $normalized])->save();
                        }
                    }),

                TextInputColumn::make('bv')
                    ->label('BV')
                    ->type('number')
                    ->inputMode('decimal')
                    ->step('any')
                    ->rules(['numeric', 'min:0'])
                    ->sortable()
                    ->afterStateUpdated(function (Reward $record, $state): void {
                        $normalized = $this->toFloatOrZero($state);

                        if ((float) $record->bv !== (float) $normalized) {
                            $record->forceFill(['bv' => $normalized])->save();
                        }
                    }),

                SelectColumn::make('status')
                    ->label('Status')
                    ->options([
                        1 => 'Aktif',
                        0 => 'Nonaktif',
                    ])
                    ->rules(['required', 'in:0,1'])
                    ->sortable()
                    ->afterStateUpdated(function (Reward $record, $state): void {
                        $normalized = (int) ((string) $state === '1' ? 1 : 0);

                        if ((int) $record->status !== $normalized) {
                            $record->forceFill(['status' => $normalized])->save();
                        }
                    }),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        1 => 'Aktif',
                        0 => 'Nonaktif',
                    ]),
            ])
            ->headerActions([
                Action::make('create')
                    ->label('Tambah Reward')
                    ->icon('heroicon-m-plus')
                    ->color('primary')
                    ->modalHeading('Tambah Reward Lifetime')
                    ->modalSubmitActionLabel('Simpan')
                    ->form([
                        TextInput::make('code')
                            ->label('Kode')
                            ->maxLength(50)
                            ->helperText('Opsional. Contoh: RW-001'),

                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(150),

                        Textarea::make('reward')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->helperText('Opsional.'),

                        TextInput::make('value')
                            ->label('Value')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),

                        TextInput::make('bv')
                            ->label('BV')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                1 => 'Aktif',
                                0 => 'Nonaktif',
                            ])
                            ->default(1)
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        $payload = [
                            'code' => $this->nullIfBlank($data['code'] ?? null),
                            'name' => trim((string) ($data['name'] ?? '')),
                            'reward' => $this->nullIfBlank($data['reward'] ?? null),
                            'value' => $this->toFloatOrZero($data['value'] ?? null),
                            'bv' => $this->toFloatOrZero($data['bv'] ?? null),
                            'status' => (int) ((string) ($data['status'] ?? '1') === '1' ? 1 : 0),

                            // lifetime
                            'type' => 1,
                            'start' => null,
                            'end' => null,

                            // model timestamps=false
                            'created_at' => Carbon::now(),
                        ];

                        Reward::query()->create($payload);

                        Notification::make()
                            ->success()
                            ->title('Berhasil dibuat')
                            ->body('Reward lifetime berhasil ditambahkan.')
                            ->send();
                    }),
            ])
            ->recordActions([
                DeleteAction::make()
                    ->label('Hapus')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus reward?')
                    ->modalDescription('Data reward akan dihapus permanen.')
                    ->successNotificationTitle('Reward dihapus'),
            ])
            ->bulkActions([
                DeleteBulkAction::make()
                    ->label('Hapus terpilih')
                    ->requiresConfirmation(),
            ]);
    }

    protected function getBaseQuery(): Builder
    {
        return Reward::query()->where('type', 1);
    }

    // =========================
    // Helpers (null safe)
    // =========================
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
