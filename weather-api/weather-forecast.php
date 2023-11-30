<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

require_once '../utils.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

function getAPIKey(): string
{
    return $_ENV['API_KEY'];
}

function getResponseFromURL(string $URL): mixed
{
    $ch = curl_init();

    $opts = [
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $URL,
        CURLOPT_FOLLOWLOCATION => 1,
        CURLOPT_VERBOSE => 0,
        CURLOPT_SSL_VERIFYPEER => false,
    ];
    curl_setopt_array($ch, $opts);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true) ?: false;
}

function requestLocationDataByCityName(string $cityName): array | false
{
    $cityName = urlencode($cityName);
    $geocodingAPIUrl = "https://api.openweathermap.org/geo/1.0/direct?q=$cityName&appid=" . getAPIKey();
    return getResponseFromURL($geocodingAPIUrl) ?: false;
}

function requestCurrentWeatherDataByLatLon(float $lat, float $lon): array
{
    $currentWeatherAPI = "https://api.openweathermap.org/data/2.5/weather?lat=$lat&lon=$lon&appid=" . getAPIKey() . '&units=metric';
    return getResponseFromURL($currentWeatherAPI);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $geocodingData = requestLocationDataByCityName($_POST['cityName']);

    if (!$geocodingData) {
        redirect('weather-forecast.php');
    }
//    var_dump($geocodingData);
    $lat = $geocodingData[0]['lat'];
    $lon = $geocodingData[0]['lon'];
    $data = requestCurrentWeatherDataByLatLon($lat, $lon);
    $data['name'] = $geocodingData[0]['name'];
//    var_dump($data);

    $timeWhenCalculated = $data['dt'];
    $timeInTimeZone = $timeWhenCalculated + $data['timezone'];
    $dateTime = new DateTimeImmutable("@$timeInTimeZone");
    $formattedTime = $dateTime->format('H:i:s');
}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Weather Forecast</title>

    <link rel="stylesheet" href="../dist/output.css">
</head>
<body class="dark:bg-[#121212] dark:text-white">
<div class="p-4 grid gap-y-5">
    <form method="post" action="">
        <div class="form-items grid gap-y-3 text-lg">
            <div class="grid gap-y-2">
                <label for="city-name">City name:</label>
                <input type="text" name="cityName" id="city-name" required
                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-60 p-2.5">
            </div>
            <button type="submit" class="rounded-xl w-28 bg-yellow-600 py-3 flex justify-center text-neutral-50">
                Forecast
            </button>
        </div>
    </form>

    <?php if (isset($data)): ?>
        <div class="text-lg font-medium grid gap-y-1 bg-indigo-100 p-4 rounded-lg w-max text-indigo-950">
            <h2 class="text-3xl">Forecast for <?= $data['name']; ?>
                - <?php if (isset($formattedTime)): ?><?= $formattedTime ?><?php endif; ?>
            </h2>

            <div class="flex items-center gap-x-1">
                <img src="https://openweathermap.org/img/w/<?= $data['weather'][0]['icon']; ?>.png" class="weather-icon"
                     alt="Weather icon"/>
                <p><?= ucwords($data['weather'][0]['description']); ?></p>
            </div>

            <div>
                <p>Current temp: <?= $data['main']['temp'] ?>°C</p>
                <p>Feels like: <?= $data['main']['feels_like'] ?>°C</p>
                <p>Max temp: <?= $data['main']['temp_max']; ?>°C</p>
                <p>Min temp: <?= $data['main']['temp_min']; ?>°C</p>
            </div>

            <div>
                <p>Humidity: <?= $data['main']['humidity']; ?>%</p>
                <p>Pressure: <?= $data['main']['pressure']; ?> hPa</p>
                <p>Wind: <?= $data['wind']['speed']; ?> km/h</p>
            </div>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
