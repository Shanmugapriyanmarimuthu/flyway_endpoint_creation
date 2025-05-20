<?php

require('vendor/autoload.php');
require('config.php');
require('helpers.php');
require('processor.php');
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
        
    }else {
        echo 'Brand Name Needed ...';exit;
    }

    if(isset($_GET['type']) && $_GET['type'] != ''){
        $type = $_GET['type'];
        
    }else{
        echo 'Type Name Needed ...';exit;
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
    }elseif ($type == 'post') {
        $domain = API_URL_POSTS;
    }

    $json_file_name = $brand.'_'.$type;
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
        $max = 100;
        $url = $domain.'?per_page='.$max.'&page='.$start."&categories=18664";
        // $url = $domain.'?per_page='.$max.'&page='.$start."&categories=29720";

      
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

                if (isset($record['id']))
                $fetch_url = $domain.$record['id'];

                
                // $jsonData = fetchJsonFromUrl($fetch_url);
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

                if ($workStatus){

                $processor = new Processor();
                $processor->data = $jsonData;

                $result = $processor->processData();
                if(!$test_mode){
                $filePath = $json_file_name.'_json/'.$record['id'].'.json';
                if (!file_exists($json_file_name.'_json/')) {
                    mkdir($json_file_name.'_json/', 0777, true);
                }


                $jsonData = json_encode($result, JSON_PRETTY_PRINT);

                if ($jsonData === false) {
                    die('Error encoding JSON: ' . json_last_error_msg());
                }

                if (file_put_contents($filePath, $jsonData)) {
                    $msg .= $record['id'].' json created ';

                        $CSVfilePath = $json_file_name.'_json_list.csv';

                        if (!file_exists($CSVfilePath) || filesize($CSVfilePath) == 0) {

                            $file = fopen($CSVfilePath, 'w');
                            if ($file === false) {
                                die('Error opening the file for writing.');
                            }
                            $headers = ['ID', 'Local Path','Page No','Serial No'];
                            fputcsv($file, $headers);
                            fputcsv($file, [$record['id'], $filePath,$start,$serial_no]);
                            fclose($file);
                            $msg .= $record['id'].' csv updated ';

                        } else {
                            $file = fopen($CSVfilePath, 'a');
                            if ($file === false) {
                                die('Error opening the file for reading.');
                            }

                            fputcsv($file, [$record['id'], $filePath,$start,$serial_no]);
                            fclose($file);
                            $msg .= ' & '.$record['id'].' csv updated ';
                        }
              
                    
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
