<?php

const DEBUGELTON = false;
const LANG = 'en-IN';
const CONTENT_TYPE = 'article';
$baseUrl = '';
// const VOGUE_BASE_URL = 'https://dev1.vogue.in/';
const VOGUE_BASE_URL = 'https://creator.vogue.in/';
// const GQ_BASE_URL = 'https://dev1.gqindia.com/';
const GQ_BASE_URL = 'https://www.gqindia.com/';


if(isset($_GET['brand']) && $_GET['brand'] == 'vogue') {
    $baseUrl = VOGUE_BASE_URL;
    define('BRAND', 'vin');
}elseif ( isset($_GET['brand']) && $_GET['brand'] == 'gq') {
    $baseUrl = GQ_BASE_URL;
    define('BRAND', 'gqin');
}else{
    $baseUrl = VOGUE_BASE_URL;
    define('BRAND', 'vin');
}
define('BASE_URL', $baseUrl);
const APP_URL = 'http://localhost/WordpressToFlyway-main/';
const LOG_FILE = 'broken-sections.log';
const LIST_FILE = 'list-all.txt';

#const API_URL_PAGES = BASE_URL.'wp-json/wp/v2/pages/';
// const API_URL_PAGES = BASE_URL.'wp-json/wp/v2/pages/?per_page=100';
const API_URL_PAGES = BASE_URL.'wp-json/wp/v2/pages/';
const API_URL_POSTS = BASE_URL.'wp-json/wp/v2/posts/';
const API_URL_PRODUCTS = BASE_URL.'wp-json/wp/v2/product/';
const API_URL_PRODUCT_COLLECTION = BASE_URL.'wp-json/wp/v2/product_collection/';
#const API_URL_POSTS = BASE_URL.'wp-json/wp/v2/posts/92516';
#const API_URL_POSTS = BASE_URL.'wp-json/wp/v2/posts/?per_page=50&page=30';
const API_URL_AUTHOR = BASE_URL.'wp-json/wp/v2/users/';
const API_URL_CATEGORIES = BASE_URL.'wp-json/wp/v2/categories/';
const API_URL_TAGS = BASE_URL.'wp-json/wp/v2/tags/';
const API_URL_MEDIA = BASE_URL.'wp-json/wp/v2/media/';
