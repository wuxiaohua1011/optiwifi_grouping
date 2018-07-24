<?php
class Key {
  public function __construct($comparable, $num){
    $this->comparable = $comparable;
    $this->num = $num;
  }
  public function toString(){
    return sprintf("KEY: MAC[%s] - Group #%s", $this->comparable->getMac(), $this->num);
  }

  public function isEqualTo($other){
    if(is_a($other, "Key")){
      return $this->comparable->getMac() == $other->comparable->getMac()
            && $this->num == $other->num;
    }
    return 0;
  }
}
 ?>
