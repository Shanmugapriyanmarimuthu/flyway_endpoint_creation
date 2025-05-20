<?php
set_time_limit(0);
function fetchJsonFromUrl($url) {
    $json = @file_get_contents($url);
    if ($json === false)
        return '';
    return json_decode($json, true);
}

function slugify($text) {
    // replace non letter or digits by -
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);

    // transliterate
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

    // remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);

    // trim
    $text = trim($text, '-');

    // remove duplicate -
    $text = preg_replace('~-+~', '-', $text);

    // lowercase
    $text = strtolower($text);

    if (empty($text)) {
        return 'n-a';
    }

    return $text;
}

function convertHttpToHttps($url) {
    return preg_replace('/^http:/i', 'https:', $url);
}

function logInvalidSections($url, $message) {
    $content = $message."::".$url."\n";
    file_put_contents(LOG_FILE, $content, FILE_APPEND);
}

function debugelton($id, $url, $message, $node) {
    if (DEBUGELTON) {
        echo $message."<br>";
        echo "URL::".$url."<br>";
        echo "ID::".$id."<br>";
        echo "NODE::<br>";
        print_r($node);
        echo "<br>";
    }
    logInvalidSections($url, $message);
}

function filterRubbish($input) {
    $output = str_replace("[…]", "...", $input);
    return $output;
}

function twoArraysToOne($one, $two) {
    foreach ($two as $value) {
        $one[] = $value;
    }
    $one = array_unique($one);
    return $one;
}

function getTwitter($idx, $nodes) {
    // booger out if there is twitter or not
    $max = 5;
    $i = 0;
    $current_id = $idx;
    $nodes_to_ignore[] = $current_id;
    while ($i < $max) {
        if (isset($nodes[$current_id]['attributes']) && isset($nodes[$current_id]['attributes']['HREF'])) {
            $twitter = convertOembed($nodes[$current_id]['attributes']['HREF'], 'twitter');
            return [
              'twitter' => $twitter,
              'nodes_to_ignore' => $nodes_to_ignore
            ];
        }
        $i = $i+1;
        $current_id = $current_id+1;
        $nodes_to_ignore[] = $current_id;
    }
    return false;
}
