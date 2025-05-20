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
    public $map_contenttype = [
        'page' => 'article',
        'post' => 'article',
        'product' => 'article',
        'product_collection' => 'sub_channel',
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
            'dek' => $this->getDek(),
            'seoTitle' => $this->getSeoTitle(),
            'seoDescription' => $this->getSeoDescription(),
            'tags' => [],
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
            'inline' => $this->getInline(),
            'categories' => $this->getCategories(),
            'publishHistory' => $this->getPublishHistory()
        ];
#        return '';
        return $result;

    }

    protected function getType() {
        return isset($this->map_contenttype[$this->record['type']]) ? $this->map_contenttype[$this->record['type']] : $this->record['type'];
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
        $content = '<p>'.$this->record['title']['rendered'].'</p>';
        return $this->convert($content);
    }
    protected function getDek() {
        if(isset($this->record['excerpt'])){
            $content = '<p>'.$this->record['excerpt']['rendered'].'</p>';
            return $this->convert($content);
        }else {
            $content = '<p>'.$this->record['title']['rendered'].'</p>';
            return $this->convert($content);
        }
        
    }
    protected function getSeoTitle() {
        return $this->getHed();
    }
    protected function getSeoDescription() {
        return $this->getDek();
    }
    // as this is only for private tags we return an empty array
    protected function getTags() {
        $tagName = date('dMy', strtotime($this->record['modified']));
        return $tagName;
    }
    protected function getRubric() {
        return (string) '';
    }
    protected function getLedeCaption() {
        $url = API_URL_MEDIA . $this->record['featured_media'];
        $lede = fetchJsonFromUrl($url);
        return (isset($lede['caption'])) ? $this->converter->convert($lede['caption']['rendered']) : '';
    }
    protected function getSocialTitle() {
        return $this->getHed();
    }
    protected function getSocialDescription() {
        return $this->getDek();
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
        if($this->record['featured_media'] > 0){
        $url = API_URL_MEDIA . $this->record['featured_media'];
        $lede = fetchJsonFromUrl($url);
        $lede = $this->getImage($lede);
        return [$lede];
        }else {
            return [];
        }
    }
    // social image
    protected function getPhotosSocial() {
        return $this->getPhotosLede();
    }
    protected function getContributorsAuthor() {
        $url = API_URL_AUTHOR . $this->record['author'];
        $author = fetchJsonFromUrl($url);
        if (!isset($author['name']))
            return [];

        $author = [
          'type' => 'contributor',
          'id' => (string) $author['id'],
          'name' => $author['name'],
          'tags' => [],
          'uri' => '/author/' . $author['id']
        ];
        return [$author];
    }
    protected function getInline() {
        $url = API_URL_MEDIA . '?parent=' . $this->record['id'];
        $response = fetchJsonFromUrl($url);
        $images = [];
        foreach ($response as $node) {
            if (!isset($node['id']) || !isset($node['media_details']['sizes']['full']))
                continue;
            $images[] = $this->getImage($node);
        }
        return $images;
    }
    protected function getCategories() {
        // categories
        $result = [];
        foreach ($this->record['categories'] as $category) {
            if (!isset($result['sections'])) {
                $url = API_URL_CATEGORIES . $category;
#                echo "SECTIONS::".$url."<br>";
                $category = fetchJsonFromUrl($url);
                if(isset($category['name'])){
                    $result['sections'][] = [
                        'type' => 'category',
                        'slug' => slugify($category['name']),
                        'name' => $category['name']
                    ];
                }
                
            }
        }
        // tags
    //     if(isset($this->record['tags'])){
    //     foreach ($this->record['tags'] as $tag) {
    //         $url = API_URL_TAGS . $tag;
    //         $tag = fetchJsonFromUrl($url);
    //         if(isset($tag['name'])){
    //         $result['tags'][] = [
    //             'type' => 'category',
    //             'slug' => slugify($tag['name']),
    //             'name' => $tag['name']
    //         ];
    //     }
    //     }
    // }
    if($_GET['brand'] == 'vogue'){
        $result['tags'][] = [
            'type' => 'category',
            'slug' => $this->getTags(),
            'name' => $this->getTags()
        ];
    }
    
    $result['functional-tags'][] = [
        'type' => 'category',
        'slug' => 'noindex',
        'name' => '_noindex'
    ];
#        print_r($result);
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

        // $uri = parse_url($this->record['link'], PHP_URL_PATH); // Extract path from URL
        // $uri = trim(basename($uri), '/');
        $uri = str_replace(BASE_URL , '', $this->record['link']);
        $pubDate = date('c', strtotime($this->record['modified']));
        $result = [
          'pubDate' => $pubDate,
          'uri' => $uri
        ];
        return $result;
    }

    protected function getBody() {
        $content = $this->record['content']['rendered'];
        
        if(empty($content)){
            $content = "<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>";
        }

        return $this->convert($content);
    }

