<?php
  require_once('api_keys.inc.php');

  $db = mysql_connect($db_server, $db_user, $db_password);
  mysql_select_db($db_database, $db);

  $sql = "SELECT t.time_in, l.foursquare_id, l.name, s.herenow, l.location, l.category
          FROM ".$db_prefix."stats s
          JOIN ".$db_prefix."locations l ON l.id=s.location_id
          JOIN ".$db_prefix."timestamps t ON t.id=s.timestamp_id
          ORDER BY t.time_in DESC, s.herenow DESC
          LIMIT 300";
  $result = mysql_query($sql);

  if ( mysql_error() ) echo mysql_error() . "<br />";

  $stats = array();
  if ( mysql_num_rows($result) != 0 )
  {
    $last_stamp = "";
    $s;

    while( $row = mysql_fetch_object($result) )
    {
      if ( $last_stamp != $row->time_in )
      {
         if ( isset($s) ) $stats[] = $s;
         $s = new StdClass();
         $s->time_in = $row->time_in;
         $s->locations = array();
         $last_stamp = $row->time_in;
      }

      $loc = json_decode($row->location);
      $row->address = isset($loc->address) ? $loc->address : "" ;
      $row->lat = $loc->lat;
      $row->lng = $loc->lng;

      $s->locations[] = $row;
    }
    $stats[] = $s;
  }
  else
  {

  }

  $timeframe = 48 * 2; // 2 dagen
  $now = strtotime( date("Y-m-d H:") . floor(date("i")/30)*30 );

  $prev_bg = -1;
  $chronological = array();
  for ( $i=0; $i<$timeframe; $i++ )
  {
    $timestamp = $now - ( $i * (30*60) );
    $item = new StdClass();
    $item->timestamp = $timestamp;
    
    /*
    do {
      $bg = rand(0,3);
    } while ( $bg == $prev_bg );
    */
    
     $bg = ($prev_bg+1) % 4;
    
    $item->bg = $bg;
    $prev_bg = $bg;
    
    
    $chronological[] = $item;
  }

  mysql_close($db);
?>
<!DOCTYPE>
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <title>Trending in Ghent</title>
    <meta name="format-detection" content="telephone=no">
    <meta name="viewport" content="width=device-width, minimum-scale=1.0,maximum-scale=1.0">
    <meta name="apple-mobile-web-app-status-bar-style" content="default" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <link rel="apple-touch-startup-image" href="images/splash.png" />
    <link rel="apple-touch-icon" href="images/app-icon.png" />
    <link rel="shortcut icon" href="/>
    <link rel="icon" type="image/png" href=""/>
    <link href="reset.css" rel="stylesheet" type="text/css" />
    <link href="add2home.css" rel="stylesheet" type="text/css" />
    <link href="visual.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" media="all and (max-device-width: 480px)" href="phone.css">

    <script type="text/javascript" src="jquery-1.5.min.js"></script>
    <script type="text/javascript" src="iscroll-min.js"></script>
		<script type="application/javascript" src="add2home.js"></script>
    <script type="text/javascript" src="trending.js"></script>
  </head>
  <body id="visual" onload="on_load();" onorientationchange="update_orientation();">
    <div id="preloader"><div class="message">loading</div></div>
    <div id="main">
      <div class="heading">
        <div class="powered">powered by Foursquare</div>
        <h1><span class="bold">Trending in Ghent</span></h1>
      </div>
      <div id="infographic">
        <? foreach( $chronological as $item ) { ?>
          <div class="item bg<?=$item->bg?>">
            <div class="timestamp"><? echo date( "H:i", $item->timestamp); ?></div>
          </div>
        <? } ?>
      </div>
    </div>
  </body>
</html>