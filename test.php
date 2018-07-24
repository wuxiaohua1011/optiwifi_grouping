<?php
   require_once('Comparable.php');
   require_once('Key.php');

   $dbhost = 'manage-sit.ce09tmgi8h1g.eu-west-1.rds.amazonaws.com';
   $dbuser = 'manual_access';
   $dbpass = '';
   $db = 'optiwifi';

   $con = mysqli_connect($dbhost, $dbuser, $dbpass, $db);

   if(! $con ) {
      die('Could not connect: ' . mysqli_error());
   }
   else {
     printAllMonitorBSSIDData($con);
   }

   mysqli_close($con);


   function printAllMonitorBSSIDData($con) {
    // test();
     $sql = "SELECT MB.*, B.ap_mac FROM monitor_bssid MB, bssid B WHERE MB.bssid_id = B.bssid_id GROUP BY B.ap_mac;";
     if ($result=mysqli_query($con,$sql)) {
       $G = grouping($result);
       // Free result set
       mysqli_free_result($result);

       printG($G);
     }
   }

function printG($G){
  foreach($G as $key => $group){
    if(sizeof($group) > 1){
      echo "[".$key."] : "."\n";
      foreach($group as $c){
          $format = "        Mac: [%s] | similarityScore: [%s] | Against: [%s]"."\n";
          echo sprintf($format, $c->mac, $c->similarityScore, $c->other->mac);
      }
      echo "\n";
    }
  }
}


function grouping($result) {
  $M = array();
  // Fetch one and one row
  while ($row=mysqli_fetch_row($result)) {
      // printf ("%s (%s) [%s] <%s> (%s) [%s] <%s> %s %s \n",
      // $row[0],$row[1],$row[2],$row[3],$row[4], $row[5], $row[6], $row[7], $row[8]);
      $c = new Comparable($row[0],
                          $row[1],
                          $row[2],
                          $row[3],
                          $row[4],
                          $row[5],
                          $row[6],
                          $row[7],
                          $row[8]);
      if(array_key_exists($c->getMonitorId(), $M)) {
        array_push($M[$c->getMonitorId()], $c);
      } else{
        $M[$c->getMonitorId()] = array($c);
      }
    }

    // print_r($M);
    // group by monitors
    $G = array();
    $temp = array();
    foreach($M as $key => $C){
      // array_push($G, sortSubGroup($C));
      $counter = 1;
      $temp[$key] = sortSubGroup($C);
    }
    $counter = 1;
    foreach($temp as $monitor_id => $monitor_list){
      foreach($monitor_list as $k => $v){
        $G["Group #".$counter] = $v;
        $counter += 1;
      }

    }
    // $temp looks like this:
    /*
    * Array
    *(
    *    [monitor_radio_id_1] => Array
    *        (
    *         [mac_address_1] => Array
    *           (
    *            [0]=>Comparable Object (...)
    *            [1]=>Comparable Object (...)
    *           )
    *
    *         [mac_address_2] => Array
    *           (
    *            [0]=>Comparable Object (...)
    *            [1]=>Comparable Object (...)
    *           )
    *        )
    *    [monitor_radio_id_2] => Array
    *        (
    *         [mac_address_3] => Array
    *           (
    *            [0]=>Comparable Object (...)
    *            [1]=>Comparable Object (...)
    *           )

    *         [mac_address_4] => Array
    *           (
    *            [0]=>Comparable Object (...)
    *            [1]=>Comparable Object (...)
    *           )
    *        )







    *)
    */

    // $G looks like this:
    /*
    * Array
    * (
    *   [Group #1] => Array
    *     (
    *         [0] => Comparable Object (...)
    *         [1] => Comparable Object (...)
    *     )
    *
    *   [Group #2] => Array(...)
    */
    return $G;
}

function sortSubGroup($C){
  $result = array();
  $KEYS = array();
  foreach($C as $c){
    $mac = $c->getMac();
    $appended = False;
    $key = new Key($c, $counter);
    if (array_key_exists($mac, $KEYS)) {
      // should not enter here, but just in case
      continue;
    }
    // $k is a string, look up the key it points to in the $KEY array
    foreach($KEYS as $k => $v) {
      $score = $c->similarityScoreAgainst($v->comparable);
      $numCharDiff = computeMacCharDiff($c, $v->comparable);
      $mac_hex_diff = computeMacHexDiff($c, $v->comparable);
      if(($numCharDiff < 3 || $mac_hex_diff < 8)){
        $appended = True;
        $c->setSimilarityScore($v->comparable);
        $c->other = $v->comparable;
        array_push($result[$k], $c);
      }
    }
    if(!$appended){
      $KEYS[$mac] = $key;
      $result[$mac] = array($c);
    }
  }
  return $result;
}
function test(){
  $c1 = new Comparable("1041","1272","1122","75037","6","-85.5","2018-07-20 18:16:05","2018-07-20 18:16:05","00:1f:45:f9:63:78");
  $c2 = new Comparable("1","1272","1122","75037","6","-85.5","2018-07-22 03:09:43","2018-07-20 12:05:05","00:1f:45:f9:63:79");
  // echo "num diff = ".findNumMacDiff($c1, $c2)."\n";
  echo "hex diff = ".computeMacHexDiff($c1, $c2)."\n";
}

 ?>
