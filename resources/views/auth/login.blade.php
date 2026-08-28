<style>
    .udisp {
        display: flex;
        padding-bottom: 10px;
    }
    
    .udisp a {
        display: flex;
        align-items: center;
    }
    
    .udisp li {
        width: 50%;
        justify-content: center;
        display: flex;
    }
    .udisp a img {
        padding-right: 10px;
        width: 46px;
    }
</style>
<x-guest-layout>
    <x-jet-authentication-card>
        <x-slot name="logo">
            <img width="350px" src="/venox_logo_dark.png" srcset="/venox_logo_dark.png" alt="">
        </x-slot>

        <x-jet-validation-errors class="mb-4" />

        @if (session('status'))
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ session('status') }}
            </div>
        @endif
        
        <div style="display: none">
            <ul class="udisp">
                @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                <li>
                    <a href="{{ LaravelLocalization::getLocalizedURL($localeCode) }}">
                        <img src="/backend/images/flags/{{ $properties['native'] }}.png">
                        <span>@if(LaravelLocalization::getCurrentLocaleNative() == $properties['native']) <b>{{ $properties['name'] }}</b> @else {{ $properties['name'] }} @endif</span>
                    </a>
                </li>
                @endforeach
            </ul>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div style="display: none">
                <x-jet-input id="qr" class="block mt-1 w-full" type="text" name="qr" :value="old('qr')" autofocus placeholder="QR Code" autocomplete="off"/>
            </div>
            <br>

            <div>
                <x-jet-label for="email" value="{{ __('backend.auth.login') }}" />
                <x-jet-input id="email" class="block mt-1 w-full" type="text" name="identity" :value="old('identity')" />
                @include('layouts.message.error')
            </div>

            <div class="mt-4">
                <x-jet-label for="password" value="{{ __('backend.auth.password') }}" />
                <x-jet-input id="password" class="block mt-1 w-full" type="password" name="password" autocomplete="current-password" />
            </div>

            <div class="block mt-4">
                <label for="remember_me" class="flex items-center">
                    <x-jet-checkbox id="remember_me" name="remember" />
                    <span class="ml-2 text-sm text-gray-600">{{ __('backend.auth.remember') }}</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                @if (Route::has('password.request'))
                    <!--<a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>-->
                @endif

                <x-jet-button class="items-center " style="display: block; width: 100%">
                    {{ __('backend.auth.loginin') }}
                </x-jet-button>
            </div>
        </form>
    </x-jet-authentication-card>
</x-guest-layout>
