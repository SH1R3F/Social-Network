<?php
class Validation{
  private $_errors = array(),
          $_passed = false;

  public function check($source, $items = array()){
    if(count($items)){
      foreach($items as $item => $rules){
        $value = htmlspecialchars($source[$item]);
        foreach($rules as $rule => $rule_value){
          switch($rule){
            case "required":
              if(!strlen(str_replace(" ", "", $value))){
                $this->_errors[$item] = "This Field Is Required And Can't Be Left Empty.";
              }
            break;
            case "min":
              if(strlen($value) < $rule_value){
                $this->_errors[$item] = "This Can't Be Less Than {$rule_value} Characters.";
              }
            break;
            case "max":
              if(strlen($value) > $rule_value){
                $this->_errors[$item] = "This Can't Be More Than {$rule_value} Characters.";
              }
            break;
            case "regexp":
              if(!preg_match($rule_value, $value)){
                $this->_errors[$item] = "Please Enter A Valid Value.";
              }
            break;
            case "unique":
              $table = explode("->", $rule_value)[0];
              $colmn = explode("->", $rule_value)[1];
              if(DB::getInstance()->get('*', $table, array($colmn => $value))->count()){
                $this->_errors[$item] = "This Value Is Already In Use.";
              }
            break;
            case "matches":
              if($value !== htmlspecialchars($source[$rule_value])){
                $this->_errors[$item] = "This Value Doesn't Match {$rule_value}'s Value.";
              }
            break;
          }
        }
      }
    }
    if(empty($this->_errors)){
      $this->_passed = true;
    }
  }
  public function passed(){
    return $this->_passed;
  }

  public function errors(){
    return $this->_errors;
  }

}
