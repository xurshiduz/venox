<?php

namespace App\Exports;

use App\Models\CashReceipt;
use App\Models\Checkin;
use App\Models\Checkout;
use App\Models\Client;
use App\Models\Currency;
use App\Services\AccountingCashReportService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CheckoutMonthExport implements FromView, WithStyles
{
    protected $monthYear;
    protected $rowCount = 0;

    public function __construct($monthYear)
    {
        $this->monthYear = $monthYear;
    }

    public function view(): View
    {
        $date = Carbon::parse($this->monthYear);
        $periodStart = $date->copy()->startOfMonth();
        $periodEnd = $date->copy()->endOfMonth();

        $checkouts = Checkout::with(['supid', 'checkoutDetails.prodid'])
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        $clientIds = $checkouts->pluck('client_id')->filter()->unique()->values();
        $accounting = app(AccountingCashReportService::class);
        $clientPayments = [];

        $payments = CashReceipt::query()
            ->where('status', 1)
            ->whereIn('client_id', $clientIds)
            ->whereBetween('date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->with('checkout:id,currency_type,currency_type_price')
            ->get();

        foreach ($payments as $payment) {
            $key = (string) $payment->client_id;
            $usd = $payment->checkout
                ? $accounting->paymentAmountToUsd(
                    (float) $payment->price,
                    (int) $payment->currency_type,
                    (float) $payment->currency_type_price,
                    $payment->checkout->currency_type !== null ? (int) $payment->checkout->currency_type : null,
                    $payment->checkout->currency_type_price !== null ? (float) $payment->checkout->currency_type_price : null
                )
                : $accounting->legacyUnlinkedPaymentToUsd(
                    (float) $payment->price,
                    (int) $payment->currency_type,
                    (float) ($payment->currency_type_price ?: Currency::usdRateForDate($payment->date ?: $payment->created_at)),
                    (string) $payment->comment
                );
            $clientPayments[$key] = ($clientPayments[$key] ?? 0) + $usd;
        }

        $closingDebts = $this->clientDebtTotalsUsd($clientIds->map(fn ($id) => (string) $id)->all(), $periodEnd);
        $rows = [];
        $seenClients = [];

        foreach ($checkouts as $checkout) {
            $clientKey = (string) $checkout->client_id;
            foreach ($checkout->checkoutDetails as $detail) {
                $qty = (float) $detail->qty;
                $totalUsd = Currency::documentAmountToUsd(
                    (float) ($detail->price_total ?? ((float) $detail->price * $qty)),
                    (int) $checkout->currency_type,
                    (float) ($checkout->currency_type_price ?? 0),
                    $checkout->date ?: $checkout->created_at
                );
                $firstClientRow = !isset($seenClients[$clientKey]);
                $paid = (float) ($clientPayments[$clientKey] ?? 0);
                $closing = (float) ($closingDebts[$clientKey] ?? 0);

                $rows[] = [
                    'date' => Carbon::parse($checkout->date ?: $checkout->created_at)->format('d.m.Y'),
                    'client' => $checkout->supid->name ?? 'Noma\'lum mijoz',
                    'debt_before_payment' => $firstClientRow ? $closing + $paid : null,
                    'product' => $detail->prodid->name ?? 'Noma\'lum mahsulot',
                    'qty' => $qty,
                    'unit_price_usd' => $qty != 0 ? $totalUsd / $qty : 0,
                    'total_usd' => $totalUsd,
                    'paid_usd' => $firstClientRow ? $paid : null,
                    'closing_debt_usd' => $firstClientRow ? $closing : null,
                ];

                $seenClients[$clientKey] = true;
            }
        }

        $this->rowCount = count($rows);

        return view('backend.checkouts.excel_matrix', [
            'rows' => $rows,
            'monthYear' => $this->monthYear,
        ]);
    }

    private function clientDebtTotalsUsd(array $clientKeys, Carbon $periodEnd): array
    {
        $clientIds = collect($clientKeys)->filter(fn ($id) => ctype_digit((string) $id))->map(fn ($id) => (int) $id)->values();
        if ($clientIds->isEmpty()) {
            return [];
        }

        $totals = [];
        foreach (Client::whereIn('id', $clientIds)->get() as $client) {
            $totals[(string) $client->id] = Currency::documentAmountToUsd(
                (float) ($client->balance ?? 0),
                (int) ($client->currency_type ?? 2),
                (float) ($client->currency_type_price ?? 0),
                $client->created_at
            );
        }

        foreach (Checkout::with('alldetails')->whereIn('client_id', $clientIds)->whereDate('date', '<=', $periodEnd)->get() as $checkout) {
            $totals[(string) $checkout->client_id] = ($totals[(string) $checkout->client_id] ?? 0) + Currency::documentAmountToUsd(
                (float) $checkout->alldetails->sum('total_price'),
                (int) $checkout->currency_type,
                (float) $checkout->currency_type_price,
                $checkout->date ?: $checkout->created_at
            );
        }

        $accounting = app(AccountingCashReportService::class);
        foreach (CashReceipt::whereIn('client_id', $clientIds)->where('status', 1)->whereDate('date', '<=', $periodEnd)->with('checkout:id,currency_type,currency_type_price')->get() as $receipt) {
            $usd = $receipt->checkout
                ? $accounting->paymentAmountToUsd((float) $receipt->price, (int) $receipt->currency_type, (float) $receipt->currency_type_price, $receipt->checkout->currency_type, $receipt->checkout->currency_type_price)
                : $accounting->legacyUnlinkedPaymentToUsd((float) $receipt->price, (int) $receipt->currency_type, (float) ($receipt->currency_type_price ?: Currency::usdRateForDate($receipt->date ?: $receipt->created_at)), (string) $receipt->comment);
            $totals[(string) $receipt->client_id] = ($totals[(string) $receipt->client_id] ?? 0) - $usd;
        }

        foreach (Checkin::with('details')->whereIn('client_id', $clientIds)->where('type_id', 4)->whereDate('date', '<=', $periodEnd)->get() as $return) {
            $totals[(string) $return->client_id] = ($totals[(string) $return->client_id] ?? 0) - Currency::documentAmountToUsd(
                (float) $return->details->sum('total_price'),
                (int) $return->currency_type,
                (float) $return->currency_type_price,
                $return->date ?: $return->created_at
            );
        }

        return $totals;
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = max(3, $this->rowCount + 3);
        $sheet->setShowGridlines(false);
        $sheet->freezePane('A3');
        $sheet->setAutoFilter('A2:I' . max(2, $this->rowCount + 2));
        $sheet->getDefaultRowDimension()->setRowHeight(24);
        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->getRowDimension(2)->setRowHeight(42);

        foreach (['A' => 13, 'B' => 28, 'C' => 20, 'D' => 52, 'E' => 15, 'F' => 16, 'G' => 18, 'H' => 17, 'I' => 18] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $sheet->getStyle('A1:I' . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        $sheet->getStyle('A2:I2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2:I2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1F4E78');
        $sheet->getStyle('A2:I2')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A2:I' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('B7C9DD');
        $sheet->getStyle('A3:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E3:E' . $lastRow)->getNumberFormat()->setFormatCode('#,##0.###');
        $sheet->getStyle('C3:C' . $lastRow)->getNumberFormat()->setFormatCode('$#,##0.00');
        $sheet->getStyle('F3:I' . $lastRow)->getNumberFormat()->setFormatCode('$#,##0.00');
        $sheet->getStyle('A' . $lastRow . ':I' . $lastRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9EAF7');
        $sheet->getStyle('A' . $lastRow . ':I' . $lastRow)->getFont()->setBold(true);
    }
}
