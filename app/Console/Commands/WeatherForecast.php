<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Telegram\Bot\Laravel\Facades\Telegram;

class WeatherForecast extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'weather:forecast {location=L\'viv}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'weather:forecast {location: the name of the location}';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $locationName = $this->argument('location');
        $apiKey = env("HERE_API_KEY", "");
        $baseURL = "https://weather.ls.hereapi.com/weather/1.0/report.json";
        $params = array(
            'product' => 'forecast_7days',
            'apiKey' => $apiKey,
            'name' => $locationName,
            'metric' => true
        );
        $url = "${baseURL}?" . http_build_query($params);
        $response = Http::get($url);

        if ($response->successful()) {
            $jsonResponse = $response->json();
            $forecastLocation = $jsonResponse['forecasts']['forecastLocation'];
            $forecast = $jsonResponse['forecasts']['forecastLocation']['forecast'];
            $forecastText = "<b>" . $forecastLocation['country'] . ', ' . $forecastLocation['city'] . " weather forecast</b>\n\n";

            // forecast for today
            $currentWeekday = date('l');
            $todayForecast = array_filter($forecast, function ($array) use ($currentWeekday)  {
                if (isset($array['weekday'])) {
                    if ($array['weekday'] === $currentWeekday) {
                        return true;
                    }
                }
                return false;
            });

            foreach ($todayForecast as $key => $value) {
                $forecastText .= "\u{1f4c5} " . date('F j, ', strtotime($value['utcTime'])) .
                    $value['daySegment'] . ":\n" . $value['description'] .
                    "\n\u{1F321} Temperature - " . $value['temperature'] .
                    "°C\n\u{1F4A7} Humidity - " . $value['humidity'] .
                    "%\n\u{1F32C} Wind speed - " . $value['windSpeed'] . 'm/s, ' . $value['windDesc'] . "\n\n";
            }

            // $this->info($forecastText);

            Telegram::sendMessage([
                'chat_id' => env('TELEGRAM_CHANNEL_ID', ''),
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
                'text' => $forecastText
            ]);
        } else {
            if ($response->clientError()) {
                $this->error('Error performing request: ' . $response->getStatusCode());
            } elseif ($response->serverError()) {
                $this->error('Error from server: ' . $response->getStatusCode());
            } else {
                $this->error('Error! ' . $response->getStatusCode());
            }
        }
    }
}
