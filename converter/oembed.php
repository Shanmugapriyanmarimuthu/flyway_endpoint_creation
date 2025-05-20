<?php

function convertOembed($url, $type) {

    $typemap = [
        // OEmbed    => ContentApi 2.1
        "facebook"   => "facebook-video",
        "instagram"  => "instagram",
        "twitter"    => "twitter",
        "youtube"    => "video",
        "vimeo"      => "video",
        "tiktok"      => "video",
        "tiktok-embed"      => "video",
        "soundcloud"  => "soundcloud-track"
    ];

#        echo "OembedSection<br>";
#        dump($input);

    $initial_type = $type;
    $type = isset($typemap[$type]) ? $typemap[$type] : $type;
    // url
    $url = trim($url);
    $url = convertHttpToHttps($url);
    // youtube: /watch -> /embed
    if ($initial_type == 'youtube') {
        $parts = explode('/', $url);
        $id = array_pop($parts);
        $url = "https://www.youtube.com/embed/" . $id;

    }

    return "[#" . $type . ": " . $url . "]";

}
