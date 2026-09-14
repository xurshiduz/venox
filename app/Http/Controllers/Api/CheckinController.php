<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Checkin;
use App\Models\CheckinDetail;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CheckinController extends Controller
{
    public function apiCheckinSave(Request $request)
    {
        $request->validate(['source_code' => ['required', 'uuid']]);

        $response = Http::timeout(15)->retry(2, 500)->get(
            rtrim(config('services.lidaz_factory.url'), '/') . '/api/venox-ledger/transfer/' . $request->source_code
        );

        abort_unless($response->successful(), 422, 'LIDAZ hujjati tasdiqlanmadi.');
        $payload = $response->json();

        validator($payload, [
            'source_code' => ['required', 'uuid'],
            'source_id' => ['required', 'integer'],
            'date' => ['required', 'date'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.barcode' => ['required', 'string'],
            'details.*.qty' => ['required', 'numeric', 'gt:0'],
            'details.*.price' => ['required', 'numeric', 'min:0'],
        ])->validate();

        $missing = collect($payload['details'])->pluck('barcode')->filter()
            ->reject(fn ($barcode) => Product::where('barcode', $barcode)->exists())->values();
        abort_if($missing->isNotEmpty(), 422, 'VENOXda topilmagan shtrix-kodlar: ' . $missing->join(', '));

        $checkin = DB::transaction(function () use ($payload) {
            $existing = Checkin::where('source_system', 'lidaz')
                ->where('source_code', $payload['source_code'])->lockForUpdate()->first();
            if ($existing) {
                return $existing;
            }

            $item = Checkin::create([
                'date' => Carbon::parse($payload['date'])->format('Y-m-d'),
                'client_id' => 2,
                'warehouse_id' => 2,
                'reference' => 'LIDAZ #' . ($payload['reference'] ?: $payload['source_id']),
                'type_id' => 1,
                'user_id' => (int) config('services.lidaz_factory.user_id', 1),
                'status' => 0,
                'code' => (string) Str::uuid(),
                'source_system' => 'lidaz',
                'source_id' => $payload['source_id'],
                'source_code' => $payload['source_code'],
                'source_received_at' => now(),
            ]);

            foreach ($payload['details'] as $detail) {
                $product = Product::where('barcode', $detail['barcode'])->firstOrFail();
                CheckinDetail::create([
                    'checkin_id' => $item->id,
                    'product_id' => $product->id,
                    'warehouse_id' => 2,
                    'category_id' => $product->category_id,
                    'qty' => $detail['qty'],
                    'status' => 0,
                    'currency_type' => 2,
                    'currency_type_price' => 1,
                    'price' => $detail['price'],
                    'total_price' => $detail['price'] * $detail['qty'],
                    'code' => (string) Str::uuid(),
                    'barcode' => $this->generateUniqueDetailBarcode(),
                ]);
            }

            return $item;
        });

        return response()->json([
            'success' => true,
            'pending' => (int) $checkin->status === 0,
            'checkin_code' => $checkin->code,
        ]);
    }

    private function generateUniqueDetailBarcode(): string
    {
        do {
            $barcode = mt_rand(10, 99) . time() . mt_rand(100, 999);
        } while (CheckinDetail::where('barcode', $barcode)->exists());

        return $barcode;
    }
}