//     protected function convert($content) {

//         $content = '<root>'.$content.'</root>';

//         $tidy = new tidy();
//         $tidy_config = ['input-xml' => true, 'output-xml' => true, 'indent' => true];
//         $content = $tidy->repairString($content, $tidy_config, 'utf8');

//         $p = xml_parser_create();
//         xml_parse_into_struct($p, $content, $nodes, $index);

//         $result = '';
//         $assets = [];
//         $nodes_to_ignore = [];
// #        var_dump($content);
// #        var_dump($nodes);
//         foreach ($nodes as $idx => $node) {
//             if (in_array($idx, $nodes_to_ignore))
//                 continue;

//                 if (!empty($node['value']) || 
//                 ($node['tag'] === 'IMG' && 
//                 (!empty($node['attributes']['ORIGINAL-SET']) || !empty($node['attributes']['SRC'])))) {
//                 switch ($node['tag']) {
//                     case 'MARK':
//                     case 'P':
//                     case 'BR':
//                         $text = $this->converter->convert(filterRubbish($node['value']));
//                         $result .= ' '.$text;
//                         break;
//                     case 'A':#
//                         // the simple link
//                         if (trim($node['value']) && isset($node['attributes']) && isset($node['attributes']['HREF']) && $node['attributes']['HREF']) {
//                             $href = "<a href='".$node['attributes']['HREF']."'>".trim($node['value'])."</a>";
//                             $result .= ' '.$this->converter->convert($href);
//                             break;
//                         }
//                         // maybe it's ainstagram?
//                         if (isset($node['attributes']) && isset($node['attributes']['HREF']) && strpos($node['attributes']['HREF'], 'instagram.com') !== false) {
//                             $result .= convertInstagram($node['attributes']['HREF']);
//                             break;
//                         }
// #                        $message = "Invalid Link";
// #                        debugelton($this->current_id, $this->current_url, $message, $node);

