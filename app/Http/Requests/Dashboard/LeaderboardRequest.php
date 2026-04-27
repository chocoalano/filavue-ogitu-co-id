<?php

namespace App\Http\Requests\Dashboard;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeaderboardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->resolveAuthenticatedCustomer() !== null;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'tab' => ['nullable', 'integer', Rule::in([1, 2, 3])],
            'selected_tab' => ['nullable', 'integer', Rule::in([1, 2, 3])],
            'period' => ['nullable', 'string', Rule::in(['daily', 'weekly', 'monthly', 'harian', 'mingguan', 'bulanan'])],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'tab.integer' => 'Parameter tab harus berupa angka.',
            'tab.in' => 'Parameter tab harus bernilai 1 (Harian), 2 (Mingguan), atau 3 (Bulanan).',
            'selected_tab.integer' => 'Parameter selected_tab harus berupa angka.',
            'selected_tab.in' => 'Parameter selected_tab harus bernilai 1 (Harian), 2 (Mingguan), atau 3 (Bulanan).',
            'period.in' => 'Parameter period harus bernilai daily/weekly/monthly atau harian/mingguan/bulanan.',
        ];
    }

    /**
     * @return array{selected_tab:int,period_key:string}
     */
    public function payload(): array
    {
        $selectedTab = $this->normalizeSelectedTab($this->input('tab'));

        if ($selectedTab === null) {
            $selectedTab = $this->normalizeSelectedTab($this->input('selected_tab'));
        }

        if ($selectedTab === null) {
            $selectedTab = $this->mapPeriodToTab((string) $this->input('period', ''));
        }

        $selectedTab ??= 1;

        return [
            'selected_tab' => $selectedTab,
            'period_key' => $this->mapTabToPeriod($selectedTab),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'period' => strtolower(trim((string) $this->input('period', ''))),
        ]);
    }

    private function normalizeSelectedTab(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $tab = (int) $value;

        return in_array($tab, [1, 2, 3], true) ? $tab : null;
    }

    private function mapPeriodToTab(string $period): ?int
    {
        return match (strtolower(trim($period))) {
            'daily', 'harian' => 1,
            'weekly', 'mingguan' => 2,
            'monthly', 'bulanan' => 3,
            default => null,
        };
    }

    private function mapTabToPeriod(int $tab): string
    {
        return match ($tab) {
            2 => 'weekly',
            3 => 'monthly',
            default => 'daily',
        };
    }

    private function resolveAuthenticatedCustomer(): ?Customer
    {
        $customer = $this->user('customer');

        if ($customer instanceof Customer) {
            return $customer;
        }

        $tokenable = $this->user('sanctum');

        return $tokenable instanceof Customer ? $tokenable : null;
    }
}
