<?php

namespace App\Services\Orders;

use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Throwable;

class OrderInvoicePdfService
{
    /**
     * @return array{filename:string,content:string}
     */
    public function generate(Order $order): array
    {
        $order->loadMissing([
            'customer:id,name,email,phone',
            'shippingAddress',
            'billingAddress',
            'items:id,order_id,name,sku,qty,unit_price,row_total',
            'payments:id,order_id,status,provider_txn_id,transaction_id,created_at',
        ]);

        $store = $this->resolveStoreProfile();
        $currency = trim((string) ($order->currency ?: 'IDR'));
        $invoiceNumber = $this->invoiceNumber($order, $store['invoice_prefix']);
        $paid = filled($order->paid_at);
        $latestPayment = $this->resolveLatestPayment($order->payments);
        $items = $this->transformItems($order, $currency);

        $totals = [
            'subtotal' => $this->formatMoney((float) ($order->subtotal_amount ?? 0), $currency),
            'discount' => $this->formatMoney((float) ($order->discount_amount ?? 0), $currency),
            'shipping' => $this->formatMoney((float) ($order->shipping_amount ?? 0), $currency),
            'tax' => $this->formatMoney((float) ($order->tax_amount ?? 0), $currency),
            'grand_total' => $this->formatMoney((float) ($order->grand_total ?? 0), $currency),
        ];

        $invoice = [
            'number' => $invoiceNumber,
            'order_number' => (string) ($order->order_no ?: '-'),
            'issued_at' => $order->created_at?->format('d M Y H:i') ?: '-',
            'paid_at' => $order->paid_at?->format('d M Y H:i') ?: '-',
            'status_label' => $paid ? 'PAID' : 'UNPAID',
            'status_class' => $paid ? 'status-paid' : 'status-unpaid',
            'payment_status' => strtoupper((string) ($latestPayment?->status ?: $order->status ?: '-')),
            'payment_reference' => (string) ($latestPayment?->provider_txn_id ?: $latestPayment?->transaction_id ?: '-'),
            'customer_name' => trim((string) data_get($order, 'customer.name', '-')),
            'customer_email' => trim((string) data_get($order, 'customer.email', '-')),
            'customer_phone' => trim((string) data_get($order, 'customer.phone', '-')),
            'shipping_address' => $this->formatAddress($order->shippingAddress),
            'billing_address' => $this->formatAddress($order->billingAddress),
            'notes' => trim((string) ($order->notes ?? '')),
            'currency' => $currency,
        ];

        $html = view('pdf.orders.invoice', [
            'store' => $store,
            'invoice' => $invoice,
            'items' => $items,
            'totals' => $totals,
        ])->render();

        $content = $this->renderPdf($html, $invoiceNumber, $store['name']);

        return [
            'filename' => $this->buildFilename($order),
            'content' => $content,
        ];
    }

    /**
     * @param  Collection<int, Payment>|mixed  $payments
     */
    private function resolveLatestPayment(mixed $payments): ?Payment
    {
        if (! $payments instanceof Collection) {
            return null;
        }

        $latest = $payments
            ->sortByDesc(function (Payment $payment): string {
                return $payment->created_at?->format('Y-m-d H:i:s.u') ?? '';
            })
            ->first();

        return $latest instanceof Payment ? $latest : null;
    }

    private function invoiceNumber(Order $order, string $prefix): string
    {
        $safePrefix = trim($prefix) !== '' ? trim($prefix) : 'INV';

        return $safePrefix.'-'.trim((string) ($order->order_no ?: '-'));
    }

    /**
     * @return array<int, array{no:int,name:string,sku:string,qty:string,unit_price:string,row_total:string}>
     */
    private function transformItems(Order $order, string $currency): array
    {
        return $order->items
            ->values()
            ->map(function (mixed $item, int $index) use ($currency): array {
                return [
                    'no' => $index + 1,
                    'name' => trim((string) data_get($item, 'name', '-')),
                    'sku' => trim((string) data_get($item, 'sku', '-')),
                    'qty' => number_format((float) data_get($item, 'qty', 0), 0, ',', '.'),
                    'unit_price' => $this->formatMoney((float) data_get($item, 'unit_price', 0), $currency),
                    'row_total' => $this->formatMoney((float) data_get($item, 'row_total', 0), $currency),
                ];
            })
            ->all();
    }

