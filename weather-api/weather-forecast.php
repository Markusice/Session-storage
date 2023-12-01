<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

require_once '../utils.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

function getWeatherAPIKey(): string
{
    return $_ENV['OPEN_WEATHER_MAP_KEY'];
}

function getGoogleAPIKey(): string
{
    return $_ENV['GOOGLE_MAPS_KEY'];
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
    $geocodingAPIUrl = "https://api.openweathermap.org/geo/1.0/direct?q=$cityName&appid=" . getWeatherAPIKey();
    return getResponseFromURL($geocodingAPIUrl) ?: false;
}

function requestCurrentWeatherDataByLatLon(float $lat, float $lon): array
{
    $currentWeatherAPI = "https://api.openweathermap.org/data/2.5/weather?lat=$lat&lon=$lon&appid=" . getWeatherAPIKey() . '&units=metric';
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

    <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>

    <link rel="stylesheet" href="../dist/output.css">
</head>
<body class="dark:bg-[#121212] dark:text-white">
<div class="p-6 grid auto-cols-[minmax(250px,_1fr)] xl:grid-cols-[max-content,_minmax(auto,_70rem)] gap-5">
    <form method="post" action="">
        <div class="form-items grid gap-y-3 text-lg bg-indigo-100 rounded-lg w-max p-4 text-indigo-950">
            <div class="grid gap-y-2">
                <label for="city-name">City name:</label>
                <input type="text" name="cityName" id="city-name" required
                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-60 p-2.5">
            </div>
            <button type="submit"
                    class="rounded-xl w-28 tracking-wide bg-yellow-600 py-3 flex justify-center text-neutral-50">
                Forecast
            </button>
        </div>
    </form>

    <?php if (isset($data)): ?>
        <div class="xl:row-start-2 max-w-xl text-lg font-medium grid gap-y-1 bg-indigo-100 p-4 rounded-lg text-indigo-950">
            <h2 class="text-3xl">Forecast for <?= $data['name']; ?>
                - <?php if (isset($formattedTime)): ?><?= $formattedTime ?><?php endif; ?>
            </h2>

            <div class="flex items-center gap-x-2">
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

    <?php $canInitMap = isset($data) && isset($lat) && isset($lon) ?>
    <?php if ($canInitMap): ?>
        <div id="map" class="xl:row-start-2 h-[30rem] w-full rounded-md">
        </div>
    <?php endif; ?>
</div>
<script>
    (g => {
        let h, a, k, p = "The Google Maps JavaScript API", c = "google", l = "importLibrary", q = "__ib__",
            m = document, b = window;
        b = b[c] || (b[c] = {});
        const d = b.maps || (b.maps = {}), r = new Set, e = new URLSearchParams,
            u = () => h || (h = new Promise(async (f, n) => {
                await (a = m.createElement("script"));
                e.set("libraries", [...r] + "");
                for (k in g) e.set(k.replace(/[A-Z]/g, t => "_" + t[0].toLowerCase()), g[k]);
                e.set("callback", c + ".maps." + q);
                a.src = `https://maps.${c}apis.com/maps/api/js?` + e;
                d[q] = f;
                a.onerror = () => h = n(Error(p + " could not load."));
                a.nonce = m.querySelector("script[nonce]")?.nonce || "";
                m.head.append(a)
            }));
        d[l] ? console.warn(p + " only loads once. Ignoring:", g) : d[l] = (f, ...n) => r.add(f) && u().then(() => d[l](f, ...n))
    })({
        key: "<?= getGoogleAPIKey() ?>",
        v: "weekly",
    });

    let map;

    async function initMap() {
        const position = {
            lat: <?php if (isset($lat)): ?><?= $lat ?><?php else: ?><?= 0 ?><?php endif;?>,
            lng: <?php if (isset($lon)): ?><?= $lon ?><?php else: ?><?= 0 ?><?php endif;?>,
        };
        const {Map} = await google.maps.importLibrary("maps");
        const {AdvancedMarkerElement} = await google.maps.importLibrary("marker");

        map = new Map(document.getElementById("map"), {
            zoom: 6,
            center: position,
            mapId: "DEMO_MAP_ID",
        });

        const marker = new AdvancedMarkerElement({
            map: map,
            position: position,
            title: "<?php if (isset($data['name'])): ?><?= $data['name'] ?><?php endif;?>",
        });
    }

    const canInitMap = <?= $canInitMap ? 'true' : 'false' ?>;
    if (canInitMap) {
        initMap();
    }
</script>
</body>
</html>
