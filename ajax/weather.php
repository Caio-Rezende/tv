<?php

//$weather = file_get_contents('http://www.webservicex.net/globalweather.asmx/GetWeather?CityName=Goiania&CountryName=Brazil');
//preg_match('~F\ \(([^\)]+)\)~', $weather, $matches);
//echo $matches[1];

//$weather = file_get_contents('http://api.openweathermap.org/data/2.5/find?q=Goiania&units=metric');
$weather = file_get_contents('http://api.openweathermap.org/data/2.5/weather?lat=-16.681166&lon=-49.260369&units=metric');
$weather = json_decode($weather, true);
echo $weather['main']['temp'];