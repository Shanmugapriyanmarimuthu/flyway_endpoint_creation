<?php

use League\HTMLToMarkdown\HtmlConverter;

require('converter/instagram.php');
require('converter/oembed.php');


class Processor {

    public $data;
    public $record;
    public $converter;
    public $current_url;
    public $current_id;
    public $img_arr = [];
    public $img_cnt = 0;
    public $map_contenttype = [
        'page' => 'article',
        'post' => 'article',
        'product' => 'product',
        'product_collection' => 'article',
    ];

    public $horoscope_images = [
        1 => ["sign" => "Aries", "image" => "https://archiviodev.vogue.it/horoscope/image/aries_image.png"],
        2 => ["sign" => "Taurus", "image" => "https://archiviodev.vogue.it/horoscope/image/taurus_image.png"],
        3 => ["sign" => "Gemini", "image" => "https://archiviodev.vogue.it/horoscope/image/gemini_image.png"],
        4 => ["sign" => "Cancer", "image" => "https://archiviodev.vogue.it/horoscope/image/cancer_image.png"],
        5 => ["sign" => "Leo", "image" => "https://archiviodev.vogue.it/horoscope/image/leo_image.png"],
        6 => ["sign" => "Virgo", "image" => "https://archiviodev.vogue.it/horoscope/image/virgo_image.png"],
        7 => ["sign" => "Libra", "image" => "https://archiviodev.vogue.it/horoscope/image/libra_image.png"],
        8 => ["sign" => "Scorpio", "image" => "https://archiviodev.vogue.it/horoscope/image/scaripo_image.png"],
        9 => ["sign" => "Sagittarius", "image" => "https://archiviodev.vogue.it/horoscope/image/sagittarius_image.png"],
        10 => ["sign" => "Capricorn", "image" => "https://archiviodev.vogue.it/horoscope/image/capricorn_image.png"],
        11 => ["sign" => "Aquarius", "image" => "https://archiviodev.vogue.it/horoscope/image/aquarius_image.png"],
        12 => ["sign" => "Pisces", "image" => "https://archiviodev.vogue.it/horoscope/image/pisces_image.png"],
        13 => ["sign" => "Daily", "image" => "https://archiviodev.vogue.it/horoscope/image/daily_horoscope_image.png"]
    ];

    public $collection_images = [
        1 => ["sign" => "Aries", "image" => "https://archiviodev.vogue.it/horoscope/image/aries_image.png"],
        2 => ["sign" => "Taurus", "image" => "https://archiviodev.vogue.it/horoscope/image/taurus_image.png"],
        3 => ["sign" => "Gemini", "image" => "https://archiviodev.vogue.it/horoscope/image/gemini_image.png"],
        4 => ["sign" => "Cancer", "image" => "https://archiviodev.vogue.it/horoscope/image/cancer_image.png"],
        5 => ["sign" => "Leo", "image" => "https://archiviodev.vogue.it/horoscope/image/leo_image.png"],
        6 => ["sign" => "Virgo", "image" => "https://archiviodev.vogue.it/horoscope/image/virgo_image.png"],
        7 => ["sign" => "Libra", "image" => "https://archiviodev.vogue.it/horoscope/image/libra_image.png"],
        8 => ["sign" => "Scorpio", "image" => "https://archiviodev.vogue.it/horoscope/image/scaripo_image.png"],
        9 => ["sign" => "Sagittarius", "image" => "https://archiviodev.vogue.it/horoscope/image/sagittarius_image.png"],
        10 => ["sign" => "Capricorn", "image" => "https://archiviodev.vogue.it/horoscope/image/capricorn_image.png"],
        11 => ["sign" => "Aquarius", "image" => "https://archiviodev.vogue.it/horoscope/image/aquarius_image.png"],
        12 => ["sign" => "Pisces", "image" => "https://archiviodev.vogue.it/horoscope/image/pisces_image.png"]
    ];


    public $horoscopeOrder = [
        "Aries", "Taurus", "Gemini", "Cancer", "Leo", "Virgo", 
        "Libra", "Scorpio", "Sagittarius", "Capricorn", "Aquarius", "Pisces"
    ];

    public function processData() {
        $this->converter = new HtmlConverter();
        $result = [];
        if (is_array($this->data)) {
            // if we have a single record return it as an object
            if (isset($this->data['id'])) {
                $this->record = $this->data;
                return $this->getResult();
            }
            // if we have an array of records return an array with results
            else {
                foreach ($this->data as $id => $record) {
                    $this->record = $record;
                    $result[$id] = $this->getResult();
                }
            }
        }
        return $result;
    }

    protected function getResult() {

        $this->current_url = $this->getSelf();
        $this->current_id = $this->getId();

        $result = [
            'type' => $this->getType(),
            'id' => $this->getId(),
            'brand' => $this->getBrand(),
            'lang' => $this->getLang(),
            'hed' => $this->getHed(),
            'body' => $this->getBody(),
            'inline' => $this->getInline(),
            'dek' => $this->getDek(),
            'seoTitle' => $this->getSeoTitle(),
            'seoDescription' => $this->getSeoDescription(),
            'tags' => $this->getTags(),
            'rubric' => $this->getRubric(),
            'ledeCaption' => $this->getLedeCaption(),
            'socialTitle' => $this->getSocialTitle(),
            'socialDescription' => $this->getSocialDescription(),
            'promoHed' => $this->getPromoHed(),
            'self' => $this->getSelf(),
            'photosTout' => $this->getPhotosTout(),// site-image
            'photosLede' => $this->getPhotosLede(),// story-image
            'photosSocial' => $this->getPhotosSocial(),
            'contributorsAuthor' => $this->getContributorsAuthor(),
            'categories' => $this->getCategories(),
            'publishHistory' => $this->getPublishHistory()
        ];
#        return '';
        return $result;

    }

    protected function getType() {
        return 'article';
    }
    protected function getId() {
        return (string) $this->record['id'];
    }
    protected function getBrand() {
        return BRAND;
    }
    protected function getLang() {
        return LANG;
    }
    protected function getHed() {
        $content = '<p>'.$this->record['post_title'].'</p>';
        return $this->convert($content);
    }
    protected function getDek() {
        if(isset($this->record['excerpt'])){
            
                $content = '<p>'.$this->record['excerpt'].'</p>';
                return $this->convert($content);
        } 
        
    }
    protected function getSeoTitle() {
        if(!empty($this->record['_yoast_wpseo_title'])){
            $content = '<p>'.$this->record['_yoast_wpseo_title'].'</p>';
            return $this->convert($content);
        }else {
            return $this->getHed();
        }        
    }
    protected function getSeoDescription() {
        if(!empty($this->record['_yoast_wpseo_metadesc'])){
            $content = '<p>'.$this->record['_yoast_wpseo_metadesc'].'</p>';
            return $this->convert($content);
        }else {
            return $this->getDek();
        }
    }
    // as this is only for private tags we return an empty array
    protected function getTags() {
        // $tagName = date('dMy', strtotime($this->record['modified']));
        return [];
    }
    protected function getRubric() {
        return (string) '';
    }
    protected function getLedeCaption() {
        // $url = API_URL_MEDIA . $this->record['featured_media']['id'];
        // $lede = fetchJsonFromUrl($url);
        $lede = $this->getPhotosLede();
        return (isset($lede[0]['caption'])) ? $this->converter->convert($lede[0]['caption']) : '';
    }
    protected function getSocialTitle() {
        return $this->getSeoTitle();
    }
    protected function getSocialDescription() {
        return $this->getSeoDescription();
    }
    protected function getPromoHed() {
        return $this->getHed();
    }
    protected function getSelf() {
        
        return rtrim($this->record['permalink'], '/');
    }
    // also main images
    protected function getPhotosTout() {
        return $this->getPhotosLede();
    }
    // main image
    protected function getPhotosLede() {

        $lede = $this->getImage($this->record['featured_media']);
        return [$lede];
    }
    // social image
    protected function getPhotosSocial() {
        return $this->getPhotosLede();
    }
    protected function getContributorsAuthor() {
        return [];
        // $url = API_URL_AUTHOR . $this->record['author'];
        // $author = fetchJsonFromUrl($url);
        // if (!isset($author['name']))
        //     return [];

        // $author = [
        //   'type' => 'contributor',
        //   'id' => (string) $author['id'],
        //   'name' => $author['name'],
        //   'tags' => [],
        //   'uri' => '/author/' . $author['id']
        // ];
        // return [$author];
    }
    protected function getInline() {
        $images = [];

        if(count($this->img_arr) > 0 ){
            foreach ($this->img_arr as $key => $value) {
                $final_url = $value['URL'];
                $count = substr_count($value['URL'], "https:");
                if($count > 1){
                    preg_match('/^(\S+)/', $value['URL'], $matches);
                    $final_url = $matches[1];
                }
                if(strpos($final_url, 'author.vogue.in')){
                    $final_url =  str_replace('author.vogue.in' , 'media.vogue.in', $final_url);
                 }
                 if(strpos($final_url, 'www.vogue.in')){
                    $final_url =  str_replace('www.vogue.in' , 'media.vogue.in', $final_url);
                 }
                $id_name = $this->current_id.'-'.$key;
                $inline_ori_images = $this->getInlineOriImage(basename($final_url), $this->collection_images);

                if(!empty($inline_ori_images)){
                    $images[] = [
                        "type" => "photo",
                        "id" => (string) $inline_ori_images['id'],
                        "credit"=> "",
                        "filename" =>  basename(parse_url($inline_ori_images['url'], PHP_URL_PATH)),
                        "url" => $inline_ori_images['url'],
                        "tags" => [],
                        "caption" => isset($value['ALT']) ? $value['ALT'] : '',
                        "title" => pathinfo($inline_ori_images['url'], PATHINFO_FILENAME),
                        "inlineHref" => "/photos/$id_name"
                    ];

                }else{
                    $images[] = [
                        "type" => "photo",
                        "id" => $id_name,
                        "credit"=> "",
                        "filename" => basename($final_url),
                        "url" => $final_url,
                        "tags" => [],
                        "caption" => isset($value['ALT']) ? $value['ALT'] : '',
                        "title" => pathinfo($final_url, PATHINFO_FILENAME),
                        "inlineHref" => "/photos/$id_name"
                    ];
                }
                
            }
        }
        return $images;
    }
    protected function getCategories() {
        // categories
        $result = [];        
        if($_GET['type']=='product_col') {
            $result['sections'][] = [
                'type' => 'category',
                'slug' => "collection",
                'name' => "Collection",
                'parent' => [
                    'type' => 'category',
                    'slug' => "horoscope",
                    'name' => "Horoscope"
                ]
            ];
        }else if($_GET['type'] == 'product') {
            $result['sections'][] = [
                'type' => 'category',
                'slug' => "product",
                'name' => "Product",
                'parent' => [
                    'type' => 'category',
                    'slug' => "horoscope",
                    'name' => "Horoscope"
                ]
            ];
        }
             
        
        
        // $result['functional-tags'][] = [
        //     'type' => 'category',
        //     'slug' => 'noindex',
        //     'name' => '_noindex'
        // ];
        $result['tags'] = $this->record['tags'];
        return $result;
    }

    protected function getTaxonomy($taxonomy, $type) {
        foreach ($taxonomy as $category) {
            $url = API_URL_CATEGORIES . $category;
            $category = fetchJsonFromUrl($url);
            $result['sections'][] = [
                'type' => $type,
                'slug' => slugify($category['name']),
                'name' => $category['name']
            ];
        }
        return $result;
    }

    protected function getPublishHistory() {
        // $uri = str_replace('https://www.vogue.in/horoscope/' , 'https://www.vogue.in/verso-channel/', $this->record['permalink']);
        $uri = $this->record['permalink'];
        $uri = str_replace('https://www.vogue.in/' , '', $uri);
        // $uri = preg_replace('/^([^\/]+)/', '$1-test', $uri);

        // $uri = explode('/',$uri);
        // $uri[0] = $uri[0].'-test';
        // $uri = implode('/',$uri);
        
        if($_GET['type']=='product_col') {
            $pubDate = date('c', strtotime($this->record['post_date']));
        }else if($_GET['type'] == 'product') {
            $date_main = date('Y-m-d',strtotime($this->record['post_date'])).' '.$this->gettimeformat(basename($uri));
            $pubDate = date('c', strtotime($date_main));
        }
        
        // $uri = rtrim($uri, '/').'-test'; // for production testing
        $result = [
          'pubDate' => $pubDate,
          'uri' => rtrim($uri, '/')
        ];
        // print_r($this->record['post_title']);
        // print_r("publish date".$pubDate);
        
        return $result;
    }
    protected function getTimeFormat($val) {
        // Define the time mappings in an array
        $timeMap = [
            'today' => [
                'aries' => "06:57:00", 'taurus' => "06:56:00", 'gemini' => "06:55:00", 'cancer' => "06:54:00",
                'leo' => "06:53:00", 'virgo' => "06:52:00", 'libra' => "06:51:00", 'scorpio' => "06:50:00",
                'sagittarius' => "06:49:00", 'capricorn' => "06:48:00", 'aquarius' => "06:47:00", 'pisces' => "06:46:00"
            ],
            'weekly' => [
                'aries' => "06:42:00", 'taurus' => "06:41:00", 'gemini' => "06:40:00", 'cancer' => "06:39:00",
                'leo' => "06:38:00", 'virgo' => "06:37:00", 'libra' => "06:36:00", 'scorpio' => "06:35:00",
                'sagittarius' => "06:34:00", 'capricorn' => "06:33:00", 'aquarius' => "06:32:00", 'pisces' => "06:31:00"
            ],
            'monthly' => [
                'aries' => "06:27:00", 'taurus' => "06:26:00", 'gemini' => "06:25:00", 'cancer' => "06:24:00",
                'leo' => "06:23:00", 'virgo' => "06:22:00", 'libra' => "06:21:00", 'scorpio' => "06:20:00",
                'sagittarius' => "06:19:00", 'capricorn' => "06:18:00", 'aquarius' => "06:17:00", 'pisces' => "06:16:00"
            ],
            'yearly' => [
                'aries' => "06:12:00", 'taurus' => "06:11:00", 'gemini' => "06:10:00", 'cancer' => "06:09:00",
                'leo' => "06:08:00", 'virgo' => "06:07:00", 'libra' => "06:06:00", 'scorpio' => "06:05:00",
                'sagittarius' => "06:04:00", 'capricorn' => "06:03:00", 'aquarius' => "06:02:00", 'pisces' => "06:01:00"
            ]
        ];
    
        // Convert input to lowercase for case-insensitive matching
        $val = strtolower($val);
    
        // Check which period (today, weekly, monthly, yearly) is in the string
        foreach ($timeMap as $period => $zodiacTimes) {
            if (strpos($val, $period) !== false) {
                // Check for zodiac signs within that period
                foreach ($zodiacTimes as $zodiac => $time) {
                    if (strpos($val, $zodiac) !== false) {
                        return $time;
                    }
                }
            }
        }
    
        return null; // Return null if no match is found
    }
    

