<?php

require('vendor/autoload.php');
require('config.php');
require('helpers.php');
require('processor.php');





// URL mit dem JSON-Objekt
#$url = 'https://www.gqmiddleeast.com/wp-json/wp/v2/pages'; // Ersetze dies durch die tatsächliche URL
#$taxonomies = fetchJsonFromUrl('https://www.gqmiddleeast.com/wp-json/wp/v2/taxonomies');
#$taxonomies = fetchJsonFromUrl('https://www.gqmiddleeast.com/wp-json/wp/v2/categories');

$url = API_URL_PRODUCTS;
if (isset($_GET['id']))
    $url = $url.$_GET['id'];

#echo "URL::".$url."<br>";

// JSON-Objekt von der URL holen
$jsonData = fetchJsonFromUrl($url);

$processor = new Processor();
$processor->data = $jsonData;

$result = $processor->processData();
#echo "<div>==============================================================================</div>";
echo json_encode($result, JSON_PRETTY_PRINT);
#echo "<div>==============================================================================</div>";
#print "<pre>";
#print_r($taxonomies);
#print_r($result);
#var_dump($result);
#echo "<div>==============================================================================</div>";
#print_r($jsonData);
#print "</pre>";


// HTML in dem JSON in Markdown umwandeln
#$convertedJson = convertJsonHtmlToMarkdown($jsonData);

// JSON zurückgeben
#header('Content-Type: application/json');
#echo json_encode($convertedJson, JSON_PRETTY_PRINT);

?>
