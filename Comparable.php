<?php
  class Comparable {

    public function __construct($monitor_bssid_id,
                                $monitor_id,
                                $monitor_radio_id,
                                $bssid_id,
                                $latest_channel,
                                $latest_rssi,
                                $updated_at,
                                $created_at,
                                $ap_mac) {
      $this->monitor_bssid_id = $monitor_bssid_id;
      $this->monitor_id = $monitor_id;
      $this->monitor_radio_id = $monitor_radio_id;
      $this->bssid_id = $bssid_id;
      $this->latest_channel = $latest_channel;
      $this->rss = $latest_rssi;
      $this->updated_at = $updated_at;
      $this->created_at = $created_at;
      $this->mac = $ap_mac;

      $this->similarityScore = -1;
      $this->other=NULL;
    }



    // getters
    public function getMac() {
      return $this->mac;
    }
    public function getRSS() {
      return $this->rss;
    }
    public function getSimilarityScore() {
      if ($this->similarityScore == NULL) {
        return -1;
      }
      return $this->similarityScore;
    }
    public function getMonitorId(){
      return $this->monitor_id;
    }
    // setters
    public function setMac($mac) {
      $this->$mac = $mac;
    }
    public function setRSS($rss) {
      $this->$rss = $rss;
    }
    public function setSimilarityScore($other) {
      $this->similarityScore = $this->similarityScoreAgainst($other);
    }

    //utility functions
    public function similarityScoreAgainst($other) {
      if(is_a($other, "Comparable")){
        $hex_diff = computeMacHexDiff($this, $other);
        $char_diff = computeMacCharDiff($this, $other);
        $mac_similarity_score = min($hex_diff, $char_diff);
        // $rss_similarity_score = computeRSSSimilarityScore($this, $other);
        // echo "rss_score = ".$rss_similarity_score."\n";
        // return convertNum(0, 200, $mac_similarity_score + $rss_similarity_score, 0, 100);
        return $mac_similarity_score;
      } else{
        throw new Exception("NOT Of Class Comparable");
      }
    }

    public function toString() {
      return "mac: ".$this->mac." | similarityScore: ".$this->similarityScore." | other: ".$this->other;

    }

    public function isEqualTo($other){
      if(is_a($other,"Comparable")) {
        return $other->getMac() == $this->mac;
      }
      return 0; //0 in php means false
    }
  }

  function computeMacCharDiff($A, $B) {
      if(is_a($A, "Comparable") && get_class($B) == "Comparable"){
        if(strlen($A_mac) != strlen($B_mac)){
          throw new Exception("A and B length does not match");
        }
        $A_mac = $A->mac;
        $B_mac = $B->mac;
        // . $A_mac."\n";
        // echo $B_mac."\n";
        $counter = 0;
        for($i = 0; $i < strlen($A_mac); $i++ ){
          $chara = substr($A_mac, $i, 1);
          $charb = substr($B_mac, $i, 1);
          if($chara != $charb) {
            $counter = $counter + 1;
          }
        }
      return $counter;
    } else{
      throw new Exception("type does not match");
    }
  }
  // function computeMacSimilarityScore($A, $B){
  //   if(is_a($A, "Comparable") && get_class($B) == "Comparable"){
  //     if(strlen($A_mac) != strlen($B_mac)){
  //       throw new Exception("A and B length does not match");
  //     }
  //     $A_mac = $A->mac;
  //     $B_mac = $B->mac;
  //
  //     // echo $A_mac."\n";
  //     // echo $B_mac."\n";
  //
  //     $A_mac_split = explode(":",$A_mac);
  //     $B_mac_split = explode(":",$B_mac);
  //
  //     $hex_score_total = 0;
  //     for ($i=0; $i < sizeof($A_mac_split); $i++) {
  //       $curr_diff= 1 - abs(hexdec($A_mac_split[$i]) - hexdec($B_mac_split[$i])) / 255.0;
  //       $hex_score_total += convertNum(0, 1, $curr_diff, 0, 100/6.0);
  //     }
  //     // echo "hex_score_total = ".$hex_score_total."\n";
  //
  //
  //     $counter = 0;
  //     for($i = 0; $i < strlen($A_mac); $i++ ){
  //       $chara = substr($A_mac, $i, 1);
  //       $charb = substr($B_mac, $i, 1);
  //       if($chara == $charb) {
  //         $counter = $counter + 1;
  //       }
  //     }
  //     $counter -= 5; // ex: A_mac = ae:22:15:2f:a6:ba, there are 5 ":", therefore, subtract by 5
  //     $cc_score_total = $counter / 12.0; // the length of the mac address is 12 excluding the :
  //     $cc_score_total = convertNum(0,1,$cc_score_total, 0, 100);
  //     // return convertNum(0,1,$score, 0, 100);
  //     // echo "cc_score_total = ".$cc_score_total."\n";
  //     return max($hex_score_total, $cc_score_total);
  //   } else{
  //     throw new Exception("Parameters not the same class or not of class Comparable");
  //   }
  // }

  function computeMacHexDiff($A, $B){
    if(is_a($A, "Comparable") && get_class($B) == "Comparable"){
      if(strlen($A_mac) != strlen($B_mac)){
        throw new Exception("A and B length does not match");
      }
      $A_mac = $A->mac;
      $B_mac = $B->mac;
      $A_mac_split = explode(":",$A_mac);
      $B_mac_split = explode(":",$B_mac);
      $hex_diff_byte = 0;
      $hex_diff_char_sum = 0;
      for ($i=0; $i < sizeof($A_mac_split); $i++) {
        $A_mac_byte = $A_mac_split[$i];
        $B_mac_byte = $B_mac_split[$i];

        $A_hex = hexdec($A_mac_byte);
        $B_hex = hexdec($B_mac_byte);

        $rAB = abs($A_hex - $B_hex);
        $rA255B = abs($A_hex+255+1 - $B_hex);
        $rAB255 = abs($A_hex - ($B_hex+255+1));
        $hex_diff_byte += computeHexDiff($A_hex, $B_hex);

        $A_mac_first_char_hex = hexdec(substr($A_mac_byte, 0, 1));
        $B_mac_first_char_hex = hexdec(substr($B_mac_byte, 0, 1));
        $A_mac_second_char_hex = hexdec(substr($A_mac_byte, 1, 1));
        $B_mac_second_char_hex = hexdec(substr($B_mac_byte, 1, 1));

        $hex_diff_char_sum = $hex_diff_char_sum + computeHexDiff($A_mac_first_char_hex, $B_mac_first_char_hex) + computeHexDiff($A_mac_second_char_hex, $B_mac_second_char_hex);
      }
      return min($hex_diff_byte, $hex_diff_char_sum);
    }
  }
  function computeRSSSimilarityScore($A, $B){
    if(is_a($A, "Comparable") && is_a($B, "Comparable")) {
      //TODO replace 5 with rss_tolearnce so that I can set it globally
      if(abs($A->rss - $B->rss) < 3) {
        return 100;
      } else{
        return convertNum(0,1, 3 / abs($A->rss - $B->rss), 0, 100);
      }
    }
  }
  function convertNum($oldMin, $oldMax, $oldValue, $newMin, $newMax){
   $oldRange = $oldMax - $oldMin;
   $newRange = $newMax - $newMin;
   $newVal = ((($oldValue - $oldMin) * $newRange) / $oldRange) + $newMin;
   return $newVal;
 }

 /**
 * Assuming that $hexA and $hexB are valid decimals respresenting a hexadecimal
 * $hexA - int ranging from 0 - 255 representing hexadecimal
 * $hexB - int ranging from 0 - 255 representing hexadecimal
 **/
  function computeHexDiff($hexA, $hexB){
    $rAB = abs($hexA - $hexB);
    $rA255B = abs($hexA+255+1 - $hexB);
    $rAB255 = abs($hexA - ($hexB+255+1));
    return min($rAB, $rA255B, $rAB255);
  }
 ?>
