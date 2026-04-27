<?php

namespace App\Filament\Pages;

use App\Models\Customer;
use App\Models\CustomerPackage;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class CustomerSponsorshipNetworks extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $title = 'Melihat Jaringan Sponsorship';

    protected ?string $subheading = 'Lihat jaringan sponsorship member berdasarkan generasi sponsor.';

    protected static ?string $navigationLabel = 'Melihat Jaringan Sponsorship';

    protected static ?string $slug = 'affiliate/jaringan-sponsorship';

    protected static string|UnitEnum|null $navigationGroup = 'Affiliate';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-m-user-group';

    protected string $view = 'filament.pages.customer-sponsorship-networks';

    public static function canAccess(): bool
    {
        return Filament::auth()->user()?->can('ViewAny:CustomerNetworkMatrix') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getBaseQuery())
            ->defaultSort('level')
            ->paginated([15, 25, 50, 100])
            ->defaultPaginationPageOption(15)
            ->emptyStateHeading('Belum ada jaringan sponsorship')
            ->emptyStateDescription('Data sponsor matrix akan tampil di halaman ini setelah jaringan member terbentuk.')
            ->columns([
                TextColumn::make('member.name')
                    ->label('Nama')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('member.username')
                    ->label('Username')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('member.package.name')
                    ->label('Paket')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('member.level')
                    ->label('Peringkat')
                    ->placeholder('-')
                    ->badge()
                    ->sortable(),

                TextColumn::make('member.omzet_group')
                    ->label('Omset Group')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('generation')
                    ->label('Generasi')
                    ->placeholder('Semua generasi')
                    ->options(fn (): array => self::generationOptions())
                    ->query(function (Builder $query, array $data): Builder {
                        $generation = $data['value'] ?? null;

                        if (blank($generation)) {
                            return $query;
                        }

                        return $query->where('level', (int) $generation);
                    }),
                SelectFilter::make('package_id')
                    ->label('Paket')
                    ->placeholder('Semua paket')
                    ->options(fn (): array => self::packageOptions())
                    ->searchable()
                    ->query(function (Builder $query, array $data): Builder {
                        $packageId = $data['value'] ?? null;

                        if (blank($packageId)) {
                            return $query;
                        }

                        return $query->whereHas('member', function (Builder $memberQuery) use ($packageId): Builder {
                            return $memberQuery->where('package_id', (int) $packageId);
                        });
                    }),
                SelectFilter::make('member_level')
                    ->label('Peringkat')
                    ->placeholder('Semua peringkat')
                    ->options(fn (): array => self::rankOptions())
                    ->searchable()
                    ->query(function (Builder $query, array $data): Builder {
                        $rank = $data['value'] ?? null;

                        if (blank($rank)) {
                            return $query;
                        }

                        return $query->whereHas('member', function (Builder $memberQuery) use ($rank): Builder {
                            return $memberQuery->where('level', $rank);
                        });
                    }),
            ], layout: FiltersLayout::BeforeContent);
    }

    protected function getBaseQuery(): Builder
    {
        return CustomerNetworkMatrix::query()
            ->with([
                'member:id,name,username,package_id,level,omzet_group',
                'member.package:id,name',
            ])
            ->whereNotNull('member_id');
    }

    /**
     * @return array<int|string, string>
     */
    protected static function generationOptions(): array
    {
        return CustomerNetworkMatrix::query()
            ->whereNotNull('level')
            ->orderBy('level')
            ->pluck('level')
            ->unique()
            ->mapWithKeys(static fn (mixed $level): array => [
                (int) $level => 'Generasi '.(int) $level,
            ])
            ->all();
    }

    /**
     * @return array<int|string, string>
     */
    protected static function packageOptions(): array
    {
        return CustomerPackage::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    /**
     * @return array<int|string, string>
     */
    protected static function rankOptions(): array
    {
        return Customer::query()
            ->whereNotNull('level')
            ->where('level', '!=', '')
            ->orderBy('level')
            ->pluck('level')
            ->unique()
            ->mapWithKeys(static fn (mixed $level): array => [
                (string) $level => (string) $level,
            ])
            ->all();
    }
}
