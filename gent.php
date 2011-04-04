<?php
  require_once('api_keys.inc.php');
  
  // GENT
  $location = "51.0510868,3.7313823";
  
  // New York
  //$location = "40.7,-74";


  $url = "https://api.foursquare.com/v2/venues/trending?ll=$location&radius=2000&client_id=EILX4RP3SHOEBL5NXK1PQ3KPTLW3YSPFV334ZRKKNL2SOJCR&client_secret=3KSRIAC5XRC11MF31LQWVS0B0W013QXWEDFGNYBLAEWNMPRS";

  $ch = curl_init(); 
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json; charset=utf-8" ));
  
  /*  
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
  curl_setopt($ch, CURLOPT_CAINFO, getcwd() . "./https_certificate_foursquare_com.crt");
  */

  $result = curl_exec($ch); 
  if($result === false)
  {
      $result = 'Curl error: ' . curl_error($ch);
  }

  curl_close($ch);  




  $result = json_decode($result);
  $venues = $result->response->venues;

  $db = mysql_connect($db_server, $db_user, $db_password);
  mysql_select_db($db_database, $db);
  
  $data = array();

  foreach ( $venues as $venue )
  {
    $name = addslashes($venue->name);
    $foursquare_id = addslashes($venue->id);
    $location = addslashes(json_encode($venue->location));
    $category = addslashes($venue->categories[0]->icon);
    $herenow = $venue->hereNow->count;

    $sql = "SELECT foursquare_id FROM ".$db_prefix."locations
            WHERE foursquare_id='$foursquare_id'
            LIMIT 1";
    echo $sql . "<br /><br />";
    $result = mysql_query($sql);
    if ( mysql_num_rows($result) == 0 ) 
    {
      $sql = "INSERT INTO ".$db_prefix."locations SET
              name='$name',
              foursquare_id='$foursquare_id',
              location='$location',
              category='$category' 
              ";
      $result = mysql_query($sql);
      echo $sql . "<br /><br />";
    }

    $stats = new StdClass();
    $stats->foursquare_id = $foursquare_id;
    $stats->herenow = $herenow;

    $data[] = $stats;
  }

  //if ( count($venues) > 0 )
  {
    $data = json_encode($data);
    $data = addslashes($data);

    $sql = "INSERT INTO ".$db_prefix."stats SET
                data='$data'";
    $result = mysql_query($sql);
    echo $sql . "<br /><br />";
  }

  mysql_close($db);

?>
<code><pre>
<? print_r($venues); ?>
</pre></code>