<?php

namespace App\Filament\Resources\BugReports\Tables;

use App\Enums\BugErrorCategory;
use App\Enums\BugPriority;
use App\Enums\BugReporterType;
use App\Enums\BugSeverity;
use App\Enums\BugSource;
use App\Enums\BugStatus;
use App\Enums\Platform;
use App\Filament\Resources\BugReports\BugReportResource;
use App\Filament\Resources\BugReports\Schemas\BugReportForm;
use App\Models\BugReport;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;

class BugReportsTable
{
    private static function hidden(TextColumn $column): TextColumn
    {
        return $column->toggleable(isToggledHiddenByDefault: true);
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->columns(self::columns())
            ->defaultSort('created_at', 'desc')
            ->filters(self::filters())
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->recordActions(self::recordActions())
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * @return array<int, TextColumn>
     */
    private static function columns(): array
    {
        return [
            TextColumn::make('id')
                ->label('#')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('title')
                ->label('Judul Bug')
                ->searchable()
                ->sortable()
                ->limit(40)
                ->tooltip(fn (TextColumn $column): ?string => strlen((string) $column->getState()) > 40
                    ? $column->getState()
                    : null),

            TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->sortable(),

            TextColumn::make('severity')
                ->label('Severity')
                ->badge()
                ->sortable(),

            TextColumn::make('priority')
                ->label('Prioritas')
                ->badge()
                ->sortable(),

            TextColumn::make('error_category')
                ->label('Kategori Error')
                ->badge()
                ->placeholder('—')
                ->sortable(),

            TextColumn::make('platform')
                ->label('Platform')
                ->badge()
                ->sortable(),

            TextColumn::make('source')
                ->label('Sumber')
                ->badge()
                ->sortable(),

            TextColumn::make('assignee.name')
                ->label('Ditugaskan')
                ->placeholder('—')
                ->icon('heroicon-o-user')
                ->sortable(),

            self::hidden(
                TextColumn::make('reporter_type')
                    ->label('Tipe Pelapor')
                    ->badge()
            ),

            self::hidden(
                TextColumn::make('page_url')
                    ->label('URL')
                    ->limit(40)
                    ->placeholder('—')
            ),

            TextColumn::make('created_at')
                ->label('Dilaporkan')
                ->dateTime('d M Y H:i')
                ->sortable()
                ->since(),
        ];
    }

    /**
     * @return array<int, SelectFilter>
     */
    private static function filters(): array
    {
        return [
            SelectFilter::make('status')
                ->label('Status')
                ->options(BugStatus::class)
                ->multiple(),

            SelectFilter::make('severity')
                ->label('Severity')
                ->options(BugSeverity::class)
                ->multiple(),

            SelectFilter::make('priority')
                ->label('Prioritas')
                ->options(BugPriority::class)
                ->multiple(),

            SelectFilter::make('platform')
                ->label('Platform')
                ->options(Platform::class),

            SelectFilter::make('source')
                ->label('Sumber')
                ->options(BugSource::class),

            SelectFilter::make('error_category')
                ->label('Kategori Error')
                ->options(BugErrorCategory::class)
                ->multiple(),

            SelectFilter::make('reporter_type')
                ->label('Tipe Pelapor')
                ->options(BugReporterType::class),
        ];
    }

    /**
     * @return array<int, ActionGroup>
     */
    private static function recordActions(): array
    {
        return [
            ActionGroup::make([
                self::chatAction(),
                self::repairAction(),
                ViewAction::make(),
                EditAction::make(),
            ]),
        ];
    }

    private static function chatAction(): Action
    {
        return Action::make('chat')
            ->label('Percakapan')
            ->icon('heroicon-o-chat-bubble-left-right')
            ->color('gray')
            ->url(fn (BugReport $record): string => BugReportResource::getUrl('chat', ['record' => $record]));
    }

    private static function repairAction(): Action
    {
        return Action::make('repair')
            ->label('Perbaikan')
            ->icon('heroicon-o-wrench-screwdriver')
            ->visible(fn (BugReport $record): bool => BugReportForm::canEditResolution($record) && $record->status !== BugStatus::Closed)
            ->modalHeading('Form Perbaikan')
            ->modalDescription('Isi hasil penanganan bug ini. Catatan resolusi dan waktu resolved hanya dapat diisi melalui dialog ini oleh user yang ditugaskan.')
            ->modalSubmitActionLabel('Simpan Perbaikan')
            ->fillForm(fn (BugReport $record): array => self::repairFormDefaults($record))
            ->form(self::repairForm())
            ->action(function (BugReport $record, array $data): void {
                self::persistRepair($record, $data);
            });
    }

    /**
     * @return array<int, Select|Textarea|DateTimePicker>
     */
    private static function repairForm(): array
    {
        return [
            Select::make('resolution_status')
                ->label('Hasil Penanganan')
                ->options([
                    BugStatus::Resolved->value => BugStatus::Resolved->getLabel(),
                    BugStatus::Rejected->value => BugStatus::Rejected->getLabel(),
                ])
                ->required()
                ->live()
                ->validationMessages([
                    'required' => 'Pilih hasil penanganan bug ini: Resolved atau Rejected.',
                ]),
            Textarea::make('resolution_note')
                ->label('Catatan Resolusi')
                ->required()
                ->rows(4)
                ->validationMessages([
                    'required' => 'Catatan resolusi wajib diisi oleh user yang ditugaskan.',
                ])
                ->placeholder('Jelaskan perbaikan yang dilakukan, hasil investigasi, atau alasan penolakan...'),
            DateTimePicker::make('resolved_at')
                ->label('Waktu Resolved')
                ->seconds(false)
                ->required(fn ($get): bool => $get('resolution_status') === BugStatus::Resolved->value)
                ->validationMessages([
                    'required' => 'Waktu resolved wajib diisi saat hasil penanganan ditandai Resolved.',
                ])
                ->helperText('Isi waktu penyelesaian jika bug sudah benar-benar selesai diperbaiki.'),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function persistRepair(BugReport $record, array $data): void
    {
        if (! BugReportForm::canEditResolution($record)) {
            throw ValidationException::withMessages([
                'data.assigned_to' => 'Dialog Perbaikan hanya dapat digunakan oleh user yang sedang ditugaskan menangani bug ini.',
            ]);
        }

        $resolutionStatus = BugStatus::tryFrom((string) ($data['resolution_status'] ?? ''));

        if (! in_array($resolutionStatus, [BugStatus::Resolved, BugStatus::Rejected], true)) {
            throw ValidationException::withMessages([
                'mountedActionsData.0.resolution_status' => 'Hasil penanganan bug harus berupa Resolved atau Rejected.',
            ]);
        }

        $resolvedAt = $data['resolved_at'] ?? null;

        if ($resolutionStatus === BugStatus::Resolved && blank($resolvedAt)) {
            throw ValidationException::withMessages([
                'mountedActionsData.0.resolved_at' => 'Waktu resolved wajib diisi saat hasil penanganan ditandai Resolved.',
            ]);
        }

        $record->forceFill([
            'status' => $resolutionStatus->value,
            'resolution_note' => trim((string) ($data['resolution_note'] ?? '')),
            'resolved_at' => filled($resolvedAt)
                ? $resolvedAt
                : ($resolutionStatus === BugStatus::Resolved ? ($record->resolved_at ?? now()) : $record->resolved_at),
        ])->save();

        Notification::make()
            ->success()
            ->title('Data perbaikan berhasil disimpan.')
            ->send();
    }

    /**
     * @return array<string, mixed>
     */
    private static function repairFormDefaults(BugReport $record): array
    {
        $defaultStatus = in_array($record->status, [BugStatus::Resolved, BugStatus::Rejected], true)
            ? $record->status->value
            : BugStatus::Resolved->value;

        return [
            'resolution_status' => $defaultStatus,
            'resolution_note' => $record->resolution_note,
            'resolved_at' => $record->resolved_at ?? now(),
        ];
    }
}
