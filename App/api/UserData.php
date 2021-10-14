<?php
require_once __DIR__.'/../DataApi.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

use Ps\Ps\User;
use Ps\Ps\UserQuery;
use Ps\Ps\Role;
use Ps\Ps\RoleQuery;


class UserData extends DataApi {
  function __construct(?object $params) {
    parent::__construct($params);
  }
  public function getItems(){
    $users=UserQuery::create()->select(array("Id","Name","Phone","Mail","RoleId"))->find()->toARRAY();
    return DataApi::Response($users, null);
  }
  public function getItemById(){
    $user=UserQuery::create()->select(array("Id","Name","Phone","Mail","RoleId"))->findOneById($this->getParams()->id);
    if(!empty($user)){
      return DataApi::Response($user,'');
    }
    else
      return DataApi::Response(null, 'User not exist');
  }
  public function getCode(){
    $params=$this->getParams();
    $em=UserQuery::create()->findOneByMail($params->mail);
    if(!empty($em)){
      if($em->getPassword()==$params->password){
        $code=$this->senCodeTodMail($em->getMail());
        $em->setCode($code);
        $em->save();
        $res=new stdClass;
        $res->Code="Ok";
        return DataApi::Response($res,null);
      }
      else
        return DataApi::Response(null,"password is incorrect");
    }
    else
      return DataApi::Response(null,"user not exist");

  }
  private function senCodeTodMail($mail){
    $mail = new PHPMailer(true);
    try {
      //Server settings
      $mail->isSMTP();
      $mail->Host = 'smtp.live.com';
      $mail->SMTPAuth   = true;
      $mail->Username   = 'dimaboguzki@hotmail.com';
      $mail->Password   = 'dimaa1234Q';
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Port       = 587;

      //Recipients
      $mail->setFrom('dimaboguzki@hotmail.com', 'Shelters');

      $mail->addAddress('dimaboguzki@gmail.com');

      $code='';
      for($i=0;$i<6;$i++){
        $s=rand(0,9);
        $code.=strval($s);
      }
      //Content
      $mail->isHTML(true);
      $mail->Subject = 'You recive miklatim code';
      $mail->Body    = $code;
      $mail->AltBody = '';
      if(strlen($code) > 0){
        $mail->send();
        return $code;
      }
      return -1;
    }
    catch (Exception $e) {
      echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
      return -1;
    }
  }
  public function checkCode() {
    $params=$this->getParams();
    $em=UserQuery::create()->findOneByMail($params->email);
    if(!empty($em)){
      if($em->getPassword()==$params->pass){
        if($em->getCode()==$params->code){
          $role=RoleQuery::create()->findOneById($em->getRoleId());
          if(!empty($role)){
            $key = 'superDuper'.$em->getMail().'@'.$em->getName();
            $signature = hash_hmac('sha256', $em->getPassword(), $key);
            $em->setToken($signature);
            $em->save();
          }
          $obj=new stdClass;
          $obj->Id=$em->getId();
          $obj->Name=$em->getName();
          $obj->Mail=$em->getMail();
          $obj->Role=!empty($role) ? $role->getType() : null;
          $obj->Token=$em->getToken();
          $em->setCode(NULL);
          $em->save();
          return DataApi::Response($obj, null);
        }
        else {
          return DataApi::Response(NULL, "code is incorrect");
        }
      }
      else
        return DataApi::Response(NULL, "password is incorrect");
    }
    else
      return DataApi::Response(NULL, "use not exist");
  }


  /**
   * Admin medoth
   */
  public function getItemsAdmin(){
    if($this->role!=RoleType::SUPER_USER)
      return DataApi::Response(null, 'אין הרשאה');

    $res=new stdClass();
    $res->Users=UserQuery::create()->select(array("Id","Name","Phone","Mail",'Password',"RoleId"))->find()->toARRAY();
    $res->Roles=RoleQuery::create()->find()->toArray();
    return DataApi::Response($res, null);
  }
  public function getItemByIdAdmin($id){
    if($this->role!=RoleType::SUPER_USER)
      return DataApi::Response(null, 'אין הרשאה');

    $userQuery=UserQuery::create()->findOneById($id);
    if(!empty($userQuery)){
      $user=$userQuery->toARRAY();
      $role=RoleQuery::create()->findOneById($user['RoleId']);
      $user['Role'] = !empty($role) ? $role->toARRAY() : NULL;
      return DataApi::Response($user,NULL);
    }
    else
      return DataApi::Response(NULL, 'User not exist');
  }
  public function insertItem(){
    if($this->role!=RoleType::SUPER_USER)
      return DataApi::Response(null, 'אין הרשאה');

    $params=$this->getParams();
    $mail=trim($params->mail);
    $user=UserQuery::create()->findOneByMail($mail);
    if(empty($user)){
      $user=new User();
      $user->setName($params->name);
      $user->setPhone($params->phone);
      $user->setMail($mail);
      $user->setPassword($params->password);
      $user->save();
      return DataApi::Response($user->toARRAY(), NULL);
    }
    else
      return DataApi::Response(NULL, 'מייל זה כבר קיים');
  }
  public function updateItem(){
    if($this->role!=RoleType::SUPER_USER)
      return DataApi::Response(null, 'אין הרשאה');
    
    $params=$this->getParams();
    $item = UserQuery::create()->findOneById($params->id);
    if(!empty($item)){
      $fieldName = 'set'.$params->field;
      $item->$fieldName(isset($params->value) ? $params->value : NULL);
      if($item->save())
        return DataApi::Response($item->toARRAY(), NULL);
      else
        return DataApi::Response(NULL, "המידע לא נשמר");
    }
    return DataApi::Response(NULL, "משתמש לא קיים");
  }
}
