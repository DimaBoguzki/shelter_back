<?php

// require_once __DIR__.'/../DataApi.php';

use Ps\Ps\HistorySearch;
use Ps\Ps\HistorySearchQuery;

class HistorySearchData{
  public static function insertSearch(float $lat, float $lon) : bool{
    if(isset($lat) && isset($lon)){
      $x=new HistorySearch();
      $curDate = new DateTime();
      $date=$curDate->format('Y-m-d');
      $time=$curDate->format('H:i:s');
      $x->setLat($lat);
      $x->setLon($lon);
      $x->setDate($date);
      $x->setTime($time);
      $x->setIP(DataApi::GetClientIp());
      if($x->save())
        return true;
    }

    return false;
  }
}
