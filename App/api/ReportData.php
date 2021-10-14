<?php
require_once __DIR__.'/../DataApi.php';


class ReportData extends DataApi{
  function __construct(?object $params) {
    parent::__construct($params);
  }
  public function getLastSearch(){
    $res=DataApi::CustomQuery("SELECT date AS 'תאריך', count(*) AS 'כמות' FROM `history_search`GROUP BY date ORDER BY date desc limit 7");
    if($res->Data)
      return DataApi::Response($res->Data, NULL);
    return DataApi::Response(NULL, $res->Error);
  }
  public function getCostPerShelter(){
    $res=DataApi::CustomQuery(
      "SELECT s.address as 'מקלט', sum(cost) AS 'עלות' from shelter_work AS w 
      INNER JOIN shelter AS s on s.id=w.shelter_id where w.is_complete=1 GROUP BY w.shelter_id"
    );
    if($res->Data)
      return DataApi::Response($res->Data, NULL);
    return DataApi::Response(NULL, $res->Error);

  }
}