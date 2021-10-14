<?php
require_once __DIR__.'/../DataApi.php';
require_once __DIR__.'/./HistorySearchData.php';

use Ps\Ps\ShelterQuery;
use Ps\Ps\UseShelterQuery;
use Ps\Ps\Inspection;
use Ps\Ps\Shelter;

class ShelterData extends DataApi{
  function __construct(?object $params) {
    parent::__construct($params);
  }

  public function getSheltersByRadius(){
    $params=$this->getParams();
    HistorySearchData::insertSearch($params->curentPostion->lat, $params->curentPostion->lng);
    $shelters=ShelterQuery::create()
      ->leftJoinWith('ShelterWork')
      ->leftJoinWith('Inspection')
      ->useInspectionQuery()
        ->orderById('desc')
      ->endUse()
      ->find()
      ->toARRAY();

    $resShelters=array();
    foreach ($shelters as &$shelter) {
      $cordShleter=new stdClass;
      $cordShleter->lat=$shelter['Lat'];
      $cordShleter->lng=$shelter['Lon'];
      $distance=MapTools::getDistance($params->curentPostion, $cordShleter);
      if($distance < $params->radius){
        $shelter['Distance']=$distance;
        $resShelters[]=$shelter;
      }
    }
    foreach ($resShelters as &$s) {
      foreach ($s['Inspections'] as &$ins) {
        unset($ins['Report']);
        unset($ins['Shelter']);
        unset($ins['ShelterId']);
      }
    }
    return DataApi::Response($resShelters, NULL);
  }
  public function getItems(){
    $shelters=ShelterQuery::create()
    ->leftJoinWith('ShelterWork')
    ->leftJoinWith('Inspection')
    ->find()
    ->toARRAY();
    foreach ($shelters as &$shelter) {
      foreach ($shelter['Inspections'] as &$ins) {
        unset($ins['Report']);
        unset($ins['Shelter']);
        unset($ins['ShelterId']);
      }
    }
    return DataApi::Response($shelters, NULL);
  }
  public function getItemById(){
    $shelter= ShelterQuery::create()->findOneById($this->getParams()->id);
    if(!empty($shelter))
      return DataApi::Response($shelter->toARRAY(),NULL);
    else
      return DataApi::Response(NULL,"SHELTER IS NOT EXIST");
  }
  public function ceateInspection(){
    $params=$this->getParams();
    $curDate = new DateTime();
    $date=$curDate->format('Y-m-d');
    $time=$curDate->format('H:i:s');
    $insp=new Inspection();
    $insp->setDate($date);
    $insp->setTime($time);
    $insp->setReport($params->report);
    $insp->setShelterId($params->shelterId);
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
    $shelters=ShelterQuery::create()
      ->leftJoinWith('Inspection')
      ->leftJoinWith('ShelterWork')
      ->find();
    return DataApi::Response($shelters->toARRAY(), NULL);
  }
  public function getItemByIdAdmin(){
    if($this->role!=RoleType::SUPER_USER && $this->role!=RoleType::SHELTERS_MANAGER)
      return DataApi::Response(NULL, 'אין הרשאה');

      
    $shelter=ShelterQuery::create()->findOneById($this->getParams()->id);
    if(!empty($shelter)){
      $res=new stdClass();
      $res->UseShelters=UseShelterQuery::create()->find()->toArray();
      $res->Shelter=$shelter->toArray();
      return DataApi::Response($res,NULL);
    }
    else
      return DataApi::Response(NULL,"SHELTER IS NOT EXIST");
  }
  public function insertItem(){
    if($this->role!=RoleType::SUPER_USER && $this->role!=RoleType::SHELTERS_MANAGER)
      return DataApi::Response(NULL, 'אין הרשאה');
    
    $shelter=new Shelter();
    $shelter->save();
    return DataApi::Response($shelter->toARRAY(), NULL);
  }
  public function updateItem(){
    if($this->role!=RoleType::SUPER_USER && $this->role!=RoleType::SHELTERS_MANAGER)
      return DataApi::Response(NULL, 'אין הרשאה');
    
    $params=$this->getParams();
    $item = ShelterQuery::create()->findOneById($params->id);
    if(!empty($item)){
      $fieldName = 'set'.$params->field;
      $item->$fieldName(isset($params->value) ? $params->value : NULL);
      if($item->save())
        return DataApi::Response($item->toARRAY(), NULL);
      else
        return DataApi::Response(NULL, "המידע לא נשמר");
    }
    return DataApi::Response(NULL, "מקלט לא קיים");
  }
  public function updatePoint(){
    if($this->role!=RoleType::SUPER_USER && $this->role!=RoleType::SHELTERS_MANAGER)
      return DataApi::Response(NULL, 'אין הרשאה');
    
    $params=$this->getParams();
    $item = ShelterQuery::create()->findOneById($params->id);
    if(!empty($item)){
      $item->setLat($params->lat);
      $item->setLon($params->lon);
      $item->setAddress($params->address);
      if($item->save())
        return DataApi::Response($item->toARRAY(), NULL);
      else
        return DataApi::Response(NULL, "SOME PROBLEM WITH UPDATE ITEM");
    }
    return DataApi::Response(NULL, "Shelter is  not exist");
  }
  public function deleteItem(){
    if($this->role!=RoleType::SUPER_USER && $this->role!=RoleType::SHELTERS_MANAGER)
      return DataApi::Response(NULL, 'אין הרשאה');
    
    $shelters=ShelterQuery::create()->findOneById($this->getParams()->id);
    if(!empty($shelters)){
      $shelters->delete();
      return DataApi::Response(1, NULL);
    }
    else
      return DataApi::Response(NULL, "Shelter is not exist");
  }
  public function setContact(){
    $params=$this->getParams();
    $item=ShelterQuery::create()->findOneById($params->id);
    if(!empty($item)){
      $item->setContactName($params->contactName);
      $item->setContactPhone($params->contactPhone);
      if($item->save())
        return DataApi::Response($item->toARRAY(),NULL);
      else
        return DataApi::Response(NULL,"Some Problem");
    }
  }

}


abstract class MapTools{
  static function rad($x){
    return $x * pi() / 180;
  }
  static function getDistance(object $cord1, object $cord2){
    $R = 6378137; // Earth’s mean radius in meter
    $dLat = self::rad($cord2->lat - $cord1->lat);
    $dLong = self::rad($cord2->lng - $cord1->lng);
    $a = sin($dLat / 2) * sin($dLat / 2) +	cos(self::rad($cord1->lat)) * cos(self::rad($cord2->lat)) *	sin($dLong / 2) * sin($dLong / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $d = $R * $c;
    return $d; // returns the distance in meter
  }
}


?>
