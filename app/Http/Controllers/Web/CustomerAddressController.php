<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerAddress\SaveCustomerAddressRequest;
use App\Models\Customer;
use App\Repositories\Shipping\Contracts\ShippingTargetRepositoryInterface;
use App\Services\CustomerAddress\CustomerAddressService;
use App\Services\RajaOngkirService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CustomerAddressController extends Controller
{
    public function __construct(
        private readonly CustomerAddressService $customerAddressService,
        private readonly RajaOngkirService $rajaOngkirService,
        private readonly ShippingTargetRepositoryInterface $shippingRepository,
    ) {}

    public function index(): RedirectResponse
    {
        return redirect()->route('dashboard', ['section' => 'addresses']);
    }

    public function store(SaveCustomerAddressRequest $request): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        $this->customerAddressService->create($customer, $request->payload());

        return back()->with('success', 'Alamat berhasil ditambahkan.');
    }

    public function update(SaveCustomerAddressRequest $request, int $addressId): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        $this->customerAddressService->update($customer, $addressId, $request->payload());

        return back()->with('success', 'Alamat berhasil diperbarui.');
    }

    public function setDefault(Request $request, int $addressId): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        $this->customerAddressService->setDefault($customer, $addressId);

        return back()->with('success', 'Alamat default berhasil diperbarui.');
    }

    public function destroy(Request $request, int $addressId): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        $this->customerAddressService->delete($customer, $addressId);

        return back()->with('success', 'Alamat berhasil dihapus.');
    }

    public function provinceOptions(): JsonResponse
    {
        $items = collect($this->rajaOngkirService->getProvinces())
            ->map(function (mixed $province): ?array {
                $id = $this->extractRegionValue($province, ['id', 'province_id']);
                $label = $this->extractRegionValue($province, ['province_name', 'province', 'name']);

                if (! is_numeric($id) || ! is_string($label) || trim($label) === '') {
                    return null;
                }

                return [
                    'id' => (int) $id,
                    'label' => trim($label),
                ];
            })
            ->filter()
            ->values()
            ->all();

        return response()->json($items);
    }

    public function cityOptions(Request $request): JsonResponse
    {
        $provinceId = (int) $request->integer('province_id');

        if ($provinceId < 1) {
            return response()->json([]);
        }

        $items = collect($this->rajaOngkirService->getCities($provinceId))
            ->map(function (mixed $city) use ($provinceId): ?array {
                $id = $this->extractRegionValue($city, ['id', 'city_id']);
                $name = $this->extractRegionValue($city, ['city_name', 'city', 'name']);
                $type = $this->extractRegionValue($city, ['type']);

                if (! is_numeric($id) || ! is_string($name) || trim($name) === '') {
                    return null;
                }

                $label = is_string($type) && trim($type) !== ''
                    ? trim($type).' '.trim($name)
                    : trim($name);

                return [
                    'id' => (int) $id,
                    'province_id' => $provinceId,
                    'label' => $label,
                ];
            })
            ->filter()
            ->values()
            ->all();

        return response()->json($items);
    }

    public function districtOptions(Request $request): JsonResponse
    {
        $cityId = (int) $request->integer('city_id');

        if ($cityId < 1) {
            return response()->json([]);
        }

        return response()->json($this->shippingRepository->districtOptionsByCityId($cityId));
    }

    private function extractRegionValue(mixed $row, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (is_array($row) && array_key_exists($key, $row)) {
                return $row[$key];
            }

            if (is_object($row) && isset($row->{$key})) {
                return $row->{$key};
            }
        }

        return null;
    }
}
