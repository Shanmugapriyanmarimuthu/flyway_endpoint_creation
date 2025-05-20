<?php

require('vendor/autoload.php');
require('config.php');
require('helpers.php');
require('gqprocessor.php');
set_time_limit(0);
$retry_count = 0 ;

function importJSON($cnt) {
    global $retry_count;
    $test_mode = false;
    $date_search =  false;
    $brand_URL = '';
    $type_URL = '';
    
    if(isset($_GET['mode']) && $_GET['mode'] == 'test'){
        $test_mode = true;
    }

    if(BASE_URL == ''){
        echo 'Brand Name Needed ...';exit;
    }
    if(isset($_GET['brand']) && $_GET['brand'] != ''){
        $brand = $_GET['brand'];
        
    }else{
        echo 'Brand Name Needed ...';exit;
    }

    if(isset($_GET['type']) && $_GET['type'] != ''){
        $type = $_GET['type'];
        
    }else{
        echo 'Type Name Needed ...';exit;
    }

    if(isset($_GET['cat']) && ($_GET['cat'] == 'GW' || $_GET['cat'] == 'BW')){
        
    }else{
        echo 'Category Name Needed ...';exit;
    }

    if($brand == 'vogue') {
        $brand_URL = VOGUE_BASE_URL;
    }elseif ($brand == 'gq') {
        $brand_URL = GQ_BASE_URL;
    }

    if($type == 'product') {
        $domain = API_URL_PRODUCTS;
    }elseif ($type == 'product_col') {
        $domain = API_URL_PRODUCT_COLLECTION;
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
        $max = 10;
        $url = $domain.'?per_page='.$max.'&page='.$start."&categories=29720";

      
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
                if($_GET['cat']=='GW') {
                   $cat_id = "53299";
                //    $record['category_ids'] = ['53299'];
                }else if($_GET['cat'] == 'BW'){
                    $cat_id = "53479";
                    // $record['category_ids'] = ['53479'];
                }
                
                if (isset($record['category_ids']) && (in_array($cat_id,$record['category_ids']) || in_array($cat_id,$record['category_ids']))) {

                if (isset($record['id']))
                // $record['id'] = '281987';
                $fetch_url = $domain.$record['id'];

                
                $jsonData = fetchJsonFromUrl($fetch_url);
                if($cat_id == '53479'){
                    $folder_name = 'bingewatch';

                 }else if($cat_id == '53299'){
                    $folder_name = 'gqwardrobe';
                 }

                $workStatus = true;
                if($date_search){
                    $workStatus = false;
                    $jsonDate = date('d-m-Y',strtotime($jsonData['date']));
                    $jsonDate = new DateTime($jsonDate);  
                }

                if ($date_search && (($jsonDate >= $fromDate) && ($jsonDate <= $toDate))){
                    $workStatus = true;
                }

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
                  
                    if ($key === 'body' && $inlineValue !== null) {
                        $newData['inline'] = $inlineValue;
                    }
 
                    $newData[$key] = $value;
                }


                if(!$test_mode){
                $folderpath = "GQ_BW_JSON_FILES/WITHOUTTAGS_".date('dmY').'_'.$folder_name."/";
                $filePath = $folderpath.$folder_name.'_'.$record['id'].'.json';
                if (!file_exists($folderpath)) {
                    mkdir($folderpath, 0777, true);
                }


                $jsonData = json_encode($newData, JSON_PRETTY_PRINT);

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
                echo "<div>==============================================================================</div>";
                echo json_encode($result, JSON_PRETTY_PRINT);
                echo "<div>==============================================================================</div>";
            }
        }
        flush();
    // }
            }
        }
        }
        echo $url." This URL JSON COnverted Completed... </br>";
        echo "<div>==============================================================================</div>";
        

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
