<?php
  require_once('api_keys.inc.php');

  $timeframe = 24 * 2; // 1 dag
  $now = strtotime( date("Y-m-d H:") . floor(date("i")/30)*30 );

  $chronological = array();
  for ( $i=0; $i<$timeframe; $i++ )
  {
    $timestamp = $now - ( $i * (30*60) );
    $item = new StdClass();
    $item->timestamp = $timestamp;
    $item->bg = $i % 4;

    $chronological[] = $item;
  }



  $timelength = $chronological[count($chronological)-1]->timestamp - $chronological[0]->timestamp;
  $timestart = $chronological[0]->timestamp;


  $db = mysql_connect($db_server, $db_user, $db_password);
  mysql_select_db($db_database, $db);

  $sql = "SELECT t.time_in, l.foursquare_id, l.id, l.name, s.herenow, l.location, l.category
          FROM ".$db_prefix."stats s
          JOIN ".$db_prefix."locations l ON l.id=s.location_id
          JOIN ".$db_prefix."timestamps t ON t.id=s.timestamp_id
          WHERE t.time_in <= '" . date("Y-m-d H:i") ."' AND t.time_in >= '" . date("Y-m-d H:i", $chronological[count($chronological)-1]->timestamp) ."'
          ORDER BY l.id DESC, t.time_in DESC, s.herenow DESC";
  $result = mysql_query($sql);

  if ( mysql_error() ) echo mysql_error() . "<br />";

  $stats = array();
  if ( mysql_num_rows($result) != 0 )
  {
    $last_location = "";
    $s;

    while( $row = mysql_fetch_object($result) )
    {
      if ( $last_location != $row->id )
      {
        if ( isset($s) ) {
          $stats[] = $s;
        }
        
        $s = new StdClass();
        $s->name = $row->name;
        $s->category = $row->category;
        
        $s->location = new StdClass();
        
        $loc = json_decode($row->location);
        $s->location->address = isset($loc->address) ? $loc->address : "" ;
        $s->location->lat = $loc->lat;
        $s->location->lng = $loc->lng;
        $s->moments = array();
        
        $last_location = $row->id;
      }

      $moment = new StdClass();
      $moment->timestamp = strtotime( date("Y-m-d H:", strtotime($row->time_in) ) . floor(date("i", strtotime($row->time_in))/30)*30 );
      $moment->time_index = ($timestart - $moment->timestamp) / (30*60);
      $moment->herenow = $row->herenow;
      $s->moments[] = $moment;
    }
    $stats[] = $s;
  }
  else
  {

  }
  
  //var_dump($stats);


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
    <script type="text/javascript" src="visual.js"></script>
  </head>
  <body id="visual" onload="on_load();" onorientationchange="update_orientation();">
    <div id="preloader"><div class="message">loading</div></div>
    <div id="main">
      <div class="heading">
        <div class="powered">according to Foursquare</div>
        <h1><span class="bold">Trending in Ghent</span></h1>
      </div>
      <div id="scrollwrapper">
        <div id="infographic">
          <div class="timestamps">
            <? $i=0; foreach( $chronological as $item ) { ?>
              <div class="item bg<?=$item->bg?>" id="item-<?=$i?>">
                <div class="timestamp"><? echo date( "H:i", $item->timestamp); ?></div>
              </div>
            <? $i++; } ?>
          </div>
          
          <div class="stats">
            <? $i=0; foreach( $stats as $s ) { ?>
              <div class="stat" id="stat-<?=$i?>" style="margin-top: <?= 60*$i ?>px; right: <?= 60+40*$s->moments[0]->time_index ?>px;">
                <? for ( $j=1; $j<count($s->moments); $j++) { ?>
                  <div class="tail" style="right: <?= 40*($s->moments[$j]->time_index - $s->moments[0]->time_index) ?>px;">
                    <?= $s->moments[$j]->time_index - $s->moments[0]->time_index ?>
                  </div>
                <? } ?>
                <div class="head">
                  <div class="name"><?= $s->name ?></div>
                </div>
              </div>
            <? $i++; } ?>
          </div>
          
        </div>
      </div>
    </div>
  </body>
</html>