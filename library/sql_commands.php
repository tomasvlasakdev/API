<?php

// SQL Commands
function sql_commands($type) {
  $sum = get_data("SELECT COUNT(*) FROM DATA_API_HOUSING");

  if($type == 1) {
    $total_records = $sum[0]["COUNT(*)"];
    echo $total_records;
    return;
  }
  elseif($type == 2) {
    $median_val = get_data("SELECT value FROM DATA_API_HOUSING WHERE measure = 'median'");
    $avg_val = 0;
    foreach ($median_val as $key => $value) {
      $avg_val += $value["value"];
    }
    $avg = $avg_val / $sum[0]["COUNT(*)"];
    echo round($avg);
    return;
  } 
  elseif($type == 3) {
    $mean_val = get_data("SELECT value FROM DATA_API_HOUSING WHERE measure = 'mean'");
    $avg_val_mean = 0;
    foreach ($mean_val as $key => $value) {
      $avg_val_mean += $value["value"];
    }
    $avg_mean = $avg_val_mean / $sum[0]["COUNT(*)"];  
    echo round($avg_mean);
    return;
  } 
  elseif($type == 4) {
    $sales_sum = get_data("SELECT COUNT(value) FROM DATA_API_HOUSING WHERE measure = 'sales'");
    $number_of_sales = $sales_sum[0]["COUNT(value)"];
    echo $number_of_sales;
    return;
  }
  elseif($type == 5) {
    $db_size = get_data("SELECT round((data_length + index_length) / 1024 / 1024, 2) AS velikost_MB FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'DATA_API_HOUSING'");
    $db_size_MB = $db_size[0]["velikost_MB"];
    echo $db_size_MB;
    return;
  }
  elseif($type == 6) {
    $latest_import = get_data("SELECT MAX(imported_at) FROM DATA_API_HOUSING");
    $date_string = $latest_import[0]["MAX(imported_at)"];
    $date_object = new DateTime($date_string);
    $formatted_latest_import = $date_object->format('j. n. Y H:i:s');
    echo $formatted_latest_import;
    return;
  }
}

?>