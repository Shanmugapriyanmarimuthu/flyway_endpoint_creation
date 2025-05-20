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
    public $site_img; 
    public $map_contenttype = [
        'page' => 'article',
        'post' => 'article',
        'product' => 'product',
        'product_collection' => 'article',
    ];
    

    public $BVCATS;

    public function processData() {
        $this->converter = new HtmlConverter();
        $result = [];
        if (is_array($this->data)) {
            // if we have a single record return it as an object
            if (isset($this->data['ID'])) {
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
        $this->BVCATS = file_get_contents('/Users/shanmugapriyanmac/Desktop/WordpressToFlyway-main/url_category_mapping.json');
        $this->BVCATS = json_decode($this->BVCATS,true);
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
        return (string) $this->record['ID'];
    }
    protected function getBrand() {
        return 'vit';
    }
    protected function getLang() {
        return 'it-IT';
    }
    protected function getHed() {
        $content = '<p>'.$this->record['title'].'</p>';
        return $this->convert($content);
    }
    protected function getDek() {
        if(isset($this->record['excerpt']) && $this->record['excerpt'] != ''){
            
            $content = '<p>'.$this->record['excerpt'].'</p>';
            return $this->convert($content);
        }  else {
            return $this->getHed();
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
        return $this->record['link'];
    }
    // also main images
    protected function getPhotosTout() {
        return $this->getPhotosLede();
    }
    // main image
    protected function getPhotosLede() {

        $lede = $this->getImage($this->record['featured_image'],$this->site_img);
        return [$lede];
    }
    // social image
    protected function getPhotosSocial() {
        return $this->getPhotosLede();
    }
    protected function getContributorsAuthor() {
        // $url = API_URL_AUTHOR . $this->record['author'];
        // $author = fetchJsonFromUrl($url);

        $author_data = $this->record['author'];
        if (!isset($author_data['ID'])){
            return [];
        }
           

        $author = [
          'type' => 'contributor',
          'id' => (string) $author_data['ID'],
          'name' => $author_data['name'],
          'tags' => [],
          'uri' => '/author/' . $author_data['ID']
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
                if(strpos($final_url, 'creator.gqindia.com')){
                    $final_url =  str_replace('creator.gqindia.com' , 'media.gqindia.com', $final_url);
                 }
                 if(strpos($final_url, 'author.gqindia.com')){
                     $final_url =  str_replace('author.gqindia.com' , 'media.gqindia.com', $final_url);
                  }
                $id_name = $this->current_id.'-'.$key;
                $path = pathinfo($final_url);
                $encodedFilename = urlencode($path['basename']); 
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

// foreach ($this->record['terms']['category'] as $key => $value) {
    
    

//    if(isset($value['parent']['name']) && isset($value['parent']['slug'])){

//    $parent = [
//     'type' => 'category',
//     'slug' => $value['parent']['slug'],
//     'name' => $value['parent']['name']
// ];
// $result['sections'][] = [
//     'type' => 'category',
//     'slug' => $value['slug'],
//     'name' => $value['name'],
//     'parent' => $parent
// ];
//    }else {
//     $result['sections'][] = [
//         'type' => 'category',
//         'slug' => $value['slug'],
//         'name' => $value['name']
//     ];
//    }
//     }

if(isset($this->BVCATS[$this->record['link']])){
    $parent_name = $this->BVCATS[$this->record['link']]['Category'];
    $parent_slug = $this->BVCATS[$this->record['link']]['Category_slug'];
    $child_name = $this->BVCATS[$this->record['link']]['Sub Category'];
    $child_slug = isset($this->BVCATS[$this->record['link']]['Sub_slug']) ? $this->BVCATS[$this->record['link']]['Sub_slug'] : "" ;

    if($child_name != ""){
        $parent = [
        'type' => 'category',
        'slug' => $parent_slug,
        'name' => $parent_name
        ];
        $result['sections'][] = [
        'type' => 'category',
        'slug' => $child_slug,
        'name' => $child_name,
        'parent' => $parent
        ];
    }else {
        $result['sections'][] = [
        'type' => 'category',
        'slug' => $parent_slug,
        'name' => $parent_name
        ];
    }

}

        
        // $result['functional-tags'][] = [
        //     'type' => 'category',
        //     'slug' => 'noindex',
        //     'name' => '_noindex'
        // ];

        
        if(isset($this->record['terms']['topic_tag']) && count($this->record['terms']['topic_tag']) > 0){
            foreach ($this->record['terms']['topic_tag'] as $key => $val) {
    
            

                if(isset($val['name']) && isset($val['slug'])){

                    // $CSVFP = 'BV_TAG_LIST.csv';

                    //             if (!file_exists($CSVFP) || filesize($CSVFP) == 0) {

                    //                 $file = fopen($CSVFP, 'w');
                    //                 if ($file === false) {
                    //                     die('Error opening the file for writing.');
                    //                 }
                    //                 $headers = ['Tag Name','Tag Slug'];
                    //                 fputcsv($file, $headers);
                    //                 fputcsv($file, [$val['name'],$val['slug']]);
                    //                 fclose($file);

                    //             } else {
                    //                 $file = fopen($CSVFP, 'a');
                    //                 if ($file === false) {
                    //                     die('Error opening the file for reading.');
                    //                 }

                    //                 fputcsv($file, [$val['name'],$val['slug']]);
                    //                 fclose($file);
                    //             }

                 $result['tags'][] = [
                     'type' => 'category',
                     'slug' => $val['slug'],
                     'name' => $val['name']
                 ];
                }
                 }
        }
        

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
        $uri = str_replace('https://beauty.vogue.it/' , '', $this->record['link']);
        // $uri = preg_replace('/^([^\/]+)/', '$1-test', $uri);

        // $uri = explode('/',$uri);
        // $uri[0] = $uri[0].'-test';
        // $uri = implode('/',$uri);
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
            // print_r($grp_data[0]['description']);exit;  
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
               $extra_content .= '<br><strong>'.$value['title'].'</strong> <br> <img src="'.$value['image'].'"> <br>';
            }
            $extra_content .= '</div>';
        }
        return $this->convert($content.$product_groups.$extra_content,"BODY");
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
        $insideHtag = false;
#        var_dump($content);
#        var_dump($nodes);

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
            // print_r($idx."  sdssdsdsdsd<br>");
            // print_r($node);
            // print_r($nodes[$idx_one]['tag']."<br>");
            // print_r($nodes[$idx_two]['tag']."<br>");
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
                        // $CSVFP = 'slider_json_list.csv';

                        //         if (!file_exists($CSVFP) || filesize($CSVFP) == 0) {

                        //             $file = fopen($CSVFP, 'w');
                        //             if ($file === false) {
                        //                 die('Error opening the file for writing.');
                        //             }
                        //             $headers = ['Article ID','Link'];
                        //             fputcsv($file, $headers);
                        //             fputcsv($file, [$this->record['id'],$this->record['link']]);
                        //             fclose($file);

                        //         } else {
                        //             $file = fopen($CSVFP, 'a');
                        //             if ($file === false) {
                        //                 die('Error opening the file for reading.');
                        //             }

                        //             fputcsv($file, [$this->record['id'],$this->record['link']]);
                        //             fclose($file);
                        //         }
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
                                    ".convertOembed($twitter_link, 'twitter');
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
                if (!$forTwitter && (!empty($node['value']) || 
                ($node['tag'] === 'IMG' && 
                (!empty($node['attributes']['ORIGINAL-SET']) || !empty($node['attributes']['SRC']))) || ($node['tag'] === 'IFRAME' && 
                ( !empty($node['attributes']['SRC']))) || $node['tag'] == 'BR' )) {

                switch ($node['tag']) {
                    case 'MARK':
                    case 'P':
                        if (strpos($node['value'], 'instagram.com') !== false) {
                            $result .= convertInstagram($node['value']);
                            break;
                        }else {
                            $text = $this->converter->convert(filterRubbish($node['value']));
                            $result .= "
                            ".$text."
                            ";
                            break;
                        }
                        
                    case 'SPAN':
                        if (strpos($node['value'], 'instagram.com') !== false) {
                            $result .= convertInstagram($node['value']);
                            break;
                        }else {
                            if(!$insideAtag){
                                $text = $this->converter->convert(filterRubbish($node['value']));
                                $result .= ' '.$text.' ';
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
                    case 'A':#
                        // the simple link
                        if (trim($node['value']) && isset($node['attributes']) && isset($node['attributes']['HREF']) && $node['attributes']['HREF']) {
                            if(strpos($node['attributes']['HREF'], 'twitter.com')){
                                if(isset(explode('/',$node['attributes']['HREF'])[4]) && explode('/',$node['attributes']['HREF'])[4] == 'status'){
                                    $result .= "
                                    ".convertOembed($node['attributes']['HREF'], 'twitter').' ';
                                // $CSVFP = 'twitter_json_list.csv';

                                // if (!file_exists($CSVFP) || filesize($CSVFP) == 0) {

                                //     $file = fopen($CSVFP, 'w');
                                //     if ($file === false) {
                                //         die('Error opening the file for writing.');
                                //     }
                                //     $headers = ['Article ID'];
                                //     fputcsv($file, $headers);
                                //     fputcsv($file, [$this->record['id']]);
                                //     fclose($file);

                                // } else {
                                //     $file = fopen($CSVFP, 'a');
                                //     if ($file === false) {
                                //         die('Error opening the file for reading.');
                                //     }

                                //     fputcsv($file, [$this->record['id']]);
                                //     fclose($file);
                                // }
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
                            $href = "<a href='".$tag_A_link."'>".trim($node['value'])." </a>";
                            $result .= ' '.$this->converter->convert($href).'   ';
                            break;
                        }  
                        // maybe it's ainstagram?
                        if (isset($node['attributes']) && isset($node['attributes']['HREF']) && strpos($node['attributes']['HREF'], 'instagram.com') !== false) {
                            $result .= convertInstagram($node['attributes']['HREF']);
                            break;
                        }
#                        $message = "Invalid Link";
#                        debugelton($this->current_id, $this->current_url, $message, $node);

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
                    case 'DIV':
                        $text = $this->converter->convert(filterRubbish($node['value']));
                        $result .= '
                        '.$text.'
                        ';
                        break;
                    case 'I':
                        $value = '<'.$node['tag'].'>'.$node['value'].' </'.$node['tag'].'>';
                        $str = $this->converter->convert($value);
                        $result .= ' '.$str.' ';
                        break;
                    case 'H1':
                        $value = '<'.$node['tag'].'>'.$node['value'].'</'.$node['tag'].'>';
                        $str = $this->converter->convert($value);
                        $result .= '
                        '.$str;
                        break;
                    case 'H2':
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
                        
                        break;
                    case 'H3':
                        $value = '<'.$node['tag'].'>'.$node['value'].'</'.$node['tag'].'>';
                        $str = $this->converter->convert($value);
                        $result .= '
                        '.$str;
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
                        if (isset($node['attributes']) && $node['attributes'] && !$insideTargetDiv) {
                            $url = isset($node['attributes']['ORIGINAL-SET']) ? $node['attributes']['ORIGINAL-SET'] : $node['attributes']['SRC'];
                            if (!in_array($url, $assets) && $url != '') {
                                // $assets[] = $url;
                                // $img = "<img src='".$url."'>";
                                // $result .= ' '.$this->converter->convert($img);
                                $this->img_cnt++;
                                $newNumber = str_pad($this->img_cnt, 3, "0", STR_PAD_LEFT);
                            if($this->img_cnt == 1){
                                $this->site_img = $url;
                            }elseif($this->img_cnt > 1){

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
                            }
                        }else if ($insideTargetDiv && $node['tag'] === 'IMG' && isset($node['attributes'])) {
                            
                            $url = isset($node['attributes']['ORIGINAL-SET']) ? $node['attributes']['ORIGINAL-SET'] : $node['attributes']['SRC'];
                            if (!in_array($url, $assets) && $url != '') {
                                // $assets[] = $url;
                                // $img = "<img src='".$url."'>";
                                // $result .= ' '.$this->converter->convert($img);
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
                        // $value = '<p>'.$node['value'].'</p>';
                        // $str = $this->converter->convert($value);
                        // $result .= '|||'.$str.'||| 
                        // ';
                        // break;
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

        // $result = preg_replace('/\s+/', ' ', $result);
        // $result = trim($result);
        $result = preg_replace('/[ \t]+/', ' ', $result);
        $result = preg_replace("/\n{3,}/", "\n\n", $result);
        $result = preg_replace('/\s+(\.|,)/', '$1', $result);
        $result = preg_replace('/\[\s*(.*?)\s*\](?=\([^\)]+\)\s*[.,])/', '[$1]', $result);
        $result = trim($result);
        return $result;
    }

    protected function getImage($node,$site_image = '') {

        if (!isset($node['ID'])){
            if($site_image != ''){
                $image = [
                    'type' => 'photo',
                    'id' => '0',
                    'credit' => "",
                    'filename' => basename(parse_url($site_image, PHP_URL_PATH)),
                    'url' =>  $site_image,
                    'tags' => [],
                    'caption' => $this->converter->convert('<p>Beauty Vogue</p>'),
                    'title' => $this->converter->convert('<p>Beauty Vogue</p>'),
                    "restrictCropping"=> false
                ];

            }else{

            $image = [
                'type' => 'photo',
                'id' => '0',
                'credit' => "",
                'filename' => basename(parse_url('https://images.glamour.it/wp-content/uploads/2015/03/1425463689_Vogue-8-15-67-COVER.jpg', PHP_URL_PATH)),
                'url' =>  'https://images.glamour.it/wp-content/uploads/2015/03/1425463689_Vogue-8-15-67-COVER.jpg',
                'tags' => [],
                'caption' => $this->converter->convert('<p>Beauty Vogue</p>'),
                'title' => $this->converter->convert('<p>Beauty Vogue</p>'),
                "restrictCropping"=> false
            ];
        }
            return $image;

        }else {
            if($site_image != ''){
            $image = [
                'type' => 'photo',
                'id' => (string) $node['ID'],
                'credit' => "",
                'filename' => basename(parse_url($site_image, PHP_URL_PATH)),
                'url' =>  $site_image,
                'tags' => [],
                'caption' => "",
                'title' => $this->converter->convert('<p>'.$node['title'].'</p>'),
                "restrictCropping"=> false
            ];

            }else {
                $url = $node['source'];
                $image = [
                    'type' => 'photo',
                    'id' => (string) $node['ID'],
                    'credit' => "",
                    'filename' => basename(parse_url($url, PHP_URL_PATH)),
                    'url' =>  $url,
                    'tags' => [],
                    'caption' => "",
                    'title' => $this->converter->convert('<p>'.$node['title'].'</p>'),
                    "restrictCropping"=> false
                ];
            }
            
            return $image;
        }
            

        
    }

}

