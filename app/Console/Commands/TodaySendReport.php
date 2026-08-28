<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use DB;
use GuzzleHttp\Client as GClient;
use App\Models\{
    Checkout,
    CheckoutDetail,
    Product,
    Unit,
    CashReceipt,
    WarehouseStock
};

class TodaySendReport extends Command
{
    protected $signature = 'report:today-send';
    protected $description = 'Send today sales report to Telegram';

    public function handle()
    {
        $client = new GClient([
            "base_uri" => "https://api.telegram.org",
        ]);

        $resultqarzyuq = "";
        $resultqarzbor = "";
        $result_pay = "";

        $bugungiSotilganMahsulotlarQarzsiz = Checkout::where('checkout_tip_id', 1)
            ->whereDate('date', today())
            ->where('total_price_debt', 0)
            ->join('checkout_details', 'checkouts.id', '=', 'checkout_details.checkout_id')
            ->select('checkout_details.product_id', DB::raw('SUM(checkout_details.qty) as total_qty'))
            ->groupBy('checkout_details.product_id')
            ->get();

        foreach ($bugungiSotilganMahsulotlarQarzsiz as $mahsulot) {
            $product = Product::find($mahsulot->product_id);
            $unit = Unit::find($product->unit_id);
            $resultqarzyuq .= $product->name . ' - ' . $mahsulot->total_qty . ' ' . $unit->name . "\n";
        }

        $bugungiSotilganMahsulotlarQarzBilan = Checkout::where('checkout_tip_id', 1)
            ->whereDate('date', today())
            ->where('total_price_debt', '>', 0)
            ->join('checkout_details', 'checkouts.id', '=', 'checkout_details.checkout_id')
            ->select('checkout_details.product_id', DB::raw('SUM(checkout_details.qty) as total_qty'))
            ->groupBy('checkout_details.product_id')
            ->get();

        foreach ($bugungiSotilganMahsulotlarQarzBilan as $mahsulot) {
            $product = Product::find($mahsulot->product_id);
            $unit = Unit::find($product->unit_id);
            $resultqarzbor .= $product->name . ' - ' . $mahsulot->total_qty . ' ' . $unit->name . "\n";
        }

        foreach (CashReceipt::where('status', 1)
            ->whereDate('date', Carbon::today())
            ->get()
            ->groupBy('cash_receipt_type') as $pay) {

            $result_pay .= $pay->first()->tname->name . ': ' .
                number_format($pay->sum('price'), 0, '.', ' ') . "\n";
        }

        $summa = number_format(
            CashReceipt::where('status', 1)->whereDate('date', today())->sum('price'),
            0, '.', ' '
        );

        $summa_dolg = number_format(
            Checkout::where('checkout_tip_id', 1)
                ->whereDate('date', today())
                ->sum('total_price_debt'),
            0, '.', ' '
        );

        $date = now()->format('Y-m-d H:i:s');
        $ddate = now()->format('d.m.Y');

        $bot_token = "7335500759:AAFQCsaM-8jLefkgmNEW3phJxmn_gQIBSQw";
        $chat_id = "-1003627640983";

        $message = "<b><u>🛒 Продажа на $ddate</u></b>\n"
            . "<b>💵 Продажа:</b> $summa\n"
            . "<b>💵 Продажа (долг):</b> $summa_dolg\n\n"
            . "<b>Проданные товары:</b>\n$resultqarzyuq\n"
            . "<b>Товары в долг:</b>\n$resultqarzbor\n\n"
            . "<b>⏱ Дата:</b> $date";

        $client->get("/bot$bot_token/sendMessage", [
            "query" => [
                "chat_id" => $chat_id,
                "text" => $message,
                "parse_mode" => "html"
            ]
        ]);

        $this->info('Report sent successfully');
    }
}
