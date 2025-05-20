<?php
set_time_limit(0);
$folderPath = '/Users/shanmugapriyanmac/Desktop/WordpressToFlyway-main/VOGUE_JSON_FILES/13052025wedding_wardrobe_collection_WNI';


if (!is_dir($folderPath)) {
    die("Folder does not exist.");
}

// $TAGS = file_get_contents('C:\xampp\htdocs\WordpressToFlyway-main\FINAL_JSON_FILES\1103LATESTWARDROBETAGS.json');
// $TAGS = file_get_contents('C:\xampp\htdocs\WordpressToFlyway-main\FINAL_JSON_FILES\1103LATESTBINGEWATCHTAGS.json');

// $TAGS = json_decode($TAGS,true);

$wo_folder_path = 'C:\xampp\htdocs\WordpressToFlyway-main\GQ_BW_JSON_FILES\WITHTAGS_imgcheck_26032025_bingewatch/';
$wo_file_name = 'bingewatch_';
// $wo_file_name ='gqwardrobe_';
$wo_create = false;

$files = glob($folderPath . '/*.{json}', GLOB_BRACE);

if (empty($files)) {
    die("No JSON found in the folder.");
}

function removeDuplicateSlugs($data) {
    $unique = [];
    $filteredData = [];

    foreach ($data as $item) {
        if (!isset($unique[$item['slug']])) {
            $unique[$item['slug']] = true;
            $filteredData[] = $item;
        }
    }
    
    return $filteredData;
}

$counter = 1;
// echo "<table><tbody><tr><td>JSON LINK</td><td>Article URL</td><td>Article ID</td></tr>";
foreach ($files as $file) {
    $jsonData = file_get_contents($file);

    $data = json_decode($jsonData, true);
    if ($data === null) {
        die("Error decoding JSON file.");
    }
    // $check_arr = ['220830','219609','219542','219409','219158','218229'];
    // $check_arr = ['236229','233805','251339','233089','272017','233369','260876','281832'];
//   if(in_array($data['id'],$check_arr)) {
    if(true) {
    if($wo_create){
    // //   Unset no_index
    if(isset($data['categories']['functional-tags'])){
        unset($data['categories']['functional-tags']);
    }

    // $data['publishHistory']['uri'] = str_replace('https://www.gqindia.com/' , '', $data['publishHistory']['uri']);
    // $data['categories']['functional-tags'][] = [
    //     'type' => 'category',
    //     'slug' => 'noindex',
    //     'name' => '_noindex'
    // ];
    }






// CSV CREATION
    // $CSVFP = 'bingewatch_article_IDs_with_link.csv';

    // if (!file_exists($CSVFP) || filesize($CSVFP) == 0) {

    //     $file = fopen($CSVFP, 'w');
    //     if ($file === false) {
    //         die('Error opening the file for writing.');
    //     }
    //     $headers = ['Article ID','LINK'];
    //     fputcsv($file, $headers);
    //     fputcsv($file, [$data['id'],$data['self']]);
    //     fclose($file);

    // } else {
    //     $file = fopen($CSVFP, 'a');
    //     if ($file === false) {
    //         die('Error opening the file for reading.');
    //     }

    //     fputcsv($file, [$data['id'],$data['self']]);
    //     fclose($file);
    // }



    // if (count($data['contributorsAuthor']) > 0 ){
    //     print_r("ID : ".$data['id']."<br>");
    //     print_r(" Link : ".$data['self']."<br>");
    //     print_r("Publish History URL : ". $data['publishHistory']['uri']."<br>");
    //     echo "<div>================================================================================================</div>";
    // }

// print_r("<br>");

// echo "<tr><td>https://dev1.vogue.in/JSON_FILES/wedding_wardrobe/wedding_wardrobe_collection_".$data['id'].".json</td><td>".$data['self']."</td><td>".$data['id']."</td></tr>";


    // Tags Added

    if(isset($TAGS[$data['self']])){
            $data['categories']['tags'] = $TAGS[$data['self']]['tags'];
        }
// Remove dublicate tags
  
    
    // Remove duplicates
    if(isset($data['categories']['tags'])){
        $filteredData = removeDuplicateSlugs($data['categories']['tags']);
        $data['categories']['tags'] = $filteredData;
    }
    

    
    
    $error = false;
    if(empty($data['hed'])){
        print_r("Heading Missing...<br>");
        $error = true;
    }
    if(empty($data['body'])){
        print_r("Body Content Missing...<br>");
        $error = true;
    }
    if(empty($data['dek'])){
        print_r("Dek Content Missing...<br>");
        $error = true;
    }
    if(empty($data['seoTitle'])){
        print_r("seoTitle Missing...<br>");
        $error = true;
    }
    if(empty($data['seoDescription'])){
        print_r("seoDescription Missing...<br>");
        $error = true;
    }
    // if(isset($data['categories']['sections'])&&count($data['categories']['sections']) > 1) {
        
    //     print_r("Additional Category Added...<br>");
    //     print_r($data['categories']['sections']);
    //     print_r("<br>");
    //     $error = true;
        
    // }
    // print_r('https://dev1.vogue.in/products/sample/gqwardrobe_'.$data['id'].'.json <br>');
    // if(count($data['inline']) == 0){
    //     print_r("Inline images are empty...<br>");
    //         $error = true;
    // }
    foreach ($data['inline'] as $key => $value) {
        $count = substr_count($value["url"], "https:");
        if($count > 1){
            print_r($data['id']." This Article .".$key."th Inline Images Had Multiple URLs. That URL is : ".$value['url']."...<br>");
            
            preg_match('/^(\S+)/', $value['url'], $matches);
            $first_url = $matches[1];
            $data['inline'][$key]['url'] = $first_url;
            $data['inline'][$key]['filename'] = basename($first_url);
            $data['inline'][$key]['title'] = pathinfo($first_url, PATHINFO_FILENAME);
            $error = true;
        }

        if(strpos($value["url"], 'creator.gqindia.com')){
            if(strpos($value["url"], 'creator.gqindia.com')){
                $final_url =  str_replace('creator.gqindia.com' , 'media.gqindia.com', $value["url"]);
             }
            $data['inline'][$key]['url'] = $final_url;
            print_r("Inline image URL HAVE Creator Category Added...<br>");
            $error = true;
         }
         if(strpos($value["url"], 'author.gqindia.com')){
            if(strpos($value["url"], 'author.gqindia.com')){
                $final_url =  str_replace('author.gqindia.com' , 'media.gqindia.com', $value["url"]);
             }
            $data['inline'][$key]['url'] = $final_url;
            print_r("Inline image  URL HAVE Author Category Added...<br>");
            $error = true;
          }

          if (preg_match('/\.webp($|\?)/i', $value['url'])) {
            print_r($value['url']."<br>");
          }

        //   print_r($value['url']."<br>");
        //    Image URL Check
    //     $headers = @get_headers($value["url"]);
    // if( !($headers && strpos($headers[0], '200') !== false)) {
    //     print_r($data['id']." This Article .".$key."th Inline images are not open. That URL is : ".$value['url']."...<br>");
    //     $data['inline'][$key]['url'] = 'https://assets.gqindia.com/photos/6448cf0e0d73cc36bdb7895a/';
    //     $data['inline'][$key]['filename'] =basename(parse_url('https://assets.gqindia.com/photos/6448cf0e0d73cc36bdb7895a/', PHP_URL_PATH));
    //     $data['inline'][$key]['caption'] = 'GQ India';
    //     $data['inline'][$key]['title'] = 'GQ India';
    //     $error = true;
    // };


        
    }
    // if( substr_count($data['photosTout'][0]['url'], "https:") > 1 ){
    //     print_r($data['id']." This Article .photosTout Images Had Multiple URLs...<br>");
    //         $error = true;
    // }
    // if(!isset($data['photosTout'][0]) && $data['photosTout'][0] == 0){
    //     print_r("Photos Missing...<br>");
    //     $error = true;
    // }

    // if(strpos($data['photosTout'][0]['url'], 'creator.gqindia.com')){
    //     print_r("ULImage URL HAVE Creator Category Added...<br>");
    //     $error = true;
    //  }
    //  if(strpos($data['photosTout'][0]['url'], 'author.gqindia.com')){
    //     print_r("ULImage URL HAVE Author Category Added...<br>");
    //     $error = true;
    //   }
    //   print_r($data['photosTout'][0]['url']."<br>");
    if(!isset($data['photosTout'][0]['url'])){
            print_r("Article Image Missing ......<br>");
        $error = true; 
    }
      //    Image URL Check
    //   $outheaders = @get_headers($data['photosTout'][0]['url']);
    //   if( !($outheaders && strpos($outheaders[0], '200') !== false)) {
    //       print_r($data['id']."  Article images are not open. That URL is : ".$data['photosTout'][0]['url']."...<br>");

    //       $error = true;
    //   };
    if($error){
        print_r("ID : ".$data['id']."<br>");
        print_r(" Link : ".$data['self']."<br>");
        print_r("Publish History URL : ". $data['publishHistory']['uri']."<br>");
        if($wo_create) {
            $filePath = $wo_folder_path.$wo_file_name.$data['id'].'.json';
            if (!file_exists($wo_folder_path)) {
                mkdir($wo_folder_path, 0777, true);
            }
        
        // Encode JSON back and save it
            file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
        
        // echo "<div>================================================================================================</div>";
        // echo "<tr><td>https://dev1.vogue.in/JSON_FILES/wedding_wardrobe/wedding_wardrobe_collection_".$data['id'].".json</td><td>".$data['self']."</td><td>".$data['id']."</td></tr>";

    }else {
        echo $data['id']."    JSON files are Formatted Correctly. <br><div>================================================================================================</div>";
    }
    
    
if($wo_create) {
    $filePath = $wo_folder_path.$wo_file_name.$data['id'].'.json';
    if (!file_exists($wo_folder_path)) {
        mkdir($wo_folder_path, 0777, true);
    }

// Encode JSON back and save it
    file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

}

}
// echo "</tbody></table>";


exit;
?>
