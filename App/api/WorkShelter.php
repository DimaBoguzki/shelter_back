<?php
require_once __DIR__.'/../DataApi.php';


use Ps\Ps\ShelterWork;
use Ps\Ps\ShelterWorkQuery;
use Ps\Ps\ShelterQuery;



class WorkShelter extends DataApi{
  function __construct(?object $params) {
    parent::__construct($params);

  }

  public function getItems(){
    if($this->role!=RoleType::SUPER_USER && $this->role!=RoleType::SHELTERS_MANAGER)
      return DataApi::Response(NULL, 'אין הרשאה');
    $items=ShelterWorkQuery::create()
      ->setFormatter('\Propel\Runtime\Formatter\ArrayFormatter')
      ->joinWith('Shelter')
      ->find()
      ->toARRAY();
    return DataApi::Response($items, NULL);
  }
  public function getWorksByShelterId(){
    if($this->role!=RoleType::SUPER_USER && $this->role!=RoleType::SHELTERS_MANAGER)
      return DataApi::Response(NULL, 'אין הרשאה');

    $items=ShelterWorkQuery::create()
    ->setFormatter('\Propel\Runtime\Formatter\ArrayFormatter')
    ->filterByShelterId($this->getParams()->shelterId)
    ->joinWith('Shelter')
    ->find();
    return DataApi::Response($items->toARRAY(),NULL);
  }
  public function getItemById(){
    if($this->role!=RoleType::SUPER_USER && $this->role!=RoleType::SHELTERS_MANAGER)
      return DataApi::Response(NULL, 'אין הרשאה');

    $item=ShelterWorkQuery::create()->findOneById($this->getParams()->id);
    if(!empty($item))
      return DataApi::Response($item->toARRAY(),NULL);
    else
      return DataApi::Response(NULL,"NOT EXIST");
  }
  public function insertItem(){
    if($this->role!=RoleType::SUPER_USER && $this->role!=RoleType::SHELTERS_MANAGER)
      return DataApi::Response(NULL, 'אין הרשאה');

    $shekter=ShelterQuery::create()->findOneById($this->getParams()->shelterId);
    if(!empty($shekter)){
      $now = new DateTime();
      $work=new ShelterWork();
      $work->setShelterId($shekter->getId());
      $work->setDate($now->format('Y-m-d'));
      $work->setTime($now->format('H:i:s'));
      $work->save();
      $work=$work->toARRAY();
      $work['Shelter']=$shekter->toARRAY();
    }
    return DataApi::Response($work, NULL);
  }
  public function updateItem(){
    if($this->role!=RoleType::SUPER_USER && $this->role!=RoleType::SHELTERS_MANAGER)
      return DataApi::Response(NULL, 'אין הרשאה');

    $params=$this->getParams();
    $item=ShelterWorkQuery::create()->findOneById($params->id);

    if(!empty($item)){
      $fieldName = 'set'.$params->field;
      $item->$fieldName(isset($params->value) ? $params->value : NULL);
      if($item->save())
        return DataApi::Response($item->toARRAY(), NULL);
      else
        return DataApi::Response(NULL, "המידע לא נשמר");
    }
    return DataApi::Response(NULL,"id not exist");
  }
}

