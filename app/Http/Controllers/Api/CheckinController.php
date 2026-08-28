<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


use App\Models\CheckinDetail;
use App\Models\Product;
use App\Models\Checkin;

use Carbon\Carbon;
use Http;
use Str;

class CheckinController extends Controller
{
    public function apiCheckinSave(Request $request)
{
    try {
        $request->validate([
            'date'              => 'required|date',
            'reference'         => 'nullable|string',
            'details'           => 'required|array|min:1',
            'details.*.barcode' => 'required|string',
            'details.*.qty'     => 'required|numeric|min:0.01',
            'details.*.price'   => 'required|numeric|min:0',
        ]);

        $data['date']      = Carbon::parse($request->date)->format('Y-m-d');
        $data['client_id'] = 2;
        $data['warehouse_id'] = 2;
        $data['reference'] = $request->reference;
        $data['type_id']   = 1;
        $data['user_id']   = 1;
        $data['status']    = 1;
        $data['code']      = Str::uuid();

        $year  = Carbon::now()->format('Y');
        $slice = Checkin::whereYear('date', '=', $year)->max('number_order');

        $data['number_order'] = Str::padLeft(($slice + 1), 6, '0');
        $data['number_work']  = Carbon::now()->format('y') . $data['number_order'];

        $item = Checkin::create($data);

        $created = [];
        $skipped = [];

        foreach ($request->details as $detail) {
            $pid = Product::where('barcode', $detail['barcode'])->first();

            if (!$pid) {
                $skipped[] = $detail['barcode'];
                continue;
            }

            if (CheckinDetail::where('checkin_id', $item->id)->where('product_id', $pid->id)->exists()) {
                $skipped[] = $detail['barcode'];
                continue;
            }

            $detdata['checkin_id']          = $item->id;
            $detdata['product_id']          = $pid->id;
            $detdata['warehouse_id']        = 2;
            $detdata['category_id']         = $pid->category_id;
            $detdata['qty']                 = $detail['qty'];
            $detdata['status']              = 1;
            $detdata['currency_type']       = 2;
            $detdata['currency_type_price'] = 1;
            $detdata['price']               = $detail['price'];
            $detdata['total_price']         = $detail['price'] * $detail['qty'];
            $detdata['code']                = Str::uuid();
            $detdata['barcode']             = $this->generateUniqueDetailBarcode();

            $created[] = CheckinDetail::create($detdata);
        }

        return response()->json([
            'success'      => true,
            'checkin_id'   => $item->id,
            'checkin_code' => $item->code,
            'number_work'  => $item->number_work,
            'created'      => count($created),
            'skipped'      => $skipped,
        ]);

    } catch (\Throwable $e) {
        // VAQTINCHALIK: aniq xatoni ko'rish uchun
        return response()->json([
            'success' => false,
            'error'   => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
        ], 500);
    }
}

    private function generateUniqueDetailBarcode()
    {
        do {
            $barcode = mt_rand(10, 99) . time() . mt_rand(100, 999);
        } while (CheckinDetail::where('barcode', $barcode)->exists());

        return $barcode;
    }

    private function sendTelegramNotification($item, $createdDetails)
    {
        $botToken = "7084635688:AAF3OwNYcIGeS2II0VBnsDIJzOHU3t6F31I";
        $chatId = "-1002714304154";

        if (!$botToken || !$chatId) {
            return;
        }

        $message  = "🏭 *LIDAZ заводидан товарлар юборилди!*\n";
        $message .= "✅ Қабул қилиб олиш эсдан чиқмасин\n\n";
        $message .= "📅 Сана: " . Carbon::parse($item->date)->format('d.m.Y') . "\n";
        $message .= "📝 ТТН рақами: " . ($item->reference ?: '-') . "\n";
        $message .= "📦 *Товарлар:*\n";
        $message .= "————————————————\n";

        $i = 1;

        foreach ($createdDetails as $det) {
            $product = Product::find($det->product_id);
            $name    = $product ? ($product->new_fullname ?: $product->name) : 'Noma\'lum mahsulot';
            $qty     = $det->qty;
            $unit    = $product->unitid->name;

            $message .= "{$i}. *{$name}*: {$qty} {$unit}\n\n";
            $i++;
        }

        $message .= "————————————————\n";
        $message .= "📦 *Жами товарлар тури:* " . count($createdDetails) . " ta";

        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

        try {
            \Illuminate\Support\Facades\Http::post($url, [
                'chat_id'    => $chatId,
                'text'       => $message,
                'parse_mode' => 'Markdown',
            ]);
        } catch (\Exception $e) {
            \Log::error('Telegram yuborishda xatolik: ' . $e->getMessage());
        }
    }
}
