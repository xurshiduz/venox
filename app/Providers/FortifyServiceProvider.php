<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Requests\LoginRequest;
use App\Models\User;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        
        
        Fortify::authenticateUsing(function (LoginRequest $request) {
            if($request->qr){
                
                $rulatkey = [
                    "А"=>"F","Б"=>"<","В"=>"D","Г"=>"U",
                    "Д"=>"L","Е"=>"T","Ж"=>":","З"=>"P","И"=>"B",
                    "Й"=>"Q","К"=>"R","Л"=>"K","М"=>"V","Н"=>"Y",
                    "О"=>"J","П"=>"G","Р"=>"H","С"=>"C","Т"=>"N",
                    "У"=>"E","Ф"=>"A","Х"=>"{","Ц"=>"W","Ч"=>"X",
                    "Ш"=>"I","Щ"=>"O","Ъ"=>"}","Ы"=>"S","Ь"=>"M",
                    "Э"=>'"',"Ю"=>">","Я"=>"Z","а"=>"f","б"=>",",
                    "в"=>"d","г"=>"u","д"=>"l","е"=>"t","ж"=>";",
                    "з"=>"p","и"=>"b","й"=>"q","к"=>"r","л"=>"k",
                    "м"=>"v","н"=>"y","о"=>"j","п"=>"g","р"=>"h",
                    "с"=>"c","т"=>"n","у"=>"e","ф"=>"a","х"=>"[",
                    "ц"=>"w","ч"=>"x","ш"=>"i","щ"=>"o","ъ"=>"]",
                    "ы"=>"s","ь"=>"m","э"=>"'","ю"=>".","я"=>"z"
                ];
                $runame = strtr($request->qr,$rulatkey);
        
                $user = User::where('code', $request->qr)->orWhere('code', $runame)->first();
                if ($user) {
                    return $user;
                }
            } else {
                $user = User::where('email', $request->identity)
                    ->orWhere('username', $request->identity)->first();
                
                if (
                    $user &&
                    \Hash::check($request->password, $user->password)
                ) {
                    return $user;
                }
            }
            
        });
        
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->email.$request->ip());
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
