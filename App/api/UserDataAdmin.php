<?php
require_once __DIR__.'/../DataApi.php';


use Ps\Ps\User;
use Ps\Ps\UserQuery;
use Ps\Ps\Role;
use Ps\Ps\RoleQuery;
use Ps\Ps\Auditer;
use Ps\Ps\AuditerQuery;

class UserDataAdmin extends DataAdminApi{
  function __construct(string $action, object $params) {
    parent::__construct($action, $params);
    $this->Request();
  }

  public function Request(){
    switch (parent::getAction()) {
      case 'getItems':
        echo $this->getItems();
        break;
      case 'getItemById':
        echo $this->getItemById(parent::getParams()->id);
        break;
      case 'insertItem':
        echo $this->insertItem(json_decode(parent::getParams()->user));
        break;
      case 'updateItem':
        echo $this->updateItem(parent::getParams()->id, parent::getParams()->params);
        break;
      case 'toggleAuditer':
        echo $this->toggleAuditer(parent::getParams()->id);
        break;
      case 'toggleRoleAdmin':
        echo $this->toggleRoleAdmin(parent::getParams()->id);
        break;
        die("Actions not exist");
        break;
    }
  }
  protected function getItems(){
    $users=UserQuery::create()
      ->leftJoinWith('Role')
      ->leftJoinWith('Auditer')
      ->find()->toARRAY();
    foreach ($users as &$user) {
      if(count($user['Auditers']) && $user['Auditers'][0]['Active']==1)
        $user['Auditers']=1;
      else
        $user['Auditers']=NULL;
      if(count($user['Roles']))
        $user['Roles']=1;
      else
        $user['Roles']=NULL;
    }
    return DataApi::Response($users, NULL);
  }
  protected function getItemById($id){
    $userQuery=UserQuery::create()->findOneById($id);
    if(!empty($userQuery)){
      $user=$userQuery->toARRAY();
      $role=RoleQuery::create()->findOneById($user['Id']);
      $auditer=AuditerQuery::create()->findOneById($user['Id']);
      $user['Role'] = !empty($role) ? $role->toARRAY() : NULL;
      $user['Auditer'] = !empty($auditer) ? $auditer->toARRAY() : NULL;
      return DataApi::Response($user,NULL);
    }
    else
      return DataApi::Response(NULL, 'User not exist');
  }
  protected function insertItem(?object $params){
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
  /*
  * $params --> {field to update and value}
  */
  protected function updateItem($id, $params){
    $params=json_decode($params);
    $item = UserQuery::create()->findOneById($id);
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
  protected function deleteItem($id){
  }
  protected function toggleAuditer($id){
    $auditer=AuditerQuery::create()->findOneByUserId($id);
    if(!empty($auditer)){
      $auditer->setActive( $auditer->getActive()==1 ? 0 : 1 );
    }
    else {
      $auditer=new Auditer();
      $auditer->setUserId($id);
      $auditer->setActive(1);
    }
    if($auditer->save())
      return DataApi::Response($auditer->toARRAY(), NULL);
    else
      return DataApi::Response(NULL, "המידע לא נשמר");
  }
  protected function toggleRoleAdmin($id){
    $role=RoleQuery::create()->findOneByUserId($id);
    if(!empty($role)){
      $role->delete();
    }
    else {
      $role=new Role();
      $role->setUserId($id);
      if(!$role->save())
        return DataApi::Response(NULL, "המידע לא נשמר");
    }
    return DataApi::Response("1", NULL);
  }
}

?>