    protected function getBody() {
        $content = '<div>'.$this->record['summary'].'</div>';
        $extra_content = '';
        if(isset($this->record['related_products']) && count($this->record['related_products']) > 0){
            $extra_content = '<div>';
            $re_ids = '';
            $this->record['related_products'] = array_reverse($this->record['related_products']);
            foreach ($this->record['related_products'] as $key => $value) {

                $re_ids .= $value['id'].',';
                $image_details = json_decode($value['featured_media'],true);

                $produrl =  str_replace('www.vogue.in' , 'www.vogue.in', $value['permalink']);
                $produrl = rtrim($produrl,'/');
                $extra_content .= '<br><strong id="prod_title" data-src="'.$produrl.'">'.$value['post_title'].'</strong> <br><br> <img src="'.$image_details['guid'].'" id="prod_image" data-src = "'.$produrl.'"> <br><br> <p> '.$value['summary'].'</p> <br> <br>';
            }
            $extra_content .= '</div>';
        }
        // print_r($re_ids."<br>");
        // print_r($extra_content);exit;
        return $this->convert($content.$extra_content,"BODY");
    }

    protected function convert($content,$type = '') {

        $content = '<root>'.$content.'</root>';
        if($type == 'BODY'){
            // print_r($content);exit;
        }
        $tidy = new tidy();
        $tidy_config = ['input-xml' => true, 'output-xml' => true, 'indent' => true];
        $content = $tidy->repairString($content, $tidy_config, 'utf8');

        $p = xml_parser_create();
        xml_parse_into_struct($p, $content, $nodes, $index);

        $result = '';
        $assets = [];
        $nodes_to_ignore = [];
        $slideshowImages = [];
        $insideTargetDiv = false;
        $insideAtag = false;
        $forTwitter = false;
#        var_dump($content);
#        var_dump($nodes);

        $check_div_level = '';
        $check_A_level = '';
        $check_twitter_level = '';
        $tag_A_link = '';
        $twitter_link = '';
        foreach ($nodes as $idx => $node) {
            $idx_one = $idx + 1;
            if (in_array($idx, $nodes_to_ignore))
                continue;
            // print_r($node);
            // print_r("<br><div>===============================================================================================</div>");
                if($type == 'BODY'){
            //         print_r($node);
            // print_r("<br><div>===============================================================================================</div>");
                // For Slider Image
                if($insideTargetDiv && $check_div_level != '' && $node['level'] == $check_div_level && $node['type'] == 'close'){
                    $insideTargetDiv  = false ;
                    $check_div_level = '';
                    $result .= "
                        \+\+\+
                        ";
                        continue;
                }
                if ($node['tag'] === 'DIV' && ( (isset($node['attributes']['ID']) && $node['attributes']['ID'] === 'slide-shotrcode-meta-box') || ((isset($node['attributes']['CLASS']) && ($node['attributes']['CLASS'] === 'in-story-slideshow full-with-slider' || $node['attributes']['CLASS'] === 'instory-slide-counter'))))) {
                    if($node['type'] == 'open') {
                        $CSVFP = 'slider_json_list.csv';

                                if (!file_exists($CSVFP) || filesize($CSVFP) == 0) {

                                    $file = fopen($CSVFP, 'w');
                                    if ($file === false) {
                                        die('Error opening the file for writing.');
                                    }
                                    $headers = ['Article ID','Link'];
                                    fputcsv($file, $headers);
                                    fputcsv($file, [$this->record['id'],$this->record['link']]);
                                    fclose($file);

                                } else {
                                    $file = fopen($CSVFP, 'a');
                                    if ($file === false) {
                                        die('Error opening the file for reading.');
                                    }

                                    fputcsv($file, [$this->record['id'],$this->record['link']]);
                                    fclose($file);
                                }
                        $insideTargetDiv  = true ;
                        $check_div_level = $node['level'];
                        $result .= "
                        \+\+\+slideshow
                        ";
                        continue;
                    }
                }
                //  End Of Slider Image

                // For A tag with Another tag
                if($insideAtag && $check_A_level != '' && $node['level'] == $check_A_level && $node['type'] == 'close'){
                    $insideAtag  = false ;
                    $check_A_level = '';
                    $$tag_A_link = '';
                        continue;
                }
                if ($node['tag'] === 'A') {
                    if($node['type'] == 'open' && empty(trim($node['value'])) && isset($node['attributes']['HREF'])) {
                        $insideAtag  = true ;
                        $check_A_level = $node['level'];
                        $tag_A_link = $node['attributes']['HREF'];
                        continue;
                    }
                }

                // End of A tag with Another tag

                // For Twiiter link with BLOCKQUOTE tag
                if($forTwitter && $check_twitter_level != '' && $node['level'] == $check_twitter_level && $node['type'] == 'close'){
                    $forTwitter  = false ;
                    $check_twitter_level = '';
                    $$twitter_link = '';
                        continue;
                }
                if ($node['tag'] === 'BLOCKQUOTE' && isset($node['attributes']['CLASS']) && $node['attributes']['CLASS'] === 'twitter-tweet' && $node['type'] == 'open') {
                        $forTwitter  = true ;
                        $check_twitter_level = $node['level'];
                        continue;
                }

                if($forTwitter && $node['tag'] == 'A' && $node['type'] == 'complete' && $node['attributes']['HREF'] != '') {
                    if(strpos($node['attributes']['HREF'], 'twitter.com')){
                        if(isset(explode('/',$node['attributes']['HREF'])[4]) && explode('/',$node['attributes']['HREF'])[4] == 'status'){
                                $twitter_link = $node['attributes']['HREF'];

                                $result .= "
                                    ".convertOembed($twitter_link, 'twitter');
                        }
                    }
                }

                // End of Twiiter link with BLOCKQUOTE tag

                 }
                if (!$forTwitter && !empty($node['value']) || 
                ($node['tag'] === 'IMG' && 
                (!empty($node['attributes']['ORIGINAL-SET']) || !empty($node['attributes']['SRC']))) || ($node['tag'] === 'IFRAME' && 
                ( !empty($node['attributes']['SRC'])))) {

                switch ($node['tag']) {
                    case 'MARK':
                    case 'P':
                        $text = $this->converter->convert(filterRubbish($node['value']));
                        $result .= "
                        ".$text;
                        break;
                    case 'SPAN':
                        if (!$insideAtag) {
                            $text = trim($node['value']);
                    
                            // Keywords that should always start a new paragraph
                            $paragraph_starters = [
                                'Read on for what the stars have',
                                'Here are your guidance based'
                            ];
                    
                            // Check if the text starts with any of the special keywords
                            foreach ($paragraph_starters as $starter) {
                                if (strpos($text, $starter) === 0) {
                                    // Force a new paragraph
                                    $result .= "\n\n" . $this->converter->convert(filterRubbish($text)) . "\n\n";
                                    break; // Exit loop once a match is found
                                }
                            }
                    
                            // Otherwise, keep inline behavior
                            if (!isset($starter) || strpos($text, $starter) !== 0) {
                                $result .= ' ' . $this->converter->convert(filterRubbish($text)) . ' ';
                            }
                        } else {
                            // If inside an A tag, process as normal
                            $href = "<a href='" . $tag_A_link . "'>" . trim($node['value']) . "</a>";
                            $result .= ' ' . $this->converter->convert($href) . ' ';
                        }
                        break;
                        
                        
                    case 'BR':
                        $text = $this->converter->convert("<br>");
                        $result .= "
                        ";
                        break;
                    case 'A':#
                        // the simple link
                        if (trim($node['value']) && isset($node['attributes']) && isset($node['attributes']['HREF']) && $node['attributes']['HREF']) {
                            if(strpos($node['attributes']['HREF'], 'twitter.com')){
                                if(isset(explode('/',$node['attributes']['HREF'])[4]) && explode('/',$node['attributes']['HREF'])[4] == 'status'){
                                    $result .= "
                                    ".convertOembed($node['attributes']['HREF'], 'twitter');
                                    break;
                                }else {
                                    $href = "<a href='".$node['attributes']['HREF']."'>".trim($node['value'])."</a>";
                                    $result .= ' '.$this->converter->convert($href).' ';
                                    break;
                                }
                        
                            }else {
                                $href = "<a href='".$node['attributes']['HREF']."'>".trim($node['value'])."</a>";
                                $result .= ' '.$this->converter->convert($href).' ';
                                break;
                            }
                           
                        }else if($insideAtag && $node['value'] != '' && !isset($node['HERF'])) {
                            $href = "<a href='".$tag_A_link."'>".trim($node['value'])."</a>";
                            $result .= ' '.$this->converter->convert($href).' ';
                            break;
                        }  
                        // maybe it's ainstagram?
                        if (isset($node['attributes']) && isset($node['attributes']['HREF']) && strpos($node['attributes']['HREF'], 'instagram.com') !== false) {
                            $result .= convertInstagram($node['attributes']['HREF']);
                            break;
                        }

                        break;
                    case 'SUP':
                        $result .= ' ^'.$node['value'].'^ ';
                        break;
                    case 'EM':
                        if(!$insideAtag){
                        $value = '<'.$node['tag'].'>'.$node['value'].'</'.$node['tag'].'>';
                        $str = $this->converter->convert($value);
                        $result .= ' '.$str.' ';
                        break;
                        }else {
                            $href = "<a href='".$tag_A_link."'>".trim($node['value'])."</a>";
                            $result .= ' '.$this->converter->convert($href).' ';
                            break;
                        }
                    case 'STRONG':
                        if(isset($node['attributes']['ID']) && $node['attributes']['ID'] == 'prod_title'){
                            if(isset($node['attributes']['DATA-SRC'])){
                                $value = '<'.$node['tag'].'>'.$node['value'].'</'.$node['tag'].'>';
                                $str = "## [".$node['value']."](".$node['attributes']['DATA-SRC'].")";
                                // ## [**Aries Horoscope Today: February 27, 2025**](https://stag.vogue.in/content/aries-horoscope-today-february-27-2025)
                                // print_r($str);
                                // print_r("<div>============================================================</div>");
                                $result .= '
                                '.$str;
                            }
                        }else {
                            $value = '<'.$node['tag'].'>'.$node['value'].'</'.$node['tag'].'>';
                            $str = $this->converter->convert($value);
                            $result .= '

                            '.$str;
                        }
                        
                        break;
                    case 'DIV':
                        $text = $this->converter->convert(filterRubbish($node['value']));
                        $result .= ' '.$text.' ';
                        break;
                    case 'I':
                        if((isset($nodes[$idx_one]['tag']) && $nodes[$idx_one]['tag'] == 'B')){
                            $value = '<'.$node['tag'].'>'.$node['value'].'</'.$node['tag'].'>';
                            $str = $this->converter->convert($value);
                            $result .= ' **'.$str.'** ';
                            // print_r($this->getId()."   Italic with Bold <br>");
                            break;
                        }else {
                            $value = '<'.$node['tag'].'>'.$node['value'].'</'.$node['tag'].'>';
                        $str = $this->converter->convert($value);
                        $result .= ' '.$str.' ';
                        break;
                        }
                        
                    case 'H1':
                        $value = '<'.$node['tag'].'>'.$node['value'].'</'.$node['tag'].'>';
                        $str = $this->converter->convert($value);
                        $result .= '
                        '.$str;
                        break;
                    case 'H2':
                        $value = '<'.$node['tag'].'>'.$node['value'].'</'.$node['tag'].'>';
                        $str = $this->converter->convert($value);
                        $result .= '
                        '.$str;
                        break;
                    case 'H3':
                        $value = '<'.$node['tag'].'>'.$node['value'].'</'.$node['tag'].'>';
                        $str = $this->converter->convert($value);
                        $result .= '
                        '.$str;
                        break;
                    case 'B':
                        $h_val = $node['value'];
                        if(strlen(trim($h_val)) != 0){
                        $value = '<'.$node['tag'].'>'.$node['value'].'</'.$node['tag'].'>';
                        $str = $this->converter->convert($value);
                        $result .= '
                        '.$str;
                        }
                        // print_r($this->getId()."    Bold Content Comes <br>");
                        break;
                    case 'H4':
                        $value = '<'.$node['tag'].'>'.$node['value'].'</'.$node['tag'].'>';
                        $str = $this->converter->convert($value);
                        $result .= '
                        '.$str;
                        break;
                    case 'H5':
                        $value = '<'.$node['tag'].'>'.$node['value'].'</'.$node['tag'].'>';
                        $str = $this->converter->convert($value);
                        $result .= '
                        '.$str;
                        break;
                    case 'LI':
                        $str = '* '.$node['value'];
                        $result .= ' '.$str." ";
                        break;
                    case 'IMG':
                       if(isset($node['attributes']['ID']) && $node['attributes']['ID'] == 'prod_image'){
                        if(isset($node['attributes']['SRC']) && isset($node['attributes']['DATA-SRC'])){
                            $this->img_cnt++;
                                $newNumber = str_pad($this->img_cnt, 3, "0", STR_PAD_LEFT);
                                $result .= "
                                 [![#image: /photos/".$this->current_id.'-'.$newNumber."]](".$node['attributes']['DATA-SRC'].")
                                 ";

                                //  [![#image: /photos/67c02b380b53ee4c4039aa8a]](https://stag.vogue.in/content/aries-horoscope-today-february-27-2025)
                                
                                $this->img_arr[$newNumber]['URL'] = $node['attributes']['SRC'];
                                if(isset($node['attributes']['ALT'])){
                                    $this->img_arr[$newNumber]['ALT'] = $node['attributes']['ALT'];
                                }

                        }

                       }else {

                        if (isset($node['attributes']) && $node['attributes'] && !$insideTargetDiv) {
                            $url = isset($node['attributes']['ORIGINAL-SET']) ? $node['attributes']['ORIGINAL-SET'] : $node['attributes']['SRC'];
                            if (!in_array($url, $assets) && $url != '') {
                                $assets[] = $url;
                                $this->img_cnt++;
                                $newNumber = str_pad($this->img_cnt, 3, "0", STR_PAD_LEFT);
                                $result .= "
                                 [#image: /photos/".$this->current_id.'-'.$newNumber."] 
                                 ";
                                
                                $this->img_arr[$newNumber]['URL'] = $url;
                                if(isset($node['attributes']['ALT'])){
                                    $this->img_arr[$newNumber]['ALT'] = $node['attributes']['ALT'];
                                }
                            }
                        }else if ($insideTargetDiv && $node['tag'] === 'IMG' && isset($node['attributes'])) {
                            
                            $url = isset($node['attributes']['ORIGINAL-SET']) ? $node['attributes']['ORIGINAL-SET'] : $node['attributes']['SRC'];
                            if (!in_array($url, $assets) && $url != '') {
                                $assets[] = $url;
                                $this->img_cnt++;
                                $newNumber = str_pad($this->img_cnt, 3, "0", STR_PAD_LEFT);
                                $result .= "
                                 [#image: /photos/".$this->current_id.'-'.$newNumber."] 
                                 ";
                                
                                $this->img_arr[$newNumber]['URL'] = $url;
                                if(isset($node['attributes']['ALT'])){
                                    $this->img_arr[$newNumber]['ALT'] = $node['attributes']['ALT'];
                                }
                            }

                        }
                        else {
                            $message = " === Hier fehlt ein Bild === ";
                            debugelton($this->current_id, $this->current_url, $message, $node);
                        }
                    }
                        break;
                    case 'BLOCKQUOTE':
                        if (!isset($node['attributes']))
                            break;

                        $attributes = $node['attributes'];
                        switch ($attributes['CLASS']) {
                            case 'instagram-media':
                                $result .= "
                                ".convertInstagram($attributes['DATA-INSTGRM-PERMALINK']);
                                break;
                            case 'tiktok-embed':
                                $result .= "
                                ".convertOembed($attributes['CITE'], 'tiktok');
                                break;
                            case 'twitter-tweet':
                                $twitter = getTwitter($idx, $nodes);
                                if (is_array($twitter)) {
                                    $nodes_to_ignore[] = $twitter['nodes_to_ignore'];
                                    $result .= '
                                    ' . $twitter['twitter'];
                                    break;
                                }

                                $message = "Broken BLOCKQUOTE:" . $attributes['CLASS'];
                                debugelton($this->current_id, $this->current_url, $message, $node);
                                break;
                            default:
                                // Let's try to extract quotes.
                                if (strpos($attributes['CLASS'], 'wp-block-quote') !== false) {

                                    $next = $idx+1;
                                    $nodes_to_ignore[] = $next;
                                    $blockquote = $this->converter->convert($nodes[$next]['value']);

                                    $next = $next+1;
                                    $nodes_to_ignore[] = $next;
                                    $next = $next+1;
                                    $nodes_to_ignore[] = $next;
                                    $next = $next+1;
                                    $nodes_to_ignore[] = $next;
                                    $cite = $this->converter->convert($nodes[$next]['value']);
                                    $result ."+++pullquote\n\n" . $blockquote . "\n\n+++\n\n> " . $cite;

                                    break;
                                }
                                $message = 'Unknown BLOCKQUOTE::'.$attributes['CLASS'];
                                debugelton($this->current_id, $this->current_url, $message, $node);
                                break;
                        }
                        break;
                    case 'IFRAME':
                        $attributes = $node['attributes'];
                        if (isset($attributes['SRC']) && strpos($attributes['SRC'], 'open.spotify.com') !== false) {
                            $result .= convertOembed($attributes['SRC'], 'spotify');
                            break;
                        }
                        if (isset($attributes['SRC']) && strpos($attributes['SRC'], 'youtube.com') !== false) {
                            $result .= "
                            ".convertOembed($attributes['SRC'], 'video');
                            break;
                        }
                        $message = 'Unknown IFRAME::';
                        debugelton($this->current_id, $this->current_url, $message, $node);
                        break;
                    case 'SECTION':
                    case 'FIGCAPTION':
                    case 'ROOT':
                    case 'FIGURE':
                    case 'I':
                    case 'G':
                    case 'OL':
                    case 'UL':
                    case 'DIV':
                    case 'CITE':
                    case 'SVG':
                    case 'PATH':
                    case 'NOSCRIPT':
                    case 'SCRIPT':
                        break;
                    default:
                        $message = 'Unknown node';
                        debugelton($this->current_id, $this->current_url, $message, $node);
                        break;
                }
            }
        }

        $result = preg_replace('/[ \t]+/', ' ', $result);
        $result = preg_replace("/\n{3,}/", "\n\n", $result);
        // $result = preg_replace('/\[\*\*(.*?)\n(.*?)\n(.*?)\]/', '[** $1 $2 $3 ]', $result);
        // Remove all \n within Markdown headers and links
$result = preg_replace_callback('/\[\*\*(.*?)\*\*\]\((.*?)\)/s', function ($matches) {
    $clean_text = preg_replace("/\s+/", ' ', trim($matches[1])); // Remove extra spaces and \n
    return "[** $clean_text **](" . trim($matches[2]) . ")";
}, $result);

// Remove \n from headers (## Title)
$result = preg_replace_callback('/(##+)\s*([\s\S]+?)(?=\n|$)/', function ($matches) {
    $clean_title = preg_replace("/\s+/", ' ', trim($matches[2])); // Remove extra spaces and \n
    return $matches[1] . " " . $clean_title;
}, $result);

$result = preg_replace_callback('/## \[\s*(.*?)\s*\]\((.*?)\)/s', function ($matches) {
    // Remove newlines only inside the bracketed text
    $cleaned_text = preg_replace("/\s*\n\s*/", " ", $matches[1]);
    return "## [{$cleaned_text}]({$matches[2]})";
}, $result);
        $result = trim($result);
        
    // Ensure no space appears between Markdown links and punctuation marks (.,!?)
$result = preg_replace('/(\[[^\]]+\]\([^)]+\)) (\.|\!|\,|\?)/', '$1$2', $result);

        return $result;
        // print_r("------------------------------------------------");
        // print_r($result);
        // print_r("------------------------------------------------");
    }

    protected function getImage($node) {

        $node = json_decode($node,true);

        if (!isset($node['ID'])){
            $image_url = $this->getHoroscopeImage($this->getHed(), $this->horoscope_images);

            if(!empty($image_url)){
                $image = [
                    'type' => 'photo',
                    'id' => (string) $image_url['id'],
                    'credit' => "",
                    'filename' => basename(parse_url($image_url['url'], PHP_URL_PATH)),
                    'url' =>  $image_url['url'],
                    'tags' => [],
                    'caption' => $this->converter->convert('<p>'.$image_url['name'].'</p>'),
                    'title' => $this->converter->convert('<p>'.$image_url['name'].'</p>')
                ];
                return $image;
            }else {
            return [];
            }
        }else {
            $url = $node['guid'];
            if(strpos($url, 'author.vogue.in')){
                $url =  str_replace('author.vogue.in' , 'media.vogue.in', $url);
             }
             if(strpos($url, 'www.vogue.in')){
                $url =  str_replace('www.vogue.in' , 'media.vogue.in', $url);
             }

             if(basename(parse_url($url, PHP_URL_PATH)) == 'Daily-Horoscope.jpg' || basename(parse_url($url, PHP_URL_PATH)) == 'Daily-Horoscope-1920x1080.jpg' ){
                $base_image = 'https://archiviodev.vogue.it/horoscope/image/daily_horoscope_image.png';
                $image = [
                    'type' => 'photo',
                    'id' => (string) $node['ID'],
                    'credit' => "",
                    'filename' => basename(parse_url($base_image, PHP_URL_PATH)),
                    'url' =>  $base_image,
                    'tags' => [],
                    'caption' => $this->converter->convert('<p>'.$node['post_content'].'</p>'),
                    'title' => $this->converter->convert('<p>Daily Horoscope</p>')
                ];
                return $image;
             }else {

            $image_url = $this->getHoroscopeImage($this->getHed(), $this->horoscope_images);

            if(!empty($image_url)){
                $image = [
                    'type' => 'photo',
                    'id' => (string) $image_url['id'],
                    'credit' => "",
                    'filename' => basename(parse_url($image_url['url'], PHP_URL_PATH)),
                    'url' =>  $image_url['url'],
                    'tags' => [],
                    'caption' => $this->converter->convert('<p>'.$node['post_content'].'</p>'),
                    'title' => $this->converter->convert('<p>'.$image_url['name'].'</p>')
                ];
                return $image;
            }else {
                $image = [
                    'type' => 'photo',
                    'id' => (string) $node['ID'],
                    'credit' => "",
                    'filename' => basename(parse_url($url, PHP_URL_PATH)),
                    'url' =>  $url,
                    'tags' => [],
                    'caption' => $this->converter->convert('<p>'.$node['post_content'].'</p>'),
                    'title' => $this->converter->convert('<p>'.$node['post_title'].'</p>')
                ];
                return $image;
            }
        }

            
        }
            

        
    }
    protected function getHoroscopeImage($name, $horoscope_images) {
        foreach ($horoscope_images as $index => $data) {
            if (stripos($name, $data['sign']) !== false && stripos($name, 'daily') === false) {
                $image = [];
                $image['id'] = $index;
                $image['name'] = $data['sign'];
                $image['url'] = $data['image'];
                return $image;
            }
        }
        return [];
    }
    protected function getInlineOriImage($name, $collection_images) {
      
        foreach ($collection_images as $index => $data) {
            if (stripos($name, $data['sign']) !== false && stripos($name, 'daily') === false) {
                $image = [];
                $image['id'] = $index;
                $image['name'] = $data['sign'];
                $image['url'] = $data['image'];
                return $image;
            }
        }
        return [];
    }


}