    /**
     * @return array{
     *     name:string,
     *     legal_name:string,
     *     email:string,
     *     phone:string,
     *     website:string,
     *     address:string,
     *     logo_path:string|null,
     *     tagline:string,
     *     primary_color:string,
     *     secondary_color:string,
     *     invoice_prefix:string
     * }
     */
    private function resolveStoreProfile(): array
    {
        $keys = [
            'store.name',
            'store.legal_name',
            'store.email',
            'store.phone',
            'store.website',
            'branding.tagline',
            'branding.logo',
            'branding.primary_color',
            'branding.secondary_color',
            'address.line1',
            'address.line2',
            'address.city',
            'address.province',
            'address.postal_code',
            'address.country',
            'preferences.order_prefix',
            'preferences.support_email',
        ];

        try {
            $settings = Setting::query()
                ->whereIn('key', $keys)
                ->pluck('value', 'key');
        } catch (Throwable) {
            $settings = collect();
        }

        $storeName = trim((string) ($settings['store.name'] ?? config('app.name', 'E-Commerce Store')));
        $legalName = trim((string) ($settings['store.legal_name'] ?? ''));
        $storeEmail = trim((string) ($settings['store.email'] ?? $settings['preferences.support_email'] ?? ''));
        $storePhone = trim((string) ($settings['store.phone'] ?? ''));
        $storeWebsite = trim((string) ($settings['store.website'] ?? config('app.url', '')));
        $tagline = trim((string) ($settings['branding.tagline'] ?? ''));
        $invoicePrefix = trim((string) ($settings['preferences.order_prefix'] ?? 'INV'));

        $address = collect([
            $settings['address.line1'] ?? null,
            $settings['address.line2'] ?? null,
            $settings['address.city'] ?? null,
            $settings['address.province'] ?? null,
            $settings['address.postal_code'] ?? null,
            $settings['address.country'] ?? null,
        ])
            ->map(static fn (mixed $value): string => trim((string) $value))
            ->filter(static fn (string $value): bool => $value !== '')
            ->implode(', ');

        return [
            'name' => $storeName !== '' ? $storeName : 'E-Commerce Store',
            'legal_name' => $legalName,
            'email' => $storeEmail,
            'phone' => $storePhone,
            'website' => $storeWebsite,
            'address' => $address,
            'logo_path' => $this->resolveLogoPath((string) ($settings['branding.logo'] ?? '')) ?? $this->defaultCompanyLogoPath(),
            'tagline' => $tagline,
            'primary_color' => $this->sanitizeColor((string) ($settings['branding.primary_color'] ?? '#0f172a')),
            'secondary_color' => $this->sanitizeColor((string) ($settings['branding.secondary_color'] ?? '#111827')),
            'invoice_prefix' => $invoicePrefix !== '' ? $invoicePrefix : 'INV',
        ];
    }

    private function resolveLogoPath(string $logoValue): ?string
    {
        $path = trim($logoValue);

        if ($path === '') {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $path) === 1) {
            return $path;
        }

        $publicDiskPath = storage_path('app/public/'.ltrim($path, '/'));

        if (is_file($publicDiskPath)) {
            return $publicDiskPath;
        }

        $publicPath = public_path(ltrim($path, '/'));

