<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST");

require_once __DIR__.'/db.php';

function saveImage($img) : object {
	$max_file_size=(5 * 1024 * 1024); // 5 mega
	$res = new stdClass();
	$uploadPath='/img/';
	// some error on upload file
	if($img['error'] > 0) {
		$res->error=1;
		$res->msg="Error with upload file";
		$res->url="";
		return $res;
	}
	// check if size file is valid
	if($img['size'] > $max_file_size){
		$res->error=1;
		$res->msg="size file is to big";
		$res->url="";
		return $res;
	}
	// check type of file, nly jpg and pgp is valid type file
	if((!strcmp($img['type'],'image/png')) || (!strcmp($img['type'],'image/jpeg'))){
		// save a file
		$imgName = __DIR__.$uploadPath."img_".$_POST['label'].(!strcmp($img['type'],'image/png')?'.png':'.jpg');
		if(move_uploaded_file($img['tmp_name'] , $imgName)){
			$res->error=0;
			$res->msg="upload Success";
			$res->url=$uploadPath."img_".$_POST['label'].(!strcmp($img['type'],'image/png')?'.png':'.jpg');
		}
		else { // move_uploaded_file fail
			$res->error=1;
			$res->msg="Error with upload file function move_uploaded_file";
			$res->url="";
		}
	}
	else { // type file is  invalid
		$res->error=1;
		$res->msg="type file error";
		$res->url="";
	}
	return $res;
}

$action = $_POST['action'];
$db = new db();

if(isset($action)){
  switch($action){
    case 'name':
      echo $_POST['name'];
    break;
    case 'phone':
      echo $_POST['phone'];
	  break;
	case 'img':
		echo json_encode(saveImage($_FILES['img']));
		break;
    break;
	
  }
}


?>