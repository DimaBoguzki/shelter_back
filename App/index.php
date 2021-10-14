<?php
  header('content-type: application/json; charset=utf-8');
  header('Access-Control-Allow-Origin: *');
  header('Access-Control-Allow-Headers: Content-Type');
  header('Access-Control-Allow-Methods: POST');


  $method = $_SERVER['REQUEST_METHOD'];

  if (session_status() == PHP_SESSION_NONE)
    session_start();

  require_once __DIR__.'/../vendor/autoload.php';
  require_once __DIR__.'/../vendor/bin/generated-conf/config.php';


  class ClassAutoloader {
    public function __construct() {
      spl_autoload_register(array($this, 'loader'));
    }
    private function loader($className) {
      $file = __DIR__ . '/api/' . $className . '.php';
      // $file = str_replace('\\', DIRECTORY_SEPARATOR, $file);
      if (file_exists($file)) {
        include_once $file;
      }
    }
  }


  if ($method !== 'POST')
    die("Message: The requested resource does not support http method " . $method);

  $_POST=json_decode(file_get_contents("php://input"), true);

  $autoloader = new ClassAutoloader();

  $point=$_POST['point'];
  $action=$_POST['action'];
  $params=(object)$_POST['params'];

  if (!method_exists($point, $action))
    die('function not yet exist');
  $item = new $point($params);
  echo $item->$action();

?>
