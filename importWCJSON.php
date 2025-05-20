<?php

require('vendor/autoload.php');
require('config.php');
require('helpers.php');
require('wcprocessor.php');
set_time_limit(0);
$retry_count = 0 ;

function importJSON($cnt) {
    global $retry_count;
    $test_mode = false;
    $date_search =  false;
    $brand_URL = '';
    $type_URL = '';
    $cat_type = '';
    
    if(isset($_GET['mode']) && $_GET['mode'] == 'test'){
        $test_mode = true;
    }

    if(BASE_URL == ''){
        echo 'Brand Name Needed ...';exit;
    }
    if(isset($_GET['brand']) && $_GET['brand'] != ''){
        $brand = $_GET['brand'];
        
    }else {
        echo 'Brand Name Needed ...';exit;
    }

    if(isset($_GET['cat_id']) && $_GET['cat_id'] != ''){
        
    }else {
        echo 'Category ID Needed ...';exit;
    }

    if(isset($_GET['type']) && $_GET['type'] != ''){
        $type = $_GET['type'];
        
    }else{
        echo 'Type Name Needed ...';exit;
    }
    $folder_name = '';
    if($brand == 'vogue') {
        $folder_name = 'vogue';
        $brand_URL = VOGUE_BASE_URL;
    }elseif ($brand == 'gq') {
        $brand_URL = GQ_BASE_URL;
    }

    if($type == 'product') {
        $domain = API_URL_PRODUCTS;
        $cat_type = 'product';
        $folder_name = 'vogue_product';
        if($_GET['cat_id'] == 60944){
            $folder_name = 'vogue_closet_product';
        }
        if($_GET['cat_id'] == 60945){
            $folder_name = 'wedding_wardrobe_product';
        }
    }elseif ($type == 'product_col') {
        $domain = API_URL_PRODUCT_COLLECTION;
        $cat_type = 'product_collection';
        $folder_name = 'vogue_product_collection';
        if($_GET['cat_id'] == 60944){
            $folder_name = 'vogue_closet_collection';
        }
        if($_GET['cat_id'] == 60945){
            $folder_name = 'wedding_wardrobe_collection';
        }
    }elseif ($type == 'post') {
        $domain = API_URL_POSTS;
    }

    $json_file_name = $brand.'_'.$type.'_noindex';
    $i = $cnt;
    $j = $cnt;
    if(isset($_GET['endpage']) && $_GET['endpage'] != ''){
        $j = $_GET['endpage'] ;
    }
    if(isset($_GET['from']) && isset($_GET['to']) && $_GET['from'] != '' && $_GET['to'] != ''){
        $fromDate = new DateTime($_GET['from']);  
        $toDate = new DateTime($_GET['to']);
        $date_search = true;
    }
    while ($i <= $j) {
        
        
        $start = $i;
        $max = 1000;
        $url = 'https://dev1.vogue.in/test/WW_VC_details.php?type='.$cat_type.'&cat_id='.$_GET['cat_id'].'&per_page='.$max.'&page='.$start;

        $url = 'https://dev1.vogue.in/test/WW_VC_details.php?type='.$cat_type.'&cat_id='.$_GET['cat_id'].'&ID=1064560,1069814,1072482,1073994,1075564,1075813,1076943,1078527,1078671,1093591,1101723';

        // 1099380

      
        $records = fetchJsonFromUrl($url);
        echo $url." This URL JSON COnverted Started... </br>";
        if (!is_array($records)) {
            if($retry_count < 3){
                echo 'breaking count is : '.$i.';retry_count is : '.$retry_count.'</br>';
                $retry_count = $retry_count + 1;
                sleep(5);
                importJSON($i);
                exit;
            }else{
                $retry_count = 0;
                break;
                exit;
            }
            
            
        }else if (is_array($records) && empty($records)) {

            if($retry_count < 3){
                echo 'breaking count is : '.$i.';retry_count is : '.$retry_count.'</br>';
                $retry_count = $retry_count + 1;
                sleep(5);
                importJSON($i);
                exit;
            }else{
                $retry_count = 0;
                break;
                exit;
            }

        }else{
            $retry_count = 0;
            $serial_no = 0;
            foreach ($records as $record) {
                $serial_no++;
                $msg = '';
                if (!isset($record['id'])) {
                    $i = 200;
                    break;
                }

                
                $jsonData = $record;

                $workStatus = true;
                if($date_search){
                    $workStatus = false;
                    $jsonDate = date('d-m-Y',strtotime($jsonData['date']));
                    $jsonDate = new DateTime($jsonDate);  
                }

                if ($date_search && (($jsonDate >= $fromDate) && ($jsonDate <= $toDate))){
                    $workStatus = true;
                }
                // if($record['id'] >= 1205592) {
                //     $workStatus = false;
                // }
                if ($workStatus){

                $processor = new Processor();
                $processor->data = $jsonData;

                $result = $processor->processData();
                $data = $result;
                if (isset($data['inline'])) {
                    $inlineValue = $data['inline'];
                    unset($data['inline']);
                } else {
                    $inlineValue = null;
                }

                $newData = [];
                foreach ($data as $key => $value) {
                    // When we reach the "body" key, insert the "inline" field first.
                    if ($key === 'body' && $inlineValue !== null) {
                        $newData['inline'] = $inlineValue;
                    }
                    // Then insert the current field.
                    $newData[$key] = $value;
                }

                if(!$test_mode){
                    $folderpath = "VOGUE_JSON_FILES/".date('dmY').$folder_name."_WNI/";
                    $filePath = $folderpath.$folder_name.'_'.$record['id'].'.json';
                    if (!file_exists($folderpath)) {
                        mkdir($folderpath, 0777, true);
                    }
    

                    $jsonData = json_encode($newData, JSON_PRETTY_PRINT | JSON_PARTIAL_OUTPUT_ON_ERROR);
    
                    if ($jsonData === false) {
                        die('Error encoding JSON: ' . json_last_error_msg());
                    }
    
                    if (file_put_contents($filePath, $jsonData)) {
                        $msg .= $record['id'].' json created ';
              
                    
                } else {
                    echo "Error writing JSON data to file.";
                }
                $msg .= '</br>';
                echo $msg;
            }else {
                // print_r($record['id']."<br>");
                echo "<div>==============================================================================</div>";
                echo json_encode($result, JSON_PRETTY_PRINT);
                echo "<div>==============================================================================</div>";
            }
        }   
            }
        }
        echo $url." This URL JSON COnverted Completed... </br>";
        echo "<div>==============================================================================</div>";
        flush();

        if($retry_count == 0 ){
            $i++;
            if(isset($_GET['endpage']) && $_GET['endpage'] != ''){
                $j = $_GET['endpage'] ;
            }else{
                $j++;
            }
        }

    }
}

$frompage = 1;
if(isset($_GET['frompage']) && $_GET['frompage'] != '' && $_GET['frompage'] > 0 ){
    $frompage = $_GET['frompage'] ;
}
$result = importJSON($frompage);