//                         break;
//                     case 'SUP':
//                         $result .= ' ^'.$node['value'].'^';
//                         break;
//                     case 'EM':
//                     case 'STRONG':
//                         $value = '<'.$node['tag'].'>'.$node['value'].'</'.$node['tag'].'>';
//                         $str = $this->converter->convert($value);
//                         $result .= ' '.$str;
//                         break;
//                     case 'H2':
//                         $value = '<'.$node['tag'].'>'.$node['value'].'</'.$node['tag'].'>';
//                         $str = $this->converter->convert($value);
//                         $result .= ' '.$str;
//                         break;
//                     case 'H3':
//                         $value = '<'.$node['tag'].'>'.$node['value'].'</'.$node['tag'].'>';
//                         $str = $this->converter->convert($value);
//                         $result .= ' '.$str;
//                         break;
//                     case 'H4':
//                         $value = '<'.$node['tag'].'>'.$node['value'].'</'.$node['tag'].'>';
//                         $str = $this->converter->convert($value);
//                         $result .= ' '.$str;
//                         break;
//                     case 'H5':
//                         $value = '<'.$node['tag'].'>'.$node['value'].'</'.$node['tag'].'>';
//                         $str = $this->converter->convert($value);
//                         $result .= ' '.$str;
//                         break;
//                     case 'LI':
//                         $str = '* '.$node['value'];
//                         $result .= ' '.$str;
//                         break;
//                     case 'IMG':
//                         if (isset($node['attributes']) && $node['attributes']) {
//                             $url = isset($node['attributes']['ORIGINAL-SET']) ? $node['attributes']['ORIGINAL-SET'] : $node['attributes']['SRC'];
//                             if (!in_array($url, $assets)) {
//                                 $assets[] = $url;
//                                 $img = "<img src='".$url."'>";
//                                 $result .= ' '.$this->converter->convert($img);
//                             }
//                         }
//                         else {
//                             $message = " === Hier fehlt ein Bild === ";
//                             debugelton($this->current_id, $this->current_url, $message, $node);
//                         }
//                         break;
//                     case 'BLOCKQUOTE':
//                         if (!isset($node['attributes']))
//                             break;

//                         $attributes = $node['attributes'];
//                         switch ($attributes['CLASS']) {
//                             case 'instagram-media':
//                                 $result .= convertInstagram($attributes['DATA-INSTGRM-PERMALINK']);
//                                 break;
//                             case 'tiktok-embed':
//                                 $result .= convertOembed($attributes['CITE'], $attributes['CLASS']);
//                                 break;
//                             case 'twitter-tweet':
//                                 $twitter = getTwitter($idx, $nodes);
//                                 if (is_array($twitter)) {
//                                     $nodes_to_ignore[] = $twitter['nodes_to_ignore'];
//                                     $result .= ' ' . $twitter['twitter'];
//                                     break;
//                                 }

//                                 $message = "Broken BLOCKQUOTE:" . $attributes['CLASS'];
//                                 debugelton($this->current_id, $this->current_url, $message, $node);
//                                 break;
//                             default:
//                                 // Let's try to extract quotes.
//                                 if (strpos($attributes['CLASS'], 'wp-block-quote') !== false) {

//                                     $next = $idx+1;
//                                     $nodes_to_ignore[] = $next;
//                                     $blockquote = $this->converter->convert($nodes[$next]['value']);

//                                     $next = $next+1;
//                                     $nodes_to_ignore[] = $next;
//                                     $next = $next+1;
//                                     $nodes_to_ignore[] = $next;
//                                     $next = $next+1;
//                                     $nodes_to_ignore[] = $next;
//                                     $cite = $this->converter->convert($nodes[$next]['value']);
//                                     $result ."+++pullquote\n\n" . $blockquote . "\n\n+++\n\n> " . $cite;

//                                     break;
//                                 }
//                                 $message = 'Unknown BLOCKQUOTE::'.$attributes['CLASS'];
//                                 debugelton($this->current_id, $this->current_url, $message, $node);
//                                 break;
//                         }
//                         break;
//                     case 'IFRAME':
//                         $attributes = $node['attributes'];
//                         if (isset($attributes['DATA-SRC']) && strpos($attributes['DATA-SRC'], 'open.spotify.com') !== false) {
//                             $result .= convertOembed($attributes['DATA-SRC'], 'spotify');
//                             break;
//                         }
//                         if (isset($attributes['DATA-SRC']) && strpos($attributes['DATA-SRC'], 'youtube.com') !== false) {
//                             $result .= convertOembed($attributes['DATA-SRC'], 'youtube-video');
//                             break;
//                         }
//                         $message = 'Unknown IFRAME::';
//                         debugelton($this->current_id, $this->current_url, $message, $node);
//                         break;
//                     case 'SECTION':
//                     case 'FIGCAPTION':
//                     case 'ROOT':
//                     case 'FIGURE':
//                     case 'I':
//                     case 'G':
//                     case 'OL':
//                     case 'UL':
//                     case 'DIV':
//                     case 'CITE':
//                     case 'SVG': // svg; not supported by CoPilot
//                     case 'PATH': // svg; not supported by CoPilot
//                     case 'NOSCRIPT':
//                     case 'SCRIPT':
// #                        debugelton($this->current_id, $this->current_url, "Irrelevant node:", '');
//                         break;
//                     default:
//                         $message = 'Unknown node';
//                         debugelton($this->current_id, $this->current_url, $message, $node);
//                         break;
//                 }
//             }
//         }
//         $result = preg_replace('/\s+/', ' ', $result);
//         $result = trim($result);
//         return $result;
//     }

