<?php
require 'vendor/autoload.php';
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

include_once('./bitfinex.php');
include_once('./lib/macd.php');
include_once('./lib/rsi.php');
include_once('./security.php');

$phone = getPhone();
$virtual = getVirtual();

$BASE_URL = "https://min-api.cryptocompare.com/data/";

$DAY_HIST = "histoday";
$PRICE = "price";

$coin = "ETH";
$PERIOD = 100;

// Fetch Coin Information
$client = new Client(['base_uri' => $BASE_URL, 'timeout'  => 3.0,]);
$qry_str_day_hist = "?fsym=$coin&tsym=BTC&limit=$PERIOD&e=CCCAGG";
$response = $client->request('GET', $DAY_HIST . $qry_str_day_hist);
$content = json_decode($response->getBody(), true);

// ***** CALCUATING RSI ******
$rsi = calculate_rsi($content);
echo "RSI: " . $rsi . "\n";

// ***** CALCUATING MACD ******
$macd = calculate_macd($content, $EMA_PARAMS = [12, 26, 9]);
echo "MACD: " . $macd . "\n";

// Fetch Coin price
$qry_str_price = "?fsym=$coin&tsyms=USD";
$response = $client->request('GET', $PRICE . $qry_str_price);
$coin_price =  json_decode($response->getBody(), true)["USD"];
echo "COIN PRICE API: $API_URL_PRICE" . $coin_price . "\n";

// Buy if RSI < 30 AND MACD == 1
// Sell if RSI > 60 MACD == 0
if ($rsi < 30)
{
  $message = "RSI Indicator showing good signals to buy: " . $coin . " ($" . $price . ")" . "\n";
}
else if ($rsi > 60)
{
  $message = "RSI Indicator showing good signals to sell: " . $coin . " ($" . $price . ")" . "\n";
}
else
{
  $message = "RSI Indicator not showing any buy/sell signals: " . $coin . " ($" . $price . ")" . "\n";
}

if ($macd == 1)
{
  $message1 = "MACD Indicator showing good signals to buy: " . $coin . " ($" . $price . ")" . "\n";
}
else if ($macd == 0)
{
  $message1 = "MACD Indicator showing good signals to sell: " . $coin . " ($" . $price . ")" . "\n";
}
else
{
  $message1 = "MACD Indicator not showing any buy/sell signals: " . $coin . " ($" . $price . ")" . "\n";
}


  // SMS alert
  $api_key = getKey();
  $api_secret = getSecret();
  $virtual = getVirtual();

  // First Indicator Message
  $ch = curl_init();

  curl_setopt($ch, CURLOPT_URL,"https://rest.nexmo.com/sms/json");
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS,
              "api_key=$api_key&api_secret=$api_secret&to=1$phone&from=1$virtual&text=$message");

  // receive server response ...
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $server_output = curl_exec ($ch);

  sleep(1);

  curl_setopt($ch, CURLOPT_URL,"https://rest.nexmo.com/sms/json");
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS,
              "api_key=$api_key&api_secret=$api_secret&to=1$phone&from=1$virtual&text=$message1");

  // receive server response ...
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $server_output = curl_exec ($ch);

  curl_close ($ch);


?>
