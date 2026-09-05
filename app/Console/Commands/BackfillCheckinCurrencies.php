<?php

namespace App\Console\Commands;

use App\Models\CheckinDetail;
use App\Models\Currency;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillCheckinCurrencies extends Command
{
    protected $signature = 'checkins:backfill-currencies
                            {--apply : Apply detected corrections}
                            {--max-price=1000 : Maximum suspicious UZS-labelled price}
                            {--rate= : Fallback USD rate}';

    protected $description = 'Find old checkin rows whose USD prices were incorrectly marked as UZS';

    public function handle(): int
    {
        $fallbackRate = (float) ($this->option('rate') ?: Currency::where('type_id', 1)->latest('id')->value('price'));
        $fallbackRate = $fallbackRate > 1 ? $fallbackRate : 1;
        $maxPrice = (float) $this->option('max-price');
        $apply = (bool) $this->option('apply');
        $candidates = collect();

        CheckinDetail::query()
            ->with(['checkid:id,currency_type,currency_type_price,date,number_order', 'prodid:id,name,barcode,price'])
            ->where('status', 1)
            ->where('currency_type', 2)
            ->where('price', '>', 0)
            ->where('price', '<=', $maxPrice)
            ->orderBy('id')
            ->chunkById(500, function ($details) use (&$candidates, $fallbackRate) {
                foreach ($details as $detail) {
                    $saleUsd = (float) optional($detail->prodid)->price;
                    $purchaseUsd = (float) $detail->price;
                    $ratio = $saleUsd > 0 ? $purchaseUsd / $saleUsd : 0;

                    if ($saleUsd <= 0 || $ratio < 0.05 || $ratio > 5) {
                        continue;
                    }

                    $rate = (float) optional($detail->checkid)->currency_type_price;
                    $rate = $rate > 1 ? $rate : $fallbackRate;
                    $candidates->push(['detail' => $detail, 'rate' => $rate]);
                }
            });

        $this->table(
            ['Detail ID', 'Hujjat', 'Sana', 'Mahsulot', 'Eski narx', 'Yangi valyuta', 'Kurs', 'UZS ekvivalenti'],
            $candidates->map(function ($row) {
                $detail = $row['detail'];
                return [
                    $detail->id,
                    optional($detail->checkid)->number_order ?: $detail->checkin_id,
                    optional($detail->checkid)->date,
                    optional($detail->prodid)->name,
                    $detail->price,
                    'USD',
                    $row['rate'],
                    round((float) $detail->price * $row['rate'], 2),
                ];
            })->all()
        );

        if (!$apply) {
            $this->warn("{$candidates->count()} ta shubhali qator topildi. Hech narsa o‘zgartirilmadi.");
            $this->line('Tasdiqlangandan keyin --apply bilan ishga tushiring.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($candidates) {
            foreach ($candidates as $row) {
                $row['detail']->update([
                    'currency_type' => 1,
                    'currency_type_price' => $row['rate'],
                ]);
            }
        });

        $this->info("{$candidates->count()} ta qator USD sifatida belgilandi. Narx va miqdor o‘zgartirilmadi.");
        return self::SUCCESS;
    }
}