protected function convert($content) {

    $content = '<root>'.$content.'</root>';

    $tidy = new tidy();
    $tidy_config = ['input-xml' => true, 'output-xml' => true, 'indent' => true];
    $content = $tidy->repairString($content, $tidy_config, 'utf8');

    $p = xml_parser_create();
    xml_parse_into_struct($p, $content, $nodes, $index);

    $result = '';
    $assets = [];
    $nodes_to_ignore = [];
#        var_dump($content);
#        var_dump($nodes);
    foreach ($nodes as $idx => $node) {
        if (in_array($idx, $nodes_to_ignore))
            continue;

            
            if (!empty($node['value']) || 
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
                    $text = $this->converter->convert(filterRubbish($node['value']));
                    $result .= $text;
                    break;
                    // $value = '<H5>'.$node['value'].'</H5>';
                    // $str = $this->converter->convert($value);
                    // $result .= $str;
                    // break;
                case 'BR':
                    $text = $this->converter->convert("<br>");
                    $result .= "
                    ";
                    
                    break;
                case 'A':#
                    // the simple link
                    if (trim($node['value']) && isset($node['attributes']) && isset($node['attributes']['HREF']) && $node['attributes']['HREF']) {
                        $href = "<a href='".$node['attributes']['HREF']."'>".trim($node['value'])."</a>";
                        $result .= ' '.$this->converter->convert($href);
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
                    $result .= ' ^'.$node['value'].'^';
                    break;
                case 'EM':
                case 'STRONG':
                    $value = '<'.$node['tag'].'>'.$node['value'].'</'.$node['tag'].'>';
                    $str = $this->converter->convert($value);
                    $result .= '
                    '.$str;
                    break;
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
                    $result .= ' '.$str;
                    break;
                case 'IMG':
                    if (isset($node['attributes']) && $node['attributes']) {
                        $url = isset($node['attributes']['ORIGINAL-SET']) ? $node['attributes']['ORIGINAL-SET'] : $node['attributes']['SRC'];
                        if (!in_array($url, $assets) && $url != '') {
                            $assets[] = $url;
                            $img = "<img src='".$url."'>";
                            $result .= ' '.$this->converter->convert($img);
                            // $this->img_cnt++;
                            // $newNumber = str_pad($this->img_cnt, 3, "0", STR_PAD_LEFT);
                            // $result .= "
                            //  [#image: /photos/".$this->current_id.'-'.$newNumber."] 
                            //  ";
                            
                            // $this->img_arr[$newNumber] = $url;
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
                case 'ROOT':
                case 'FIGURE':
                case 'I':
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
    $result = trim($result);
    return $result;
}

    protected function getImage($node) {

        if (!isset($node['id']) || !isset($node['media_details']['sizes']['full']))
            return null;

        $image = [
            'type' => 'photo',
            'id' => (string) $node['id'],
            'credit' => (isset($node['media_details']['image_meta'])) ? $this->converter->convert($node['media_details']['image_meta']['credit']) : [],
            'filename' => $node['media_details']['sizes']['full']['file'],
            'url' => $node['media_details']['sizes']['full']['source_url'],
            'tags' => [],
            'caption' => $this->converter->convert($node['caption']['rendered']),
            'title' => $this->converter->convert($node['title']['rendered'])
        ];

        return $image;
    }

}

