<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

class UpdateProductImagesFromApi extends Command
{
    /**
     * Buyruq nomi (terminalda ishlatish uchun).
     */
    protected $signature = 'sync:product-images';

    /**
     * Buyruq tavsifi.
     */
    protected $description = 'API orqali mahsulot rasmlarini yuklash va yangilash';

    /**
     * Asosiy rasm yuklash URL manzili (taxminiy).
     * Agar rasm boshqa papkada tursa, shu yerni o\'zgartiring.
     */
    protected $remoteBaseUrl = 'https://sklad.simmaautostar.uz/upload/product_image/';

    public function handle()
    {
        $this->info("Jarayon boshlandi...");

        // Rasm saqlanadigan papka yo'li
        $destinationPath = public_path('upload/product_image');

        // Agar papka yo'q bo'lsa, yaratamiz
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }

        // image = '0' yoki null bo'lgan va barcode bor mahsulotlarni olamiz
        // chunkById server xotirasini tejash uchun ishlatiladi
        Product::where(function($query) {
                $query->where('image', '0')
                      ->orWhereNull('image')
                      ->orWhere('image', '');
            })
            ->whereNotNull('barcode')
            ->where('barcode', '!=', '')
            ->chunkById(50, function ($products) use ($destinationPath) {
                
                foreach ($products as $product) {
                    $this->processProduct($product, $destinationPath);
                }
            });

        $this->info("Barcha jarayonlar yakunlandi!");
    }

    protected function processProduct($product, $destinationPath)
    {
        $barcode = $product->barcode;
        $apiUrl = "https://sklad.simmaautostar.uz/site_barcode/{$barcode}";

        try {
            // API ga so'rov yuborish
            $response = Http::timeout(10)->get($apiUrl);

            if ($response->successful()) {
                $data = $response->json();

                // API dan rasm nomi kelganligini tekshiramiz
                if (!empty($data['image']) && $data['image'] != '0') {
                    
                    $imageName = $data['image'];
                    $remoteImageUrl = $this->remoteBaseUrl . $imageName;
                    $localFilePath = $destinationPath . '/' . $imageName;

                    // Agar rasm allaqachon bizda bo'lsa, qayta yuklamaymiz (ixtiyoriy)
                    if (!File::exists($localFilePath)) {
                        $this->line("Rasm yuklanmoqda: {$imageName} (Barcode: {$barcode})");
                        
                        // Rasmni internetdan o'qib olish
                        $imageContent = @file_get_contents($remoteImageUrl);

                        if ($imageContent) {
                            // Rasmni papkaga yozish
                            File::put($localFilePath, $imageContent);
                            
                            // Bazani yangilash
                            $product->image = $imageName;
                            
                            // Agar API da image_2 va image_3 bo'lsa va kerak bo'lsa, ularni ham qo'shish mumkin:
                            if (!empty($data['image_2'])) {
                                $this->downloadAndSaveExtraImage($data['image_2'], $destinationPath);
                                $product->image_2 = $data['image_2'];
                            }
                            if (!empty($data['image_3'])) {
                                $this->downloadAndSaveExtraImage($data['image_3'], $destinationPath);
                                $product->image_3 = $data['image_3'];
                            }

                            $product->save();
                            $this->info("✅ Yangilandi: {$product->id}");
                        } else {
                            $this->error("❌ Rasm topilmadi yoki yuklab bo'lmadi: {$remoteImageUrl}");
                        }
                    } else {
                        // Rasm fayli bor, lekin bazada '0' bo'lsa, bazani yangilab qo'yamiz
                        $product->image = $imageName;
                        $product->save();
                        $this->comment("Fayl mavjud, baza yangilandi: {$product->id}");
                    }
                } else {
                    $this->warn("⚠️ API da rasm yo'q: ID {$product->id} (Barcode: {$barcode})");
                }
            } else {
                $this->error("API xatosi: {$response->status()} - Barcode: {$barcode}");
            }

        } catch (\Exception $e) {
            $this->error("Xatolik yuz berdi (ID: {$product->id}): " . $e->getMessage());
        }
    }

    // Qo'shimcha rasmlarni yuklab olish uchun yordamchi funksiya
    protected function downloadAndSaveExtraImage($imageName, $destinationPath)
    {
        $remoteImageUrl = $this->remoteBaseUrl . $imageName;
        $localFilePath = $destinationPath . '/' . $imageName;

        if (!File::exists($localFilePath)) {
            $content = @file_get_contents($remoteImageUrl);
            if ($content) {
                File::put($localFilePath, $content);
            }
        }
    }
}