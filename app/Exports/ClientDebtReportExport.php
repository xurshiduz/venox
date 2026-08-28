<?php

namespace App\Exports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;

class ClientDebtReportExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function query()
    {
        // Faqat qarzi bor (checkouts) mijozlarni olamiz
        // N+1 muammosini oldini olish uchun 'activeDebts' va 'lastPayment'ni oldindan yuklaymiz (eager load)
        return Client::query()
            ->whereHas('activeDebts') // Faqat qarzi borlarni olish
            ->with(['activeDebts', 'lastPayment']);
    }

    // Har bir qator uchun ma'lumotlarni hisoblash va joylash
    public function map($client): array
    {
        $now = Carbon::now();

        // Hisoblagichlarni 0 dan boshlaymiz
        $debt30 = 0;
        $debt60 = 0;
        $debt90 = 0;

        // Har bir qarz (checkout) bo'yicha aylanib chiqib, kerakli joyga qo'shamiz
        foreach ($client->activeDebts as $checkout) {
            // Qarz olinganiga necha kun bo'lganini hisoblash
            $daysDiff = Carbon::parse($checkout->date)->diffInDays($now);

            if ($daysDiff > 90) {
                // Agar 90 kundan ko'p bo'lsa, faqat 90 ligiga qo'shiladi
                $debt90 += $checkout->total_price_debt;
            } elseif ($daysDiff > 60) {
                // Yuqoridagi shart bajarilmasa (ya'ni 90 dan kam) va 60 dan ko'p bo'lsa
                $debt60 += $checkout->total_price_debt;
            } elseif ($daysDiff > 30) {
                // Yuqoridagi shartlar bajarilmasa (ya'ni 60 dan kam) va 30 dan ko'p bo'lsa
                $debt30 += $checkout->total_price_debt;
            }
        }

        // Jami qarz (barcha activeDebts summasi)
        $totalDebt = $client->activeDebts->sum('total_price_debt');

        // Oxirgi to'lov ma'lumotlari
        $lastPaymentAmount = $client->lastPayment ? $client->lastPayment->price : 0;
        $lastPaymentDate = $client->lastPayment ? Carbon::parse($client->lastPayment->date)->format('d.m.Y') : '-';

        return [
            $client->name,                              // Мижоз исми
            $client->phone,                             // Тел рақами
            number_format($totalDebt, 0, '.', ' '),     // Умумий қарзи
            number_format($debt30, 0, '.', ' '),        // 30-60 kun oralig'idagi qarz
            number_format($debt60, 0, '.', ' '),        // 61-90 kun oralig'idagi qarz
            number_format($debt90, 0, '.', ' '),        // 91+ kunlik qarz
            number_format($lastPaymentAmount, 0, '.', ' '), // Охирги тўлов суммаси
            $lastPaymentDate,                           // Охирги тўлов датаси
        ];
    }

    // Excel sarlavhalari
    public function headings(): array
    {
        return [
            'Мижоз исми',
            'Тел рақами',
            'Умумий қарзи',
            '30 кундан ошган қарз',
            '60 кундан ошган қарз',
            '90 кундан ошган қарз',
            'Охирги тўлов суммаси',
            'Охирги тўлов датаси',
        ];
    }
}