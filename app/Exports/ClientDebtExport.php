<?php

namespace App\Exports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;

class ClientDebtExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function collection()
    {
        // 1. Ma'lumotlarni bazadan olamiz
        $clients = Client::with([
            'checkouts.alldetails', // Sotuv detallari
            'checkins.details',  // Vozvrat detallari
            'cashReceipts' => function($query) {
                $query->where('status', 1);
            }
        ])
        ->where('id', '!=', 1)
        ->get();
    
        // 2. Collectionni FILTRLAYMIZ (Faqat qarzi borlarni qoldiramiz)
        return $clients->filter(function ($client) {
            
            // A. Jami savdo
            $sales = 0;
            foreach ($client->checkouts as $checkout) {
                if ($checkout->alldetails) {
                    $sales += $checkout->alldetails->sum('total_price');
                }
            }
    
            // B. Jami pul to'lovlari
            $cash = $client->cashReceipts ? $client->cashReceipts->sum('price') : 0;
    
            // C. Jami vozvratlar
            $returns = 0;
            if ($client->checkins) {
                foreach ($client->checkins as $checkin) {
                    if ($checkin->details) {
                        $returns += $checkin->details->sum('total_price');
                    }
                }
            }
    
            // D. Hisob-kitob
            $total_debt = $sales - ($cash + $returns);
    
            // E. Qarz 0 dan katta bo'lsa, ro'yxatda qolsin (TRUE), bo'lmasa olib tashlansin (FALSE)
            // Kichik tiyin farqlari bo'lmasligi uchun 0.01 dan katta deb tekshirgan ma'qul
            return $total_debt > 0.01; 
        });
    }

    public function map($client): array
    {
        // 1. SOTUVLAR (Jami qarz oshishi)
        $total_sales = 0;
        if ($client->checkouts) {
            foreach ($client->checkouts as $checkout) {
                if ($checkout->alldetails) {
                    $total_sales += $checkout->alldetails->sum('total_price');
                }
            }
        }

        // 2. TULOVLAR (Pul ko'rinishida)
        $cash_paid = $client->cashReceipts ? $client->cashReceipts->sum('price') : 0;

        // 3. VOZVRATLAR (Checkin - Tovar qaytarish) <--- YANGI QISM
        $goods_returned = 0;
        if ($client->checkins) {
            foreach ($client->checkins as $checkin) {
                // CheckinDetail dagi total_price larni yig'amiz
                if ($checkin->details) {
                    $goods_returned += $checkin->details->sum('total_price');
                }
            }
        }

        // 4. JAMI QOPLANGAN SUMMA (Pul + Vozvrat)
        $total_payments = $cash_paid + $goods_returned;

        // 5. HAQIQIY QARZ
        $total_debt = $total_sales - $total_payments;

        // 6. MUDDATLI QARZLAR (30, 60, 90)
        $debt_30 = 0;
        $debt_60 = 0;
        $debt_90 = 0;

        if ($total_debt > 0 && $client->checkouts) {
            $remaining_debt = $total_debt;
            
            // Eng yangi savdolarni olamiz
            $sorted_checkouts = $client->checkouts->sortByDesc('date');

            foreach ($sorted_checkouts as $checkout) {
                if ($remaining_debt <= 0) break;

                $checkout_sum = $checkout->alldetails ? $checkout->alldetails->sum('total_price') : 0;
                
                if ($checkout_sum > 0) {
                    $amount_in_debt = min($checkout_sum, $remaining_debt);
                    $days_diff = Carbon::parse($checkout->date)->diffInDays(now());

                    if ($days_diff > 90) {
                        $debt_90 += $amount_in_debt;
                    } elseif ($days_diff > 60) {
                        $debt_60 += $amount_in_debt;
                    } elseif ($days_diff > 30) {
                        $debt_30 += $amount_in_debt;
                    }

                    $remaining_debt -= $amount_in_debt;
                }
            }
        }

        // 7. OXIRGI TULOV (Faqat pul tushumi hisobga olinadi, vozvrat emas)
        $last_payment = null;
        if ($client->cashReceipts) {
            $last_payment = $client->cashReceipts
                ->where('price', '>', 0)
                ->sortByDesc('date')
                ->first();
        }

        return [
            $client->name,
            $client->phone ?? '', 
            number_format($total_debt, 2, '.', ''), 
            number_format($debt_30, 2, '.', ''),    
            number_format($debt_60, 2, '.', ''),    
            number_format($debt_90, 2, '.', ''),    
            $last_payment ? number_format($last_payment->price, 2, '.', '') : '', 
            $last_payment ? Carbon::parse($last_payment->date)->format('Y-m-d') : '', 
        ];
    }
    
    public function headings(): array
    {
        return [
            'Мижоз исми', 'Тел рақами', 'Умумий қарзи', '30 кундан ошган қарз', 
            '60 кундан ошган қарз', '90 кундан ошган қарз', 'Охирги тўлов суммаси', 'Охирги тўлов датаси',
        ];
    }
}