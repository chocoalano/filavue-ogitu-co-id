<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $invoice['number'] ?? 'Invoice' }}</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css"
        integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
</head>

<body class="invoice-page invoice-page-compact">
    <div class="container-fluid invoice-container invoice-shell">
        <div class="invoice-header" style="border-top-color: {{ $store['primary_color'] ?? '#2563eb' }};">
            <table class="invoice-header-table">
                <tr>
                    <td class="invoice-header-left">
                        @if (filled($store['logo_path'] ?? null))
                            <img src="{{ $store['logo_path'] }}" alt="Logo" class="invoice-logo">
                        @endif
                        <div class="invoice-store-name">{{ $store['name'] ?? '-' }}</div>
                        @if (filled($store['tagline'] ?? null))
                            <div class="invoice-tagline">{{ $store['tagline'] }}</div>
                        @endif
                    </td>
                    <td class="invoice-header-right">
                        <div class="invoice-title-wrap">
                            <div class="invoice-title">INVOICE</div>
                            <div class="invoice-subtitle">Dokumen Tagihan Resmi</div>
                            <div class="invoice-header-status text-right">
                                <span class="label invoice-status-label {{ $invoice['status_class'] ?? 'status-unpaid' }}">{{ $invoice['status_label'] ?? '-' }}</span>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="invoice-header-meta-cell">
                        <table class="invoice-info-table">
                            <tr>
                                <td class="invoice-info-item">
                                    <div class="invoice-info-item-label">Nomor Invoice</div>
                                    <div class="invoice-info-item-value nowrap">{{ $invoice['number'] ?? '-' }}</div>
                                </td>
                                <td class="invoice-info-item">
                                    <div class="invoice-info-item-label">Nomor Pesanan</div>
                                    <div class="invoice-info-item-value nowrap">{{ $invoice['order_number'] ?? '-' }}</div>
                                </td>
                                <td class="invoice-info-item">
                                    <div class="invoice-info-item-label">Tanggal Terbit</div>
                                    <div class="invoice-info-item-value nowrap">{{ $invoice['issued_at'] ?? '-' }}</div>
                                </td>
                                <td class="invoice-info-item">
                                    <div class="invoice-info-item-label">Tanggal Bayar</div>
                                    <div class="invoice-info-item-value nowrap">{{ $invoice['paid_at'] ?? '-' }}</div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <div class="invoice-meta-strip">
            <table class="invoice-meta-strip-table">
                <tr>
                    <td><span class="meta-strip-label">Pelanggan</span> {{ $invoice['customer_name'] ?? '-' }}</td>
                    <td class="text-right"><span class="meta-strip-label">No. Pesanan</span> {{ $invoice['order_number'] ?? '-' }}</td>
                </tr>
            </table>
        </div>

        <table class="invoice-party-table">
            <tr>
                <td class="invoice-party-cell invoice-party-cell-left">
                    <div class="panel panel-default">
                        <div class="panel-heading" style="color: {{ $store['secondary_color'] ?? '#111827' }};">Diterbitkan Oleh</div>
                        <div class="panel-body">
                            <div class="invoice-party-name">{{ ($store['legal_name'] ?? '') !== '' ? $store['legal_name'] : ($store['name'] ?? '-') }}</div>
                            <table class="invoice-party-detail-table">
                                @if (filled($store['address'] ?? null))
                                    <tr class="invoice-party-row-address">
                                        <td class="invoice-party-line">
                                            <span class="invoice-party-line-label">Alamat</span>
                                            <span class="invoice-party-line-value">{{ $store['address'] }}</span>
                                        </td>
                                    </tr>
                                @endif
                                @if (filled($store['email'] ?? null))
                                    <tr>
                                        <td class="invoice-party-line">
                                            <span class="invoice-party-line-label">Email</span>
                                            <span class="invoice-party-line-value">{{ $store['email'] }}</span>
                                        </td>
                                    </tr>
                                @endif
                                @if (filled($store['phone'] ?? null))
                                    <tr>
                                        <td class="invoice-party-line">
                                            <span class="invoice-party-line-label">Telepon</span>
                                            <span class="invoice-party-line-value">{{ $store['phone'] }}</span>
                                        </td>
                                    </tr>
                                @endif
                                @if (filled($store['website'] ?? null))
                                    <tr>
                                        <td class="invoice-party-line">
                                            <span class="invoice-party-line-label">Website</span>
                                            <span class="invoice-party-line-value">{{ $store['website'] }}</span>
                                        </td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </td>
                <td class="invoice-party-cell invoice-party-cell-right">
                    <div class="panel panel-default">
                        <div class="panel-heading" style="color: {{ $store['secondary_color'] ?? '#111827' }};">Ditagihkan Kepada</div>
                        <div class="panel-body">
                            <div class="invoice-party-name">{{ $invoice['customer_name'] ?? '-' }}</div>
                            <table class="invoice-party-detail-table">
                                @if (filled($invoice['customer_email'] ?? null) && ($invoice['customer_email'] ?? '-') !== '-')
                                    <tr>
                                        <td class="invoice-party-line">
                                            <span class="invoice-party-line-label">Email</span>
                                            <span class="invoice-party-line-value">{{ $invoice['customer_email'] }}</span>
                                        </td>
                                    </tr>
                                @endif
                                @if (filled($invoice['customer_phone'] ?? null) && ($invoice['customer_phone'] ?? '-') !== '-')
                                    <tr>
                                        <td class="invoice-party-line">
                                            <span class="invoice-party-line-label">Telepon</span>
                                            <span class="invoice-party-line-value">{{ $invoice['customer_phone'] }}</span>
                                        </td>
                                    </tr>
                                @endif
                                <tr class="invoice-party-row-address">
                                    <td class="invoice-party-line">
                                        <span class="invoice-party-line-label">Alamat Pengiriman</span>
                                        <span class="invoice-party-line-value">{{ $invoice['shipping_address'] ?? '-' }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="invoice-party-line">
                                        <span class="invoice-party-line-label">Alamat Penagihan</span>
                                        <span class="invoice-party-line-value">{{ $invoice['billing_address'] ?? '-' }}</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <table class="table table-bordered table-condensed invoice-items-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 43%;">Produk</th>
                    <th style="width: 14%;">SKU</th>
                    <th style="width: 10%;" class="text-right">Qty</th>
                    <th style="width: 14%;" class="text-right">Harga</th>
                    <th style="width: 14%;" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr @class(['invoice-item-row', 'invoice-item-row-alt' => $loop->even])>
                        <td>{{ $item['no'] }}</td>
                        <td>{{ $item['name'] }}</td>
                        <td>{{ $item['sku'] }}</td>
                        <td class="text-right nowrap">{{ $item['qty'] }}</td>
                        <td class="text-right nowrap">{{ $item['unit_price'] }}</td>
                        <td class="text-right nowrap">{{ $item['row_total'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-left">Tidak ada item pada pesanan ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="invoice-summary-wrap">
            <table class="table table-condensed invoice-summary-table">
                <tr class="invoice-summary-head">
                    <td colspan="2">Ringkasan Pembayaran</td>
                </tr>
                <tr>
                    <td class="text-muted">Subtotal</td>
                    <td class="text-right strong">{{ $totals['subtotal'] ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Diskon</td>
                    <td class="text-right strong">{{ $totals['discount'] ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Ongkos Kirim</td>
                    <td class="text-right strong">{{ $totals['shipping'] ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Pajak</td>
                    <td class="text-right strong">{{ $totals['tax'] ?? '-' }}</td>
                </tr>
                <tr class="invoice-grand-total">
                    <td>Total Akhir</td>
                    <td class="text-right">{{ $totals['grand_total'] ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Status Pembayaran</td>
                    <td class="text-right strong nowrap">{{ $invoice['payment_status'] ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Referensi Pembayaran</td>
                    <td class="text-right strong nowrap">{{ $invoice['payment_reference'] ?? '-' }}</td>
                </tr>
            </table>
        </div>

        @if (($invoice['notes'] ?? '') !== '')
            <div class="invoice-note">
                <div class="invoice-note-title">Catatan Pesanan</div>
                <div>{{ $invoice['notes'] }}</div>
            </div>
        @endif

        <div class="invoice-footer text-muted">
            Dokumen ini dibuat otomatis oleh sistem e-commerce dan sah tanpa tanda tangan.
            <br>
            Terima kasih telah berbelanja di {{ $store['name'] ?? 'Toko Kami' }}.
            <span class="invoice-footer-meta">Dicetak: {{ now()->format('d M Y H:i') }}</span>
        </div>
    </div>
</body>

</html>
