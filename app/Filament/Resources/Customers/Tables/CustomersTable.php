<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Models\Customer;
use App\Models\CustomerWhatsAppConfirmation;
use App\Services\QontactService;
use App\Support\CustomerUiSettingsConfig;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CustomersTable
{
    private static function hiddenByDefault(Column $column): Column
    {
        return $column->toggleable(isToggledHiddenByDefault: true);
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns(self::columns())
            ->filters(self::filters(), layout: FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(6)
            ->filtersFormSchema(self::filtersFormSchema())
            ->recordActions(self::recordActions())
            ->toolbarActions(self::toolbarActions());
    }

    /**
     * @return array<int, Column>
     */
    private static function columns(): array
    {
        $settings = CustomerUiSettingsConfig::tableColumnSettings();
        $columns = [];

        foreach (self::columnDefinitions() as $key => $factory) {
            $config = $settings[$key] ?? ['enabled' => true, 'hidden_by_default' => false];

            if (! ($config['enabled'] ?? true)) {
                continue;
            }

            $column = $factory();

            if ((bool) ($config['hidden_by_default'] ?? false)) {
                $column = self::hiddenByDefault($column);
            }

            $columns[] = $column;
        }

        return $columns;
    }

    /**
     * @return array<int, SelectFilter|TernaryFilter|Filter>
     */
    private static function filters(): array
    {
        $settings = CustomerUiSettingsConfig::tableFilterSettings();
        $filters = [];

        foreach (self::filterDefinitions() as $key => $factory) {
            if (! ($settings[$key] ?? true)) {
                continue;
            }

            $filters[] = $factory();
        }

        return $filters;
    }

    private static function createdAtFilter(): Filter
    {
        return Filter::make('created_at')
            ->label('Tanggal Bergabung')
            ->schema([
                Grid::make(2)->schema([
                    DatePicker::make('from')->label('Dari'),
                    DatePicker::make('until')->label('Sampai'),
                ]),
            ])
            ->query(fn (Builder $query, array $data): Builder => $query
                ->when($data['from'] ?? null, fn (Builder $query) => $query->whereDate('created_at', '>=', $data['from']))
                ->when($data['until'] ?? null, fn (Builder $query) => $query->whereDate('created_at', '<=', $data['until']))
            )
            ->indicateUsing(function (array $data): array {
                $indicators = [];

                if ($data['from'] ?? null) {
                    $indicators[] = Indicator::make('Bergabung dari '.Carbon::parse($data['from'])->toFormattedDateString())
                        ->removeField('from');
                }

                if ($data['until'] ?? null) {
                    $indicators[] = Indicator::make('Bergabung sampai '.Carbon::parse($data['until'])->toFormattedDateString())
                        ->removeField('until');
                }

                return $indicators;
            });
    }

    private static function filtersFormSchema(): \Closure
    {
        return function (array $filters): array {
            $sections = [];
            $classification = [];
            $flags = [];
            $period = [];

            if (isset($filters['status'])) {
                $classification[] = $filters['status'];
            }

            if (isset($filters['package_id'])) {
                $classification[] = $filters['package_id']->columnSpan(2);
            }

            if (isset($filters['level'])) {
                $classification[] = $filters['level'];
            }

            if (isset($filters['gender'])) {
                $classification[] = $filters['gender'];
            }

            if ($classification !== []) {
                $sections[] = Section::make('Klasifikasi')
                    ->description('Filter utama untuk status, paket, level, dan identitas umum.')
                    ->schema($classification)
                    ->columns(6)
                    ->columnSpanFull();
            }

            if (isset($filters['is_stockist'])) {
                $flags[] = $filters['is_stockist']->columnSpan(2);
            }

            if (isset($filters['network_generated'])) {
                $flags[] = $filters['network_generated']->columnSpan(2);
            }

            if (isset($filters['email_verified_at'])) {
                $flags[] = $filters['email_verified_at']->columnSpan(2);
            }

            if ($flags !== []) {
                $sections[] = Section::make('Flags')
                    ->description('Filter boolean / ternary untuk kondisi khusus.')
                    ->schema($flags)
                    ->columns(6)
                    ->columnSpanFull();
            }

            if (isset($filters['created_at'])) {
                $period[] = $filters['created_at']->columnSpanFull();
            }

            if ($period !== []) {
                $sections[] = Section::make('Periode')
                    ->description('Filter tanggal bergabung (opsional).')
                    ->schema($period)
                    ->columnSpanFull();
            }

            return $sections;
        };
    }

    /**
     * @return array<string, \Closure(): Column>
     */
    private static function columnDefinitions(): array
    {
        $statusLabels = self::statusLabels();
        $statusColors = self::statusColors();

        return [
            'username' => fn (): Column => TextColumn::make('username')
                ->label('Username')
                ->searchable()
                ->sortable(),
            'ewallet_id' => fn (): Column => TextColumn::make('ewallet_id')
                ->label('Ewallet ID')
                ->searchable()
                ->placeholder('-')
                ->sortable(),
            'name' => fn (): Column => TextColumn::make('name')
                ->label('Nama')
                ->searchable()
                ->sortable(),
            'package_name' => fn (): Column => TextColumn::make('package.name')
                ->label('Paket Member')
                ->searchable()
                ->placeholder('-'),
            'level' => fn (): Column => TextColumn::make('level')
                ->label('Peringkat')
                ->badge()
                ->sortable()
                ->placeholder('-'),
            'phone' => fn (): Column => TextColumn::make('phone')
                ->label('Telepon')
                ->searchable()
                ->placeholder('-'),
            'ewallet_saldo' => fn (): Column => TextColumn::make('ewallet_saldo')
                ->label('Saldo')
                ->sortable()
                ->alignEnd()
                ->formatStateUsing(fn (mixed $state): string => 'Rp '.number_format((float) $state, 0, ',', '.')),
            'sponsor_name' => fn (): Column => TextColumn::make('sponsor.name')
                ->label('Sponsor')
                ->description(fn ($record): ?string => filled($record->sponsor?->username) ? '@'.$record->sponsor->username : null)
                ->searchable()
                ->placeholder('-'),
            'status' => fn (): Column => TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->sortable()
                ->formatStateUsing(fn (mixed $state): string => $statusLabels[(int) $state] ?? (string) $state)
                ->color(fn (mixed $state): string => $statusColors[(int) $state] ?? 'gray'),
            'ref_code' => fn (): Column => TextColumn::make('ref_code')
                ->label('Ref Code')
                ->searchable(),
            'nik' => fn (): Column => TextColumn::make('nik')
                ->label('NIK')
                ->searchable(),
            'email' => fn (): Column => TextColumn::make('email')
                ->label('Email')
                ->searchable(),
            'gender' => fn (): Column => TextColumn::make('gender')
                ->label('Gender')
                ->badge(),
            'address' => fn (): Column => TextColumn::make('address')
                ->label('Alamat')
                ->searchable(),
            'city_id' => fn (): Column => TextColumn::make('city_id')
                ->label('City ID')
                ->numeric()
                ->sortable(),
            'province_id' => fn (): Column => TextColumn::make('province_id')
                ->label('Province ID')
                ->numeric()
                ->sortable(),
            'email_verified_at' => fn (): Column => TextColumn::make('email_verified_at')
                ->label('Email Verified')
                ->dateTime()
                ->sortable(),
            'bonus_pending' => fn (): Column => TextColumn::make('bonus_pending')
                ->label('Bonus Pending')
                ->numeric()
                ->sortable(),
            'bonus_processed' => fn (): Column => TextColumn::make('bonus_processed')
                ->label('Bonus Processed')
                ->numeric()
                ->sortable(),
            'bank_name' => fn (): Column => TextColumn::make('bank_name')
                ->label('Bank')
                ->searchable(),
            'bank_account' => fn (): Column => TextColumn::make('bank_account')
                ->label('No Rekening')
                ->searchable(),
            'created_at' => fn (): Column => TextColumn::make('created_at')
                ->label('Created')
                ->dateTime()
                ->sortable(),
            'updated_at' => fn (): Column => TextColumn::make('updated_at')
                ->label('Updated')
                ->dateTime()
                ->sortable(),
        ];
    }

    /**
     * @return array<string, \Closure(): SelectFilter|TernaryFilter|Filter>
     */
    private static function filterDefinitions(): array
    {
        $statusLabels = self::statusLabels();

        return [
            'status' => fn (): SelectFilter => SelectFilter::make('status')
                ->label('Status')
                ->placeholder('Semua')
                ->options($statusLabels),
            'package_id' => fn (): SelectFilter => SelectFilter::make('package_id')
                ->label('Paket')
                ->placeholder('Semua')
                ->relationship('package', 'name')
                ->searchable()
                ->preload(),
            'level' => fn (): SelectFilter => SelectFilter::make('level')
                ->label('Level')
                ->placeholder('Semua')
                ->options([
                    'Associate' => 'Associate',
                    'Senior Associate' => 'Senior Associate',
                    'Executive' => 'Executive',
                    'Director' => 'Director',
                ]),
            'gender' => fn (): SelectFilter => SelectFilter::make('gender')
                ->label('Jenis Kelamin')
                ->placeholder('Semua')
                ->options([
                    'male' => 'Laki-laki',
                    'female' => 'Perempuan',
                    'L' => 'L',
                    'P' => 'P',
                ]),
            'is_stockist' => fn (): TernaryFilter => TernaryFilter::make('is_stockist')
                ->label('Stockist')
                ->placeholder('Semua')
                ->trueLabel('Ya')
                ->falseLabel('Tidak'),
            'network_generated' => fn (): TernaryFilter => TernaryFilter::make('network_generated')
                ->label('Network Generated')
                ->placeholder('Semua')
                ->trueLabel('Ya')
                ->falseLabel('Tidak'),
            'email_verified_at' => fn (): TernaryFilter => TernaryFilter::make('email_verified_at')
                ->label('Verifikasi Email')
                ->placeholder('Semua')
                ->trueLabel('Sudah Terverifikasi')
                ->falseLabel('Belum Terverifikasi')
                ->queries(
                    true: fn (Builder $query): Builder => $query->whereNotNull('email_verified_at'),
                    false: fn (Builder $query): Builder => $query->whereNull('email_verified_at'),
                    blank: fn (Builder $query): Builder => $query,
                ),
            'created_at' => fn (): Filter => self::createdAtFilter(),
        ];
    }

    /**
     * @return array<int, string>
     */
    private static function statusLabels(): array
    {
        $labels = CustomerUiSettingsConfig::statusLabels();

        return $labels !== []
            ? $labels
            : [
                1 => 'Prospek',
                2 => 'Pasif',
                3 => 'Aktif',
            ];
    }

    /**
     * @return array<int, string>
     */
    private static function statusColors(): array
    {
        $colors = CustomerUiSettingsConfig::statusColors();

        return $colors !== []
            ? $colors
            : [
                1 => 'gray',
                2 => 'warning',
                3 => 'success',
            ];
    }

    /**
     * @return array<int, Action|ActionGroup>
     */
    private static function recordActions(): array
    {
        return [
            self::impersonateAction(),
            self::injectEwallet(),
            self::confirmWhatsAppManual(),
            ActionGroup::make([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]),
        ];
    }

    private static function impersonateAction(): Action
    {
        return Action::make('impersonate')
            ->tooltip('Impersonate')
            ->icon('bi-rocket-takeoff')
            ->requiresConfirmation()
            ->action(function (Customer $record): void {
                $adminUser = Auth::guard('web')->user();

                if (Auth::guard('customer')->check()) {
                    Auth::guard('customer')->logout();
                }

                Auth::guard('customer')->login($record);
                session()->regenerate();
                session()->put('impersonation', [
                    'is_active' => true,
                    'admin_id' => $adminUser?->id,
                    'admin_name' => $adminUser?->name,
                    'customer_id' => $record->id,
                ]);
            })
            ->successNotificationTitle('Berhasil masuk sebagai customer')
            ->successRedirectUrl(route('dashboard'));
    }

    private static function injectEwallet(): Action
    {
        return Action::make('inject_ewallet')
            ->label('Inject E-Wallet')
            ->tooltip('Menambahkan saldo secara manual ke akun member')
            ->icon('heroicon-o-wallet')
            ->form([
                TextInput::make('amount')
                    ->label('Jumlah Saldo')
                    ->numeric()
                    ->prefix('IDR')
                    ->required()
                    ->minValue(10000)
                    ->helperText('Masukkan nominal angka tanpa titik/koma.'),

                Textarea::make('note')
                    ->label('Keterangan / Alasan')
                    ->placeholder('Contoh: Koreksi bonus atau kompensasi sistem.')
                    ->required()
                    ->maxLength(500)
                    ->rows(3),
            ])
            ->action(function (Customer $record, array $data): void {
                $amount = (float) ($data['amount'] ?? 0);
                $note = trim((string) ($data['note'] ?? ''));

                try {
                    $isUpdated = $record->addBalance(
                        $amount,
                        $note !== '' ? $note : 'Top up ewallet'
                    );

                    if (! $isUpdated) {
                        throw new \RuntimeException('Saldo gagal diperbarui.');
                    }

                    $record->refresh();

                    Notification::make()
                        ->success()
                        ->title('Saldo Berhasil Diperbarui')
                        ->body('Berhasil melakukan inject saldo sebesar Rp '.number_format($amount, 0, ',', '.').'.')
                        ->send();
                } catch (\Throwable $exception) {
                    Notification::make()
                        ->danger()
                        ->title('Terjadi Kesalahan')
                        ->body('Gagal melakukan inject saldo: '.$exception->getMessage())
                        ->send();
                }
            })
            ->requiresConfirmation()
            ->modalIcon('heroicon-o-banknotes')
            ->modalHeading('Konfirmasi Inject Saldo')
            ->modalDescription('Pastikan nominal dan alasan sudah sesuai. Tindakan ini akan tercatat di log sistem.');
    }

    private static function confirmWhatsAppManual(): Action
    {
        return Action::make('confirm_wa_manual')
            ->label('Konfirmasi WA')
            ->tooltip('Konfirmasi nomor WhatsApp customer secara manual')
            ->icon('heroicon-o-check-badge')
            ->color('success')
            ->hidden(fn (Customer $record): bool => self::isWaAlreadyConfirmed($record))
            ->requiresConfirmation()
            ->modalHeading('Konfirmasi WhatsApp Manual')
            ->modalIcon('heroicon-o-chat-bubble-left-ellipsis')
            ->modalDescription(fn (Customer $record): string => sprintf(
                'Konfirmasi nomor WhatsApp %s (%s) secara manual. Gunakan ini jika webhook Qontak tidak bisa menjangkau server (contoh: development) atau pesan sudah terkirim namun sistem belum mendeteksinya.',
                $record->name,
                $record->phone ?? '-',
            ))
            ->action(function (Customer $record): void {
                $phone = app(QontactService::class)->normalizePhoneNumber((string) ($record->phone ?? ''));

                if ($phone === '') {
                    Notification::make()
                        ->warning()
                        ->title('Nomor Tidak Valid')
                        ->body('Customer ini tidak memiliki nomor telepon yang valid.')
                        ->send();

                    return;
                }

                CustomerWhatsAppConfirmation::recordIncoming($phone, (int) $record->getKey());

                Notification::make()
                    ->success()
                    ->title('WhatsApp Terkonfirmasi')
                    ->body('Nomor '.$phone.' berhasil dikonfirmasi secara manual.')
                    ->send();
            });
    }

    private static function isWaAlreadyConfirmed(Customer $record): bool
    {
        $phone = app(QontactService::class)->normalizePhoneNumber((string) ($record->phone ?? ''));

        if ($phone === '') {
            return true;
        }

        return CustomerWhatsAppConfirmation::isConfirmed($phone);
    }

    /**
     * @return array<int, BulkActionGroup>
     */
    private static function toolbarActions(): array
    {
        return [
            BulkActionGroup::make([
                DeleteBulkAction::make(),
            ]),
        ];
    }
}
