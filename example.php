<?php

/**
 * @author Charlie Gorichanaz
 * @copyright 2013-04-10
 */
error_reporting(0);
include('classes.php');

if(isset($_REQUEST['i']) && isset($_REQUEST['g'])) {
  $itemData = explode("\n",$_REQUEST['i']);
  $numGroups = intval($_REQUEST['g']);
  $itemIDs = Array();
  foreach($itemData as $datum){
    if( strlen(trim($datum)) >= 1 && strlen(trim($datum)) < 64 ){
      $itemIDs[] = trim(htmlentities($datum, ENT_QUOTES, 'UTF-8'));
    }
  }
  echo '<strong>Results:</strong><br />';
  if(count($itemIDs) < $numGroups){
    echo "Asked for ".count($itemIDs)." items to be placed in $numGroups. Must ask for fewer groups than items.<br />";
  } else {
    try {
      $start_time = MICROTIME(TRUE);
      $time;
      // create Items
      $items = Array();
      for($i = 0; $i < count($itemIDs); $i++) {
        $items[] = new Item($itemIDs[$i]);
      }
      //echo "Created ".count($items)." Item objects<br />";

      $scenario = new Scenario($items,$numGroups);
      $limit = 90;
      while( $scenario->exposure() < 1 && $time < $limit ){
        $scenario->nextArrangement(2);
        $stop_time = MICROTIME(TRUE);
        $time = $stop_time - $start_time;
      }
      if( $scenario->exposure() < 1 && $time > $limit ) {
        echo "Solution not found because script took longer than $limit seconds.<br />";
      }
      echo "$scenario<br />";
      echo "<br /><em>Script took $time seconds to work through ".$scenario->getArrangementCount()." arrangements.</em><br />";
    } catch (Exception $e) {
      $pathParts = preg_split('/\//',$e->getFile());
      $file = $pathParts[count($pathParts)-1];
      echo 'Exception in '.$file.', L. '.$e->getLine().': ',  $e->getMessage(), "\n";
    }
  }
}
?>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
  <p>
    <label for="i"><strong>Enter one name on each line:</strong></label><br />
    <textarea name="i" rows="20" cols="70"><?php if(isset($_REQUEST['i'])){ echo htmlentities($_REQUEST['i'], ENT_QUOTES, 'UTF-8'); } ?></textarea><br />
  </p>
  <p>
    <label for="g"><strong>How many groups per round?</strong></label><br />
    <input type="text" name="g" value="<?php if(isset($_REQUEST['i'])){ echo htmlentities($_REQUEST['g'], ENT_QUOTES, 'UTF-8'); } ?>" /><br />
  </p>
  <p>
    <input type="submit" value="Compute" />
  </p>
</form>
