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
        $content = '<p>'.$this->record['title'].'</p>';
        return $this->convert($content);
    }
    protected function getDek() {
        if(isset($this->record['custom_fields']['product_collection'])){
            $serialized = $this->record['custom_fields']['product_collection'][0];
            $data = unserialize($serialized);  
            if(isset($data['summary']) && $data['summary'] != ''){
                $content = '<p>'.$data['summary'].'</p>';
                return $this->convert($content);
            }else {
                $content = '<p>'.$this->record['title'].'</p>';
                return $this->convert($content);
            }
        } 
        
    }
    protected function getSeoTitle() {
        if(!empty($this->record['custom_fields']['_yoast_wpseo_title'][0])){
            $content = '<p>'.$this->record['custom_fields']['_yoast_wpseo_title'][0].'</p>';
            return $this->convert($content);
        }else {
            return $this->getHed();
        }        
    }
    protected function getSeoDescription() {
        if(!empty($this->record['custom_fields']['_yoast_wpseo_metadesc'][0])){
            $content = '<p>'.$this->record['custom_fields']['_yoast_wpseo_metadesc'][0].'</p>';
            return $this->convert($content);
        }else {
            return $this->getDek();
        }
    }
  
    protected function getTags() {
        return [];
    }
    protected function getRubric() {
        return (string) '';
    }
    protected function getLedeCaption() {
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
        $this->record['link'] =  str_replace('creator.gqindia.com' , 'www.gqindia.com', $this->record['link']);
        return $this->record['link'];
    }

    protected function getPhotosTout() {
        return $this->getPhotosLede();
    }

    protected function getPhotosLede() {

        $lede = $this->getImage($this->record['featured_media']);
        return [$lede];
    }

    protected function getPhotosSocial() {
        return $this->getPhotosLede();
    }
    protected function getContributorsAuthor() {
        // $url = API_URL_AUTHOR . $this->record['author'];
        // $author = fetchJsonFromUrl($url);
        // if (!isset($author['name']))
            return [];

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
                if(strpos($final_url, 'creator.gqindia.com')){
                    $final_url =  str_replace('creator.gqindia.com' , 'media.gqindia.com', $final_url);
                 }
                 if(strpos($final_url, 'author.gqindia.com')){
                     $final_url =  str_replace('author.gqindia.com' , 'media.gqindia.com', $final_url);
                  }
                $id_name = $this->current_id.'-'.$key;
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
                    // "caption" => isset($value['ALT']) ? $value['ALT'] : '',
                    "caption" => '',
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

        if($_GET['cat']=='GW') {
            $result['sections'][] = [
                'type' => 'category',
                'slug' => "gq-wardrobe",
                'name' => "GQ Wardrobe"
            ];
        }else if($_GET['cat'] == 'BW'){
            $result['sections'][] = [
                'type' => 'category',
                'slug' => "gq-binge-watch",
                'name' => "GQ Binge Watch"
            ];
        }
        
        $result['functional-tags'][] = [
            'type' => 'category',
            'slug' => 'noindex',
            'name' => '_noindex'
        ];

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
        $uri = str_replace(BASE_URL , '', $this->record['link']);

        $pubDate = date('c', strtotime($this->record['date']));
        $result = [
          'pubDate' => $pubDate,
          'uri' => $uri
        ];
        return $result;
    }

    protected function getBody() {
        $content = $this->record['content'];
        $extra_content = '';
        $product_groups = '';

        if(isset($this->record['custom_fields']['product_groups'])){
            $grp_serialized = $this->record['custom_fields']['product_groups'][0];
            $grp_data = unserialize($grp_serialized);

            if(isset($grp_data[0]['title'])) {
                $product_groups .= '<H2>'.$grp_data[0]['title'].'</H2>';
            }
            if(isset($grp_data[0]['description'])) {
                $product_groups .= '<div>'.$grp_data[0]['description'].'</div>';
            }

        }
        if(isset($this->record['products']) && count($this->record['products']) > 0){
            $extra_content = '<div>';
            foreach ($this->record['products'] as $key => $value) {
               $extra_content .= '<br><strong>'.$value['title'].'</strong><br><p>'.$value['content'].'</p> <br> <img src="'.$value['image'].'"> <br>';
            }
            $extra_content .= '</div>';
        }
        return $this->convert($content.$product_groups.$extra_content,"BODY");
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
            // print_r($node);
            // print_r("<br><div>===============================================================================================</div>");
                if($type == 'BODY'){
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
                        $value = '<'.$node['tag'].'>'.$node['value'].'</'.$node['tag'].'>';
                        $str = $this->converter->convert($value);
                        $result .= '
                        '.$str;
                        break;
                    case 'DIV':
                        $div_val = $node['value'];
                        if(strlen(trim($div_val)) != 0){
                            $text = $this->converter->convert(filterRubbish($node['value']));
                            $result .= '
                            '.$text.'
                            ';
                            
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
                        $value = '<'.$node['tag'].'>'.$node['value'].'</'.$node['tag'].'>';
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
        $result = trim($result);
        return $result;
    }

    protected function getImage($node) {

        if (!isset($node['id'])){

            $image = [
                'type' => 'photo',
                'id' => '0',
                'credit' => "",
                'filename' => basename(parse_url('https://assets.gqindia.com/photos/6448cf0e0d73cc36bdb7895a/', PHP_URL_PATH)),
                'url' =>  'https://assets.gqindia.com/photos/6448cf0e0d73cc36bdb7895a/',
                'tags' => [],
                'caption' => $this->converter->convert('<p>GQ India</p>'),
                'title' => $this->converter->convert('<p>GQ India</p>')
            ];
            return $image;

        }else {
            $url = $node['url'];
            if(strpos($url, 'creator.gqindia.com')){
                $url =  str_replace('creator.gqindia.com' , 'media.gqindia.com', $url);
             }
             if(strpos($url, 'author.gqindia.com')){
                 $url =  str_replace('author.gqindia.com' , 'media.gqindia.com', $url);
              }
            $image = [
                'type' => 'photo',
                'id' => (string) $node['id'],
                'credit' => "",
                'filename' => basename(parse_url($url, PHP_URL_PATH)),
                'url' =>  $url,
                'tags' => [],
                'caption' => $this->converter->convert('<p>'.$node['caption'].'</p>'),
                'title' => $this->converter->convert('<p>'.$node['title'].'</p>')
            ];
            return $image;
        }
            

        
    }

}

