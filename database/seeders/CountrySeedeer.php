<?php

namespace Database\Seeders;

use App\Jobs\CityCreationJob;
use App\Models\City;
use App\Models\Countries;
use App\Models\Currency;
use App\Models\State;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use SebastianBergmann\Environment\Console;
use Symfony\Component\Console\Output\ConsoleOutput;

class CountrySeedeer extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

       info("Starting country dump");

       $countryUrl = "https://countriesnow.space/api/v0.1/countries";
       $countryData = Http::withoutVerifying()->get($countryUrl)->collect()->toArray();
       foreach ($countryData['data'] as $datum){
           $flagUrl = "https://flagsapi.com/".$datum['iso2']."/shiny/64.png";
           Countries::create([
               'name'=>$datum['country'],
               "iso_code"=>$datum['iso2'],
               "iso3_code"=>$datum['iso3'],
               "flag_url" => $flagUrl,
               'status' => 0,
               'api_name' => $datum['country'],
               'region' => 'Europe',
           ]);
       }

       info("Starting currency dump");
       $currencyJsonPath = public_path('countries/currencies.json');
       $currencyData = json_decode(file_get_contents($currencyJsonPath), true);

       $currencyFinderPath= public_path('countries/currency2.json');
       $currencyFinderData = json_decode(file_get_contents($currencyFinderPath), true);

       info("Starting currency dump");
       foreach ($currencyFinderData as $finderDatum){
           $currencyCode = $finderDatum['currencyCode'];
           $currencyObject = isset($currencyData[$currencyCode]) ? $currencyData[$currencyCode] : null;
           if ($currencyObject!=null){
               $country = Countries::findByIso2($finderDatum['countryCode'])->first();
               info($finderDatum);
               if ($country){
                   $existingCurrency = Currency::where('code',$currencyCode)->first();
                   $currencyNew = null;
                   if ($existingCurrency == null){
                       $currencyNew = Currency::create([
                           "name"=>$currencyObject['name'],
                           "symbol"=>$currencyObject['symbol'],
                           "logo"=> $country->flag_url,
                           "active"=>false,
                           "code" => $currencyObject['code']
                       ]);
                   }
                   else{
                       $currencyNew = $existingCurrency;
                   }
                  // $country->currency_id = $currencyNew->id;
                   $country->region = $finderDatum['continentName'];
                   $country->save();
               }
           }
       }


       info("Starting phonecode dump");
       $phoneJsonPath = public_path('countries/phonecode.json');
       $phoneData = json_decode(file_get_contents($phoneJsonPath), true);
       foreach ($phoneData as  $phoneDatum){
           $country = Countries::findByIso2($phoneDatum['code'])->first();
           if ($country){
               $country->phone_code = $phoneDatum['dial_code'];
               $country->save();
           }
       }

       info("Starting states dump");
       $countries = Countries::all();
       foreach ($countries as $country){
           try {
               $url = "https://countriesnow.space/api/v0.1/countries/states/q?country=".$country->api_name;
               info($url);
               $data = Http::withoutVerifying()->get($url)->collect()->toArray();
               if (isset($data['data'])){
                   foreach ($data['data']['states'] as $datum){
                       State::create([
                           'country_id'=>$country->id,
                           'name'=>$datum['name'],
                           "code"=>$datum['state_code']
                       ]);
                   }
               }
           }
           catch (\Exception $exception){
               info($country->name);
               info($exception->getMessage());
               info($exception->getTraceAsString());
           }
       }

       $countries = Countries::where("status",true)->get();
       foreach ($countries as $country){
           foreach ($country->states as $state){
               try {
                   City::where('state_id',$state->id)->delete();
                   $this->createState($state);
               }
               catch (\Exception $exception){
                   info($state->name);
                   info($exception->getMessage());
                   info($exception->getTraceAsString());
               }
           }
       }


       info("Starting city dump");
       $states = State::all();
       foreach ($states as $state){
           try {
               $this->createState($state);
           }
           catch (\Exception $exception){
               info($state->name);
               info($exception->getMessage());
               info($exception->getTraceAsString());
           }
       }
      //CityCreationJob::dispatch();
       info("Completed country seeder");

    }

    private function createState($state){
        $output = new ConsoleOutput();
        $data = null;
        $ini = false;
        $cs = [];
        try {
            $url = "https://countriesnow.space/api/v0.1/countries/state/cities/q?country=".$state->country->api_name."&state=".$state->name;
            $c = Http::withoutVerifying()->get($url);
            if (!$c->successful()){
                throw new \Exception();
            }
            $cs = $c->collect()->toArray();
        }
        catch (\Exception $exception){
            info($state->name);
            info($exception->getMessage());
            $ini = true;
        }
        if ($ini){
            try {
                $sn = Str::replaceFirst("state",'',$state->name);
                $sn = Str::replaceFirst("State",'',$sn);
                $sn = trim($sn);
                $url = "https://countriesnow.space/api/v0.1/countries/state/cities/q?country=".$state->country->api_name."&state=".$sn;
                $cs = Http::withoutVerifying()->get($url)->collect()->toArray();
            }
            catch (\Exception $exception){

            }
        }


        //$url = "https://countriesnow.space/api/v0.1/countries/state/cities/q?country=".$state->country->api_name."&state=".$state->name;
        //$cs = Http::withoutVerifying()->get($url)->collect()->toArray();
        if (isset($cs['data'])){
            foreach ($cs['data'] as $item){
                City::create([
                    'state_id'=>$state->id,
                    'name'=>$item,
                ]);
            }
        }
    }
}
