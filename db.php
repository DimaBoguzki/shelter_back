<?php

class db  {
   private $_conn;
   public function __construct() {
    $this->_conn = new mysqli('localhost','root','','test-magazine');
    if($this->_conn->connect_errno)
        die('Connected error!');
  }
  public function getConnection(){
    return $this->_conn;
  }
  public function query($query) : object {
    return $this->_conn->query($query);
  }
}

?>