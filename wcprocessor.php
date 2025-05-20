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
            'photosTout' => $this->getPhotosTout(),
            'photosLede' => $this->getPhotosLede(),
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
        if(isset($this->record['excerpt']) && $this->record['excerpt'] != ''){
            
                $content = '<p>'.$this->record['excerpt'].'</p>';
                return $this->convert($content);
        } else {
            return $this->getHed();
        }
        
    }
    protected function getSeoTitle() {
        if(!empty($this->record['_yoast_wpseo_title']) && $this->record['_yoast_wpseo_title'] != ''){
            $this->record['_yoast_wpseo_title'] = str_replace("| Vogue India", "", $this->record['_yoast_wpseo_title']);
            $this->record['_yoast_wpseo_title'] = str_replace("| VOGUE India", "", $this->record['_yoast_wpseo_title']);
            $this->record['_yoast_wpseo_title'] = str_replace("| VOGUE INDIA", "", $this->record['_yoast_wpseo_title']);
            $this->record['_yoast_wpseo_title'] = str_replace("|VOGUE India", "", $this->record['_yoast_wpseo_title']);
            $content = '<p>'.$this->record['_yoast_wpseo_title'].'</p>';
            return $this->convert($content);
        }else {
            return $this->getHed();
        }        
    }
    protected function getSeoDescription() {
        if(!empty($this->record['_yoast_wpseo_metadesc']) && $this->record['_yoast_wpseo_metadesc'] != ''){
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
       
       
        if (!isset($this->record['author_id']) && $this->record['author_id'] == '' && !isset($this->record['author_name']) && $this->record['author_name'] == '' )
            return [];

        $author = [
          'type' => 'contributor',
          'id' => (string) $this->record['author_id'],
          'name' => $this->record['author_name'],
          'tags' => [],
          'uri' => '/author/' . $this->record['author_id']
        ];
        return [$author];
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
                 if(strpos($final_url, 'creator.vogue.in')){
                    $final_url =  str_replace('creator.vogue.in' , 'media.vogue.in', $final_url);
                 }
                $id_name = $this->current_id.'-'.$key;
                if (preg_match('/\.webp($|\?)/i', $final_url)) {
                    $final_url = "https://dev1.vogue.in/JSON_FILES/WW_WEBP_IMAGES/".basename($final_url).".jpg";
                  }
                $path = pathinfo($final_url);
                $encodedFilename = rawurlencode(urldecode($path['basename'])); 
                $url_changed = "{$path['dirname']}/$encodedFilename";

                $images[] = [
                    "type" => "photo",
                    "id" => $id_name,
                    "credit"=> "",
                    "filename" => basename($final_url),
                    "url" => $url_changed,
                    "tags" => [],
                    "caption" => isset($value['ALT']) ? $value['ALT'] : '',
                    "title" => pathinfo($final_url, PATHINFO_FILENAME),
                    "inlineHref" => "/photos/$id_name"
                ];
            }
        }
        return $images;
    }
    protected function getCategories() {
        // categories
        $result = [];        
        if($_GET['cat_id'] == 18664) {
            $slug_name = 'horoscope';
            $tag_name = 'Horoscope';
        }
        if($_GET['cat_id'] == 60944) {
            $slug_name = 'vogue-closet';
            $tag_name = 'Vogue Closet';
        }
        if($_GET['cat_id'] == 60945) {
            $slug_name = 'weddings';
            $tag_name = 'Weddings';
        }
        if($_GET['type']=='product_col') {
            $result['sections'][] = [
                'type' => 'category',
                'slug' => "bridal-looks",
                'name' => "Bridal Looks",
                'parent' => [
                    'type' => 'category',
                    'slug' => $slug_name,
                    'name' => $tag_name
                ]
            ];
        }else if($_GET['type'] == 'product') {
            $result['sections'][] = [
                'type' => 'category',
                'slug' => "bridal-looks",
                'name' => "Bridal Looks",
                'parent' => [
                    'type' => 'category',
                    'slug' => $slug_name,
                    'name' => $tag_name
                ]
            ];
        }
             
        
        
        // $result['functional-tags'][] = [
        //     'type' => 'category',
        //     'slug' => 'noindex',
        //     'name' => '_noindex'
        // ];
        $result['tags'] = $this->record['tags'];
        // print_r($this->record['tags']);exit;

        // foreach ($this->record['tags'] as $key => $val) {
        //     $CSVFP = 'WW_TAG_LIST.csv';

        //     if (!file_exists($CSVFP) || filesize($CSVFP) == 0) {

        //         $file = fopen($CSVFP, 'w');
        //         if ($file === false) {
        //             die('Error opening the file for writing.');
        //         }
        //         $headers = ['Tag Name','Tag Slug'];
        //         fputcsv($file, $headers);
        //         fputcsv($file, [$val['name'],$val['slug']]);
        //         fclose($file);

        //     } else {
        //         $file = fopen($CSVFP, 'a');
        //         if ($file === false) {
        //             die('Error opening the file for reading.');
        //         }

        //         fputcsv($file, [$val['name'],$val['slug']]);
        //         fclose($file);
        //     }
        // }
        
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
        $uri = str_replace('https://www.vogue.in/' , '', $this->record['permalink']);
        // $uri = preg_replace('/^([^\/]+)/', '$1-test', $uri);

        // $uri = explode('/',$uri);
        // $uri[0] = $uri[0].'-test';
        // $uri = implode('/',$uri);
        
            $pubDate = date('c', strtotime($this->record['post_date']));
       
        
        $result = [
          'pubDate' => $pubDate,
          'uri' => rtrim($uri, '/')
        ];
        return $result;
    }

    

    protected function getBody() {
        if($this->record['summary'] != '') {
            $content = "<div>".$this->record['summary']."</div>";
            $extra_content = '';
            if(isset($this->record['related_products']) && count($this->record['related_products']) > 0){
                $extra_content = '<div>';
                $re_ids = '';
                foreach ($this->record['related_products'] as $key => $value) {
                        $extra_content .= $value;
                    
                    
                }
    
                $extra_content .= '</div>';
            }
            return $this->convert($content.$extra_content,"BODY");
        }else {
            return "&nbsp;";
        }
        
    }

    protected function convert($content,$type = '') {

        $content = '<root>'.$content.'</root>';

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
        $insideHtag = false;


        $check_div_level = '';
        $check_A_level = '';
        $check_twitter_level = '';
        $tag_A_link = '';
        $twitter_link = '';
        $check_H_level = '';
        $H_count = 0;
        foreach ($nodes as $idx => $node) {
            $idx_one = $idx + 1;
            $idx_two = $idx + 2;
            if (in_array($idx, $nodes_to_ignore))
                continue;
            
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

                // For H tag with Another tag
                if($insideHtag && $check_H_level != '' && $node['level'] == $check_H_level && $node['type'] == 'close'){
                    $insideHtag  = false ;
                    $check_H_level = '';
                    $H_count = 0;
                    // continue;
                }
                if ($node['tag'] === 'H2') {
                    if($node['type'] == 'open') {
                        $insideHtag  = true ;
                        $H_count = 0;
                        $check_H_level = $node['level'];
                        // continue;
                    }
                }

                // End of H tag with Another tag

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
                                    ".convertOembed($twitter_link, 'twitter')."
                                    ";
                        }
                    }
                }

                // End of Twiiter link with BLOCKQUOTE tag

                 }
                 
                 if($node['type'] == 'cdata'){
                    $cdata = $node['value'];
                    if(strlen(trim($cdata)) == 0){
                        continue;
                    }
                    
                 }
                 if($node['type'] == 'close' && ($node['tag'] == 'H1' || $node['tag'] == 'H2' || $node['tag'] == 'H3' || $node['tag'] == 'H4')){
                   $result .= "  
                   
                            "; 
                        continue;
                    
                    
                 }
                if (!$forTwitter && !empty($node['value']) || 
                ($node['tag'] === 'IMG' && 
                (!empty($node['attributes']['ORIGINAL-SET']) || !empty($node['attributes']['SRC']))) || ($node['tag'] === 'IFRAME' && 
                ( !empty($node['attributes']['SRC'])))) {

                switch ($node['tag']) {
                    case 'MARK':
                    case 'P':
                        if (strpos($node['value'], 'instagram.com') !== false) {
                            $result .= "
                            ".convertInstagram($node['value'])."
                            ";
                            break;
                        }else if (strpos($node['value'], 'youtube.com') !== false) {
                            $result .= "
                            ".convertOembed($node['value'], 'video')."
                            ";
                            break;
                        }else if (strpos($node['value'], 'twitter.com') !== false) {
                            $result .= "
                                    ".convertOembed($node['value'], 'twitter')."
                                    ";
                            break;
                        }else {
                            $p_val = $node['value'];
                        if(strlen(trim($p_val)) != 0){
                            $text = $this->converter->convert(filterRubbish($node['value']));
                            $result .= "
                            ".$text."
                            ";
                        }else {
                            $result .= "
                            "; 
                        }
                            
                            break;
                        }
                        
                    case 'SPAN':
                        if (strpos($node['value'], 'instagram.com') !== false) {
                            $result .= "
                            ".convertInstagram($node['value'])."
                            ";
                            break;
                        }else if (strpos($node['value'], 'youtube.com') !== false) {
                            $result .= "
                            ".convertOembed($node['value'], 'video')."
                            ";
                            break;
                        }else if (strpos($node['value'], 'twitter.com') !== false) {
                            $result .= "
                                    ".convertOembed($node['value'], 'twitter')."
                                    ";
                            break;
                        }else {
                            if(!$insideAtag){
                                $span_val = $node['value'];
                        if(strlen(trim($span_val)) != 0){
                            $text = $this->converter->convert(filterRubbish($node['value']));
                            $result .= ' '.$text.' ';
                        }
                                
                                break;
                            }else {
                                $href = "<a href='".$tag_A_link."'>".trim($node['value'])."</a>";
                                $result .= ' '.$this->converter->convert($href).' ';
                                break;
                            }
                        }
                        
                        
                    case 'BR':
                        $text = $this->converter->convert("<br>");
                        $result .= "
                        ";
                        break;
                    case 'A':

                        if (trim($node['value']) && isset($node['attributes']) && isset($node['attributes']['HREF']) && $node['attributes']['HREF']) {
                            if(strpos($node['attributes']['HREF'], 'twitter.com')){
                                if(isset(explode('/',$node['attributes']['HREF'])[4]) && explode('/',$node['attributes']['HREF'])[4] == 'status'){
                                    $result .= "
                                    ".convertOembed($node['attributes']['HREF'], 'twitter').'
                                    ';
                                    break;
                                }else {
                                    $href = "<a href='".$node['attributes']['HREF']."'>".trim($node['value'])." </a>";
                                    $result .= ' '.$this->converter->convert($href).'   ';
                                    break;
                                }
                        
                            }else {
                                $href = "<a href='".$node['attributes']['HREF']."'>".trim($node['value'])." </a>";
                                $result .= ' '.$this->converter->convert($href).'   ';
                                break;
                            }
                           
                        }else if($insideAtag && $node['value'] != '' && !isset($node['HERF'])) {

                                if($tag_A_link == 'https://www.youtube.com/channel/UCkxP6nWL35Yq6QEjiwWBITw?sub_confirmation=1'){
                                $href = "<a href='".$tag_A_link."'>".trim($node['value'])." </a>";
                                $result .= ' '.$this->converter->convert($href).' 

                                ';
                            }else {
                                $href = "<a href='".$tag_A_link."'>".trim($node['value'])." </a>";
                                $result .= ' '.$this->converter->convert($href).' ';
                            }
                            
                            break;
                        }  

                        if (isset($node['attributes']) && isset($node['attributes']['HREF']) && strpos($node['attributes']['HREF'], 'instagram.com') !== false) {
                            $result .= "
                            ".convertInstagram($node['attributes']['HREF'])."
                            ";
                            break;
                        }

                        break;
                    case 'SUP':
                        $result .= ' ^'.$node['value'].'^ ';
                        break;
                    case 'EM':
                        if(!$insideAtag){
                        $value = '<'.$node['tag'].'>'.$node['value'].' </'.$node['tag'].'>';
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
                                $node['attributes']['DATA-SRC'] =  str_replace('www.vogue.in' , 'stag.vogue.in', $node['attributes']['DATA-SRC']);
                                $value = '<'.$node['tag'].'>'.$node['value'].'</'.$node['tag'].'>';
                                $str = "## [".$node['value']."](".$node['attributes']['DATA-SRC'].")";
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
                        $div_val = $node['value'];
                        if(strlen(trim($div_val)) != 0){
                            if (strpos($node['value'], 'instagram.com') !== false && count(explode(' ',trim($node['value']))) == 1) {
                                $pattern = '/https?:\/\/(www\.)?instagram\.com\/[^\s]+/i';
                                $textWithoutLink = preg_replace($pattern, '', $node['value']);
                                $textWithoutLink = trim($textWithoutLink);
                                $text = $this->converter->convert(filterRubbish($textWithoutLink));
                                $result .= '
                                '.$text.'
                                ';
                            }else if (strpos($node['value'], 'instagram.com') !== false && count(explode(' ',trim($node['value']))) > 1) {
                                
                                $pattern = '/https?:\/\/(www\.)?instagram\.com\/[^\s]+/i';
                                $textWithoutLink = preg_replace($pattern, '', $node['value']);
                                $textWithoutLink = trim($textWithoutLink);
                                $text = $this->converter->convert(filterRubbish($textWithoutLink));
                                $result .= '
                                '.$text.'
                                ';
                            }else if(strpos($node['value'], 'youtube.com') !== false){
                               
                            // Regular expression to match YouTube URLs
                            $pattern = '/(https:\/\/www\.youtube\.com\/watch\?[^\s]+)/i';

                            // Replace the YouTube URL with the desired format
                            $modifiedStr = preg_replace($pattern, '[#video: $1]', $node['value']);
                            $result .= '
                                '.$modifiedStr.'
                                ';
                            
                            }else {
                                $text = $this->converter->convert(filterRubbish($node['value']));
                                $result .= '
                                '.$text.'
                                ';
                            }
                            
                            
                            
                        }
                        break;
                        
                    case 'I':
                        $value = '<'.$node['tag'].'>'.$node['value'].' </'.$node['tag'].'>';
                        $str = $this->converter->convert($value);
                        $result .= ' '.$str.' ';
                        break;
                    case 'H1':
                        // $h_val = $node['value'];
                        // if(strlen(trim($h_val)) != 0){
                        $value = '<H2>'.$node['value'].'</H2>';
                        $str = $this->converter->convert($value);
                        $result .= '
                        '.$str;
                        // }
                        break;
                    case 'H2':
                        // $h_val = $node['value'];
                        // if(strlen(trim($h_val)) != 0){
                        if($insideHtag && $H_count == 0 ){
                            $value = '<'.$node['tag'].'>'.$node['value'].'</'.$node['tag'].'>';
                            $str = $this->converter->convert($value);
                            $result .= '
                            '.$str;
                            $H_count = 1;
                        }else if ($insideHtag && $H_count > 0) {
                            $value = '<'.$node['tag'].'>'.$node['value'].'</'.$node['tag'].'>';
                            $str = $this->converter->convert($value);
                            $result .= ' '.$node['value'];
                            $H_count = 0;
                        }else {
                            $value = '<'.$node['tag'].'>'.$node['value'].'</'.$node['tag'].'>';
                            $str = $this->converter->convert($value);
                            $result .= '
                            '.$str;
                        }
                    // }
                        
                        break;
                    case 'H3':
                        // $h_val = $node['value'];
                        // if(strlen(trim($h_val)) != 0){
                        $value = '<'.$node['tag'].'>'.$node['value'].'</'.$node['tag'].'>';
                        $str = $this->converter->convert($value);
                        $result .= '
                        '.$str;
                        // }
                        break;
                    case 'B':
                        $h_val = $node['value'];
                        if(strlen(trim($h_val)) != 0){
                        $value = '<'.$node['tag'].'>'.$node['value'].'</'.$node['tag'].'>';
                        $str = $this->converter->convert($value);
                        $result .= '
                        '.$str;
                        }
                        break;
                    case 'H4':
                        // $h_val = $node['value'];
                        // if(strlen(trim($h_val)) != 0){
                        $value = '<'.$node['tag'].'>'.$node['value'].'</'.$node['tag'].'>';
                        $str = $this->converter->convert($value);
                        $result .= '
                        '.$str;
                        // }
                        break;
                    case 'H5':
                        // $h_val = $node['value'];
                        // if(strlen(trim($h_val)) != 0){
                        $value = '<'.$node['tag'].'>'.$node['value'].'</'.$node['tag'].'>';
                        $str = $this->converter->convert($value);
                        $result .= '
                        '.$str;
                        // }
                        break;
                    case 'LI':
                        $li_val = $node['value'];
                        if(strlen(trim($li_val)) != 0){
                        $str = '* '.$node['value'];
                        $result .= ' '.$str." ";
                        }
                        break;
                    case 'IMG':
                        if(isset($node['attributes']['ID']) && $node['attributes']['ID'] == 'prod_image'){
                            if(isset($node['attributes']['SRC']) && isset($node['attributes']['DATA-SRC'])){
                                $node['attributes']['DATA-SRC'] =  str_replace('www.vogue.in' , 'stag.vogue.in', $node['attributes']['DATA-SRC']);
                                $this->img_cnt++;
                                    $newNumber = str_pad($this->img_cnt, 3, "0", STR_PAD_LEFT);
                                    $result .= "
                                     [![#image: /photos/".$this->current_id.'-'.$newNumber."]](".$node['attributes']['DATA-SRC'].")
                                     ";
    
                                   
                                    
                                    $this->img_arr[$newNumber]['URL'] = $node['attributes']['SRC'];
                                    if(isset($node['attributes']['ALT'])){
                                        $this->img_arr[$newNumber]['ALT'] = $node['attributes']['ALT'];
                                    }
    
                            }
    
                           }else {
                        if (isset($node['attributes']) && $node['attributes'] && !$insideTargetDiv) {
                            $url = isset($node['attributes']['ORIGINAL-SET']) ? $node['attributes']['ORIGINAL-SET'] : $node['attributes']['SRC'];
                            if (!in_array($url, $assets) && $url != '') {
                                $this->img_cnt++;
                                $newNumber = str_pad($this->img_cnt, 3, "0", STR_PAD_LEFT);
                                if((isset($nodes[$idx_one]['tag']) && $nodes[$idx_one]['tag'] == 'FIGCAPTION') || (isset($nodes[$idx_two]['tag']) && $nodes[$idx_two]['tag'] == 'FIGCAPTION')){
                                    $result .= "
                                    [#image: /photos/".$this->current_id.'-'.$newNumber."]";
                                }else {
                                    $result .= "
                                                    [#image: /photos/".$this->current_id.'-'.$newNumber."] 
                                                    ";
                                }

                                $this->img_arr[$newNumber]['URL'] = $url;
                                if(isset($node['attributes']['ALT'])){
                                    $this->img_arr[$newNumber]['ALT'] = $node['attributes']['ALT'];
                                }
                            }
                        }else if ($insideTargetDiv && $node['tag'] === 'IMG' && isset($node['attributes'])) {
                            
                            $url = isset($node['attributes']['ORIGINAL-SET']) ? $node['attributes']['ORIGINAL-SET'] : $node['attributes']['SRC'];
                            if (!in_array($url, $assets) && $url != '') {
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
                                ".convertInstagram($attributes['DATA-INSTGRM-PERMALINK'])."
                                ";
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
                            ".convertOembed($attributes['SRC'], 'video')."
                            ";
                            break;
                        }
                        $message = 'Unknown IFRAME::';
                        debugelton($this->current_id, $this->current_url, $message, $node);
                        break;
                    case 'SECTION':
                    case 'FIGCAPTION':
                        $value = '<p>'.$node['value'].'</p>';
                        $str = $this->converter->convert($value);
                        $result .= '|||'.$str.'||| 
                        ';
                        break;
                    case 'BUTTON':
                        $value = '<p>'.$node['value'].'</p>';
                        $str = $this->converter->convert($value);
                        if(isset($node['attributes']['DATA-LINK'])){
                            $result .= '+++button-group
 
                            [Buy now]('.$node['attributes']['DATA-LINK'].' "Buy now"){: target="_blank"}
                            
                           +++';
                        }
                        // +++button-group
 
// [Buy now](https://stag.vogue.in/vogue-closet/product/heels-badgley-mischka-3 "Buy now"){: target="_blank"}
 
// +++
                        
                        break;
                    case 'ROOT':
                    case 'FIGURE':
                    case 'G':
                    case 'OL':
                    case 'UL':
                    case 'DIV':
                    case 'CITE':
                    case 'SVG': // svg; not supported by CoPilot
                    case 'PATH': // svg; not supported by CoPilot
                    case 'NOSCRIPT':
                    case 'SCRIPT':
#                        debugelton($this->current_id, $this->current_url, "Irrelevant node:", '');
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
        $result = preg_replace('/\s+(\.|,)/', '$1', $result);
        $result = preg_replace('/\[\s*(.*?)\s*\](?=\([^\)]+\)\s*[.,])/', '[$1]', $result);
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
        return $result;
    }

    protected function getImage($node) {

        $node = json_decode($node,true);

        if (!isset($node['ID'])){
            return [];
        }else {
            $url = $node['guid'];
            if(strpos($url, 'author.vogue.in')){
                $url =  str_replace('author.vogue.in' , 'media.vogue.in', $url);
             }
             if(strpos($url, 'creator.vogue.in')){
                $url =  str_replace('creator.vogue.in' , 'media.vogue.in', $url);
             }
             if(strpos($url, 'www.vogue.in')){
                $url =  str_replace('www.vogue.in' , 'media.vogue.in', $url);
             }

             $path = pathinfo($url);
             $encodedFilename = rawurlencode(urldecode($path['basename'])); 
             $url_changed = "{$path['dirname']}/$encodedFilename";

            $image = [
                'type' => 'photo',
                'id' => (string) $node['ID'],
                'credit' => "",
                'filename' => basename(parse_url($url, PHP_URL_PATH)),
                'url' =>  $url_changed,
                'tags' => [],
                'caption' => $this->converter->convert('<p>'.$node['post_content'].'</p>'),
                'title' => $this->converter->convert('<p>'.$node['post_title'].'</p>')
            ];
            return $image;
        }
            

        
    }

}

