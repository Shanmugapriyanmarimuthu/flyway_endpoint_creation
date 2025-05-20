<?php

require('vendor/autoload.php');
require('config.php');
require('helpers.php');
require('vogueprocessor.php');
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
    }elseif ($type == 'product_col') {
        $domain = API_URL_PRODUCT_COLLECTION;
        $cat_type = 'product_collection';
        $folder_name = 'vogue_product_collection';
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
        $url = 'https://dev1.vogue.in/test/details.php?type='.$cat_type.'&cat_id=18664&per_page='.$max.'&page='.$start;
        // $IDS = '1155680,1155734,1140982,1186654,1201188,1155686,1205619,1171666,1171845,1144803,1072171,1203496,1155689,1196537,1144499,1199371,1155679,1156116,1155733,1197293,1159385,1158516,1205714,1087218,1203080,1076674,1070575,1073374,1074101,1074118,1148375,1155688,1111187,1175855,1146988,1148234,1155690,1144808,1138501,1118689,1155676,1155692,1206157,1204004,1204161,1203849,1086778,1096740,1098139,1155681,1155736,1139497,1155678,1204461,1156095,1155732,1153967,1192077,1155677,1195864,1155731,1204416,1196714,1205626,1199222,1165242,1163958,1146942,1155687,1190791,1145880,1122986,1146989,1155675,1155691,1196807,1087052,1203090';
        // $IDS = '1206892,1118125,1124417,1138543,1139491,1155579,1193654,1195423,1197589,1197704,1199425';
        //         $url = 'https://dev1.vogue.in/test/details.php?ID='.$IDS;
        // $url = $domain.'?per_page='.$max.'&page='.$start."&categories=29720";
        $url = 'https://dev1.vogue.in/test/details.php?&cat_id=18664&ID=1206157,1205714,1205619,1204461,1204416,1204161,1204004,1203849,1203496,1203090,1203080,1201188,1199371,1199222,1197293,1196807,1195864,1192077,1186654,1175855,1171845,1171666,1165242,1163958,1159385,1158516,1156095,1156116,1155736,1155687,1155734,1155688,1155731,1155686,1155691,1155732,1155733,1155690,1155689,1155692,1155680,1155681,1155677,1155675,1155678,1155679,1155676,1153967,1148375,1148234,1145880,1144803,1144808,1144499,1140982,1139497,1138501,1122986,1118689,1111187,1098139,1096740,1087218,1087052,1086778,1076674,1074118,1074101,1073374,1072171,1070575';

      
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
                    $folderpath = "VOGUE_JSON_FILES/Checking_prod_collection_".date('dmY').$folder_name."/";
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
