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
      $s->locations[] = $row;
    }
    $stats[] = $s;
  }
  else
  {

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
    <link href="style.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" media="all and (max-device-width: 480px)" href="phone.css"> 
    <link rel="stylesheet" media="all and (min-device-width: 481px) and (max-device-width: 1024px) and (orientation:portrait)" href="portrait.css"> 
    <link rel="stylesheet" media="all and (min-device-width: 481px) and (max-device-width: 1024px) and (orientation:landscape)" href="landscape.css"> 
    <link rel="stylesheet" media="all and (min-device-width: 1025px)" href="desktop.css"> 
    <script type="text/javascript" src="jquery-1.5.min.js"></script>
    <script type="text/javascript" src="iscroll-min.js"></script>
		<script type="application/javascript" src="add2home.js"></script>
    <script type="text/javascript" src="trending.js"></script>
  </head>
  <body onload="on_load();" onorientationchange="update_orientation();">
    <div id="preloader"><div class="message">loading</div></div>
    <div id="main">
      <div class="heading">
        <h1><span class="bold">Trending in Ghent</span> powered by Foursquare</h1>
      </div>
      <div class="stats">
        <? foreach( $stats as $s ) { ?>
          <div class="item">
            <div class="timestamp">
              <span class="date"><?= date("l jS", strtotime($s->time_in) ) ?></span>
              <span class="time"><?= date("H:i", strtotime($s->time_in) ) ?></span>
            </div>
            <div class="locations">
              <? foreach( $s->locations as $l ) { ?>
                <div class="location">
                  <div class="category"><img class="icon" src="<?= $l->category ?>" /></div>
                  <div class="name"><?= $l->name ?></div>
                  <div class="herenow"><?= $l->herenow ?></div>
                </div>
              <? } ?>
            </div>
          </div>
        <? } ?>
      </div>
    </div>  
  </body>
</html>