        return is_file($publicPath) ? $publicPath : null;
    }

    private function defaultCompanyLogoPath(): ?string
    {
        $candidates = [
            public_path('assets/logo.webp'),
            public_path('logo.webp'),
            storage_path('app/public/assets/logo.webp'),
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function sanitizeColor(string $color): string
    {
        $normalized = trim($color);

        if (preg_match('/^#[a-fA-F0-9]{6}$/', $normalized) === 1) {
            return strtoupper($normalized);
        }

        return '#111827';
    }

    private function renderPdf(string $html, string $invoiceNumber, string $storeName): string
    {
        $tempDir = storage_path('app/mpdf-temp');

        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        $mpdf = new Mpdf([
            'format' => 'A4',
            'mode' => 'utf-8',
            'margin_left' => 12,
            'margin_right' => 12,
            'margin_top' => 12,
            'margin_bottom' => 14,
            'tempDir' => $tempDir,
        ]);

        $mpdf->SetTitle($invoiceNumber);
        $mpdf->SetAuthor($storeName);
        $mpdf->SetSubject('Invoice E-Commerce');
        $mpdf->SetDisplayMode('fullpage');

        $resolvedLinkedStylesheets = $this->resolveLinkedStylesheets($html);
        $html = $resolvedLinkedStylesheets['html'];
        $css = trim(implode("\n\n", array_filter([
            $this->resolveInvoiceCss(),
            $resolvedLinkedStylesheets['css'],
        ])));

        if ($css !== '') {
            $mpdf->WriteHTML($css, HTMLParserMode::HEADER_CSS);
        }

        $mpdf->WriteHTML($html);

        return (string) $mpdf->Output('', Destination::STRING_RETURN);
    }

    /**
     * @return array{html:string,css:string}
     */
    private function resolveLinkedStylesheets(string $html): array
    {
        preg_match_all('/<link\b[^>]*>/i', $html, $matches);

        if (! isset($matches[0]) || ! is_array($matches[0]) || $matches[0] === []) {
            return [
                'html' => $html,
                'css' => '',
            ];
        }

        $resolvedCss = [];
        $filteredHtml = $html;

        foreach ($matches[0] as $linkTag) {
            if (! is_string($linkTag) || ! $this->isStylesheetLinkTag($linkTag)) {
                continue;
            }

            $href = $this->extractHrefFromLinkTag($linkTag);

            if ($href === null) {
                continue;
            }

            $css = $this->resolveStylesheetContent($href);

            if ($css !== '') {
                $resolvedCss[] = $css;
                $filteredHtml = str_replace($linkTag, '', $filteredHtml);
            }
        }

        return [
            'html' => $filteredHtml,
            'css' => implode("\n\n", $resolvedCss),
        ];
    }

    private function isStylesheetLinkTag(string $linkTag): bool
    {
        if (preg_match('/\brel\s*=\s*["\']([^"\']*)["\']/i', $linkTag, $matches) !== 1) {
            return false;
        }

        return str_contains(strtolower((string) ($matches[1] ?? '')), 'stylesheet');
    }

    private function extractHrefFromLinkTag(string $linkTag): ?string
    {
        if (preg_match('/\bhref\s*=\s*["\']([^"\']+)["\']/i', $linkTag, $matches) !== 1) {
            return null;
        }

        $href = trim((string) ($matches[1] ?? ''));

        if ($href === '') {
            return null;
        }

        return html_entity_decode($href, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function resolveStylesheetContent(string $href): string
    {
        $normalizedHref = preg_replace('/[?#].*$/', '', trim($href));
        $normalizedHref = is_string($normalizedHref) ? trim($normalizedHref) : '';

        if ($normalizedHref === '') {
            return '';
        }

        if (str_starts_with($normalizedHref, '//')) {
            $normalizedHref = 'https:'.$normalizedHref;
        }

        if ($this->isBootstrapThreeStylesheet($normalizedHref)) {
            return $this->resolveStylesheetFallbackContent($normalizedHref);
        }

        if (preg_match('/^https?:\/\//i', $normalizedHref) === 1) {
            $remoteCss = $this->resolveRemoteStylesheetContent($normalizedHref);
            $fallbackCss = $this->resolveStylesheetFallbackContent($normalizedHref);

            return $this->mergeStylesheetContent($remoteCss, $fallbackCss);
        }

        $localPath = $this->resolveStylesheetLocalPath($normalizedHref);

        $localCss = '';

        if ($localPath !== null && is_file($localPath)) {
            $css = file_get_contents($localPath);
            $localCss = is_string($css) ? $css : '';
        }

        $fallbackCss = $this->resolveStylesheetFallbackContent($normalizedHref);

        return $this->mergeStylesheetContent($localCss, $fallbackCss);
    }

    private function resolveRemoteStylesheetContent(string $href): string
    {
        try {
            $response = Http::timeout(10)->retry(1, 200)->get($href);

            return $response->successful() ? (string) $response->body() : '';
        } catch (Throwable) {
            return '';
        }
    }

    private function resolveStylesheetFallbackContent(string $href): string
    {
        if (! $this->isBootstrapThreeStylesheet($href)) {
            return '';
        }

        $candidates = [
            resource_path('css/pdf/bootstrap3-fallback.css'),
            public_path('css/pdf/bootstrap3-fallback.css'),
        ];

        foreach ($candidates as $candidate) {
            if (! is_file($candidate)) {
                continue;
            }

            $css = file_get_contents($candidate);

            if (is_string($css) && trim($css) !== '') {
                return $css;
            }
        }

        return '';
    }

    private function isBootstrapThreeStylesheet(string $href): bool
    {
        $normalizedHref = strtolower(trim($href));

        if (! str_contains($normalizedHref, 'bootstrap') || ! str_contains($normalizedHref, 'bootstrap.min.css')) {
            return false;
        }

        return preg_match('/(?:@|\/)3\.\d+\.\d+(?:\/|$)/', $normalizedHref) === 1;
    }

    private function mergeStylesheetContent(string $primaryCss, string $fallbackCss): string
    {
        $normalizedPrimaryCss = trim($primaryCss);
        $normalizedFallbackCss = trim($fallbackCss);

        if ($normalizedPrimaryCss === '') {
            return $normalizedFallbackCss;
        }

        if ($normalizedFallbackCss === '') {
            return $normalizedPrimaryCss;
        }

        return $normalizedPrimaryCss."\n\n".$normalizedFallbackCss;
    }

    private function resolveStylesheetLocalPath(string $href): ?string
    {
        $trimmedHref = trim($href);

        if ($trimmedHref === '') {
            return null;
        }

        if (is_file($trimmedHref)) {
            return $trimmedHref;
        }

        $publicRelativePath = ltrim($trimmedHref, '/');
        $publicPath = public_path($publicRelativePath);

        if (is_file($publicPath)) {
            return $publicPath;
        }

        return null;
    }

    private function resolveInvoiceCss(): string
    {
        $sourceCssPath = resource_path('css/pdf/invoice.css');

        if (! is_file($sourceCssPath)) {
            return '';
        }

        $manifestPath = public_path('build/manifest.json');

        if (! is_file($manifestPath)) {
            return '';
        }

        $manifestRaw = file_get_contents($manifestPath);

        if (! is_string($manifestRaw) || trim($manifestRaw) === '') {
            return '';
        }

        $manifest = json_decode($manifestRaw, true);

        if (! is_array($manifest)) {
            return '';
        }

        $file = $manifest['resources/css/pdf/invoice.css']['file'] ?? null;

        if (! is_string($file) || trim($file) === '') {
            return '';
        }

        $assetPath = public_path('build/'.ltrim($file, '/'));

        if (! is_file($assetPath)) {
            return '';
        }

        $css = file_get_contents($assetPath);

        return is_string($css) ? $css : '';
    }

    private function formatAddress(?CustomerAddress $address): string
    {
        if (! $address) {
            return '-';
        }

        return collect([
            $address->recipient_name,
            $address->recipient_phone,
            $address->address_line1,
            $address->address_line2,
            $address->district,
            $address->city_label,
            $address->province_label,
            $address->postal_code,
            $address->country,
        ])
            ->filter(static fn (mixed $value): bool => filled($value))
            ->map(static fn (mixed $value): string => trim((string) $value))
            ->implode(', ');
    }

    private function buildFilename(Order $order): string
    {
        $invoiceCode = preg_replace('/[^A-Za-z0-9\-]+/', '-', (string) $order->order_no) ?: 'invoice';

        return "invoice-{$invoiceCode}.pdf";
    }

    private function formatMoney(float $amount, string $currency): string
    {
        if (strtoupper($currency) === 'IDR') {
            return 'Rp '.number_format($amount, 0, ',', '.');
        }

        return strtoupper($currency).' '.number_format($amount, 2, '.', ',');
    }
}
