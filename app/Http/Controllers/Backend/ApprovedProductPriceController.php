<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ApprovedProductPrice;
use App\Models\ApprovedProductPriceAudit;
use App\Services\ApprovedProductPriceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApprovedProductPriceController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $prices = ApprovedProductPrice::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('code', 'like', '%' . $search . '%')
                        ->orWhere('match_tokens', 'like', '%' . $search . '%');
                });
            })
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $audits = ApprovedProductPriceAudit::query()
            ->with(['price', 'user'])
            ->when($search !== '', fn ($query) => $query->where('price_name', 'like', '%' . $search . '%'))
            ->latest('id')
            ->paginate(30, ['*'], 'history_page')
            ->appends($request->query());

        return view('backend.approved_product_prices.index', [
            'prices' => $prices,
            'audits' => $audits,
            'search' => $search,
            'usdRate' => (new ApprovedProductPriceService())->usdRate(),
        ]);
    }

    public function update(Request $request, ApprovedProductPrice $approvedPrice)
    {
        $validated = $request->validate([
            'sale_price_uzs' => ['nullable', 'numeric', 'min:0'],
            'factory_price_uzs' => ['nullable', 'numeric', 'min:0'],
        ]);

        $salePrice = $this->nullablePrice($validated['sale_price_uzs'] ?? null);
        $factoryPrice = $this->nullablePrice($validated['factory_price_uzs'] ?? null);
        if ($salePrice === null && $factoryPrice === null) {
            return back()->withErrors(['price' => 'Sotuv yoki zavod narxidan kamida bittasi kiritilishi kerak.']);
        }

        $oldSalePrice = $approvedPrice->sale_price_uzs !== null ? (float) $approvedPrice->sale_price_uzs : null;
        $oldFactoryPrice = $approvedPrice->factory_price_uzs !== null ? (float) $approvedPrice->factory_price_uzs : null;
        if ($this->samePrice($oldSalePrice, $salePrice) && $this->samePrice($oldFactoryPrice, $factoryPrice)) {
            return back()->with('success', 'Narxlarda o‘zgarish yo‘q');
        }

        DB::transaction(function () use (
            $request,
            $approvedPrice,
            $oldSalePrice,
            $oldFactoryPrice,
            $salePrice,
            $factoryPrice
        ) {
            $approvedPrice->update([
                'sale_price_uzs' => $salePrice,
                'factory_price_uzs' => $factoryPrice,
            ]);

            ApprovedProductPriceAudit::create([
                'approved_product_price_id' => $approvedPrice->id,
                'user_id' => Auth::id(),
                'user_name' => optional(Auth::user())->name,
                'price_name' => $approvedPrice->name,
                'old_sale_price_uzs' => $oldSalePrice,
                'new_sale_price_uzs' => $salePrice,
                'old_factory_price_uzs' => $oldFactoryPrice,
                'new_factory_price_uzs' => $factoryPrice,
                'ip_address' => $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            ]);
        });

        return back()->with('success', $approvedPrice->name . ' narxlari yangilandi');
    }

    private function nullablePrice($value): ?float
    {
        return $value === null || $value === '' ? null : round((float) $value, 2);
    }

    private function samePrice(?float $oldPrice, ?float $newPrice): bool
    {
        if ($oldPrice === null || $newPrice === null) {
            return $oldPrice === $newPrice;
        }

        return abs($oldPrice - $newPrice) < 0.005;
    }
}
