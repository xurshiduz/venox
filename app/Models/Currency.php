<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
  
class Currency extends Model
{
    use HasFactory; 
    protected $guarded = [];

    /**
     * Tizim bo'yicha yagona amaldagi USD -> UZS kursi.
     */
    public static function usdRate(): float
    {
        $rate = (float) static::where('type_id', 1)->latest('id')->value('price');

        return $rate > 0 ? $rate : 1;
    }

    /**
     * Berilgan summani asosiy hisobot valyutasi — UZSga o'tkazadi.
     * currency_type: 1 = USD, 2 = UZS.
     * Tarixiy hujjat uchun $documentRate berilsa, aynan o'sha kurs saqlanadi.
     */
    public static function toUzs(float $amount, ?int $currencyType, ?float $documentRate = null): float
    {
        if ($currencyType !== 1) {
            return $amount;
        }

        $rate = $documentRate && $documentRate > 1
            ? $documentRate
            : static::usdRate();

        return $amount * $rate;
    }

    /**
     * Hujjat qatori va sarlavhasida valyuta bir-biriga zid bo'lsa, sarlavha ustun.
     * Sotuvda tanlangan valyuta/kurs checkout sarlavhasida tarixiy holatda saqlanadi.
     */
    public static function documentAmountToUzs(
        float $amount,
        ?int $headerCurrencyType,
        ?float $headerRate,
        ?int $detailCurrencyType = null,
        ?float $detailRate = null
    ): float {
        $currencyType = $headerCurrencyType ?: $detailCurrencyType;
        $rate = ($headerRate && $headerRate > 1) ? $headerRate : $detailRate;

        return static::toUzs($amount, $currencyType, $rate);
    }

    public static function markupPercent(float $costUzs, float $saleUzs): ?float
    {
        if ($costUzs <= 0 || $saleUzs <= 0) {
            return null;
        }

        return (($saleUzs - $costUzs) / $costUzs) * 100;
    }

    public function products()
    {
        return $this->hasMany('App\Models\Product', 'category_id');
    } 
    
    public function clientid()
    {
        return $this->belongsTo('App\Models\Client', 'client_id');
    }
    
    public function cname()
    {
        return $this->belongsTo('App\Models\CurrencyType', 'type_id');
    }
    
    public function userid()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
    
}
