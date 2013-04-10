<?php

/**
 * @author Charlie Gorichanaz
 * @copyright 2013-04-10
 */

include('classes.php');

$itemList = "A B C D E F G H";
#$itemList = "A B C D E F G H I J K L M N O P Q R";
#$itemList .= " S T U V W X Y Z";
$numGroups = 2;
if(isset($_GET['i'])) { $itemList = preg_replace("/[^\w\d\s]+/","",$_GET['i']); }
if(isset($_GET['g'])) { $numGroups = intval($_GET['g']); }

$itemIDs = preg_split("/[\s,]+/", $itemList);

try {
  $start_time = MICROTIME(TRUE);
  $time;
  // create Items
  $items = Array();
  for($i = 0; $i < count($itemIDs); $i++) {
    $items[] = new Item($itemIDs[$i]);
  }
  echo "Created ".count($items)." Item objects<br />";

  $scenario = new Scenario($items,$numGroups);
  while( $scenario->exposure() < 1 && $time < 60 ){
    $scenario->nextArrangement(2);
    //echo $scenario->exposure()."<br />";
    $stop_time = MICROTIME(TRUE);
    $time = $stop_time - $start_time;
  }
  echo "$scenario<br />";
  echo "<br />Script took $time seconds to work through ".$scenario->getArrangementCount()." arrangements.<br />";
} catch (Exception $e) {
  $pathParts = preg_split('/\//',$e->getFile());
  $file = $pathParts[count($pathParts)-1];
  echo 'Exception in '.$file.', L. '.$e->getLine().': ',  $e->getMessage(), "\n";
}

?>
