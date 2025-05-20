<?php

#require('../helpers.php');

function convertInstagram($url) {

    // url
    $url = trim($url);
    $url =  convertHttpToHttps($url);

    return "[#instagram: " . $url . "]";

}
