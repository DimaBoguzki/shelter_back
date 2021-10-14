<?php
require_once __DIR__.'/../DataApi.php';

use Ps\Ps\InspectionQuery;
use Ps\Ps\Inspection;
use Ps\Ps\ShelterQuery;

class InspectionData extends DataApi{
  function __construct(?object $params) {
    parent::__construct($params);
  }

  public function reportShelter(){
    $params=$this->getParams();
    $curDate = new DateTime();
    $date=$curDate->format('Y-m-d');
    $time=$curDate->format('H:i:s');
    $insp=new Inspection();
    $insp->setDate($date);
    $insp->setTime($time);
    $insp->setReport($params->report);
    $insp->setShelterId($params->shelterId);
    $insp->setUserId($params->userId);
    if($insp->save())
      return DataApi::Response($insp->toARRAY(),NULL);
    else
      return DataApi::Response(NULL,"Error");
  }

  /**
   * Admin
   */

  public function getItemsAdmin(){
    if($this->role!=RoleType::SUPER_USER && $this->role!=RoleType::SHELTERS_MANAGER)
      return DataApi::Response(NULL, 'אין הרשאה');

    $inspections=InspectionQuery::create()->find()->toARRAY();
    return DataApi::Response($inspections, NULL);
  }
  public function getItemsByShelterID(){
    if($this->role!=RoleType::SUPER_USER && $this->role!=RoleType::SHELTERS_MANAGER)
      return DataApi::Response(NULL, 'אין הרשאה');
    $data=new stdClass();
    $data->Inspections=null;
    $data->Shelter=ShelterQuery::create()->findOneById($this->getParams()->shelterId)->toARRAY();
    if(!empty($data->Shelter)){
      $data->Inspections=InspectionQuery::create()
          ->filterByShelterId($this->getParams()->shelterId)
          ->setFormatter('\Propel\Runtime\Formatter\ArrayFormatter')
          ->joinWith('User')
          ->find()
          ->toARRAY();
      foreach ($data->Inspections as &$ins) {
        $user=$ins['User'];
        unset($user['Mail']);
        unset($user['Password']);
        unset($user['Code']);
        unset($user['Code']);
        $ins['Auditer']=$user;
      }
      return DataApi::Response($data, NULL);
    }
    return DataApi::Response(NULL, 'לא נמצא מקלט');
  }
  public function getItemByIdAdmin($id){
    if($this->role!=RoleType::SUPER_USER && $this->role!=RoleType::SHELTERS_MANAGER)
      return DataApi::Response(NULL, 'אין הרשאה');

    $shelter= InspectionQuery::create()->findOneById($this->getParams()->id);
    if(!empty($shelter))
      return DataApi::Response($shelter->toARRAY(),NULL);
    else
      return DataApi::Response(NULL,"SHELTER IS NOT EXIST");
  }
  public function insertItem(){
    if($this->role!=RoleType::SUPER_USER && $this->role!=RoleType::SHELTERS_MANAGER)
      return DataApi::Response(NULL, 'אין הרשאה');

    $inspection=new Inspection();
    $inspection->save();
    return DataApi::Response($inspection->toARRAY(), NULL);
  }
  public function updateItem(){
    if($this->role!=RoleType::SUPER_USER && $this->role!=RoleType::SHELTERS_MANAGER)
      return DataApi::Response(NULL, 'אין הרשאה');

    $params=$this->getParams();

    $item=InspectionQuery::create()->findOneById($params->id);

    if(!empty($item)){
      if($params->param=='IsValid'){
        $item->setIsValid($params->value);
        if($item->save())
          return DataApi::Response($item->toARRAY(),NULL);
        else
          return DataApi::Response(NULL,"Some Problem");
      }
    }
    return DataApi::Response(NULL,"Shelter not exist");
  }
}





?>
