<?php
require_once __DIR__.'/./db.php';
/**
 *  @ string $action func name to excute
 *  @ string ( object to string ) with  params  for action ect: user id, item id
 */
abstract class DataApi {
  protected ?object $params=null; // data
  protected $role=null;

  function __construct(?object $params) {
    $this->setParams(json_encode($params));
    if(isset($this->params->userId) && isset($this->params->token) && strlen($this->params->token) > 0)
      $this->setRole($this->params->userId, $this->params->token);
  }
  /*
  * All responce for clent trow from this func
  */
  static function Response($data, ?string $error) : string  {
    $res=new stdClass;
    $res->Error=$error;
    $res->Data=$data;
    return json_encode($res);
  }
  protected function getParams() : object{
    return $this->params;
  }
  /*
  * set params before we check the params
  */
  public function setParams($params) : void {
    $connection=new DB();
    $x=$connection->getConnection()->real_escape_string($params);
    $var = stripslashes($x);
    $var = strip_tags($var);
    $var = htmlentities($var, ENT_IGNORE, 'utf-8');
    $this->params=json_decode($var);
  }
  public function setRole(int $userId, string $token) : void{
    if(isset($token) && isset($userId)){
      $conn=new DB();
      $res=$conn->getConnection()->query("SELECT * FROM user a LEFT JOIN role b ON a.role_id=b.id WHERE a.id=$userId");
      $user=$res->fetch_assoc();
      if(!empty($user) && isset($user['token']) && strlen($user['token']) > 0){
        if($token==$user['token'] && isset($user['type'])){
          $this->role=$user['type'];
        }
      }
    }
  }
  public static function GetClientIp() : string {
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        return $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        return $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
        return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        return $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        return $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        return $_SERVER['REMOTE_ADDR'];
    else
      return 'UNKNOWN';
  }
  public static function CustomQuery(string $query) : object{
    $conn=new DB();
    $query=$conn->getConnection()->query($query);
    $res=new stdClass();
    $res->Error=null;
    $res->Data=null;
    if($query)
      $res->Data=$query->fetch_all(MYSQLI_ASSOC);
    else
      $res->Error=$conn->getError();
    return $res;
  }
}

abstract class RoleType {
  const SUPER_USER="super_user";
  const SHELTERS_MANAGER="shelters_manager";
  const SIMPLE="simple";
  const AUDITER="auditer";
}