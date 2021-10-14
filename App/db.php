<?php

class DB  {
  private $_conn;
  public function __construct() {
    $this->_conn = new mysqli('localhost','root','','shelters_db');
    if($this->_conn->connect_errno)
      die('Connected error!');
  }
  public function __destruct(){
    $this->_conn->close();
  }
  public function getConnection() {
    return $this->_conn;
  }
  public function query($query) {
    return $this->_conn->query($query);
  }
  public function getError(){
    return $this->_conn->error;
  }
}

?>
