<?php

/**
 * @author Charlie Gorichanaz
 * @copyright 2012-02-27
 */

include('classes.php');

$itemList = "A B C D E F G H I J K L M N O P Q R";
#$itemList .= " S T U V W X Y Z";
$numGroups = 5;

$items = preg_split("/[\s,]+/", $itemList);
$remainder = count($items) % $numGroups;
$groupMinSize = ( count($items) - $remainder ) / $numGroups;
$groupMaxSize = $groupMinSize;
if ($remainder > 0) { $groupMaxSize++; }

function exposedToAll($testItem){
  global $items;
  $exposureCount = 0;
  for($i = 0; $i < count($items); $i++) {
    global ${'i'.$i};
    if (${'i'.$i} != $testItem && $testItem->exposureCount(${'i'.$i}) > 0) {
      $exposureCount++;
    } else {
    }
  }
  return $exposureCount == count($items) - 1; // minus one because we don't count exposure to self
}

function allExposedToAll() {
  global $items;
  for($i = 0; $i < count($items); $i++) {
    global ${'i'.$i};
    if (!exposedToAll(${'i'.$i})) {
      return false;
    }
  }
  return true;
}

function groupsInfo() {
  $output = '';
  global $numGroups;
  for($i = 0; $i < $numGroups; $i++) {
    global ${'g'.$i};
    $output .= ${'g'.$i}."<br />";
  }
  return $output;
}

function itemsInfo() {
  $output = '';
  global $items;
  for($i = 0; $i < count($items); $i++) {
    global ${'i'.$i};
    $output .= ${'i'.$i}."<br />";
  }
  return $output;
}

try {

  // create Items
  for($i = 0; $i < count($items); $i++) {
    ${'i'.$i} = new Item($items[$i]);
  }
  echo "Created ".Item::count()." Item objects<br />";
  
  $start_time = MICROTIME(TRUE);
  $time;
  $iterations = 0;
  while(
         !allExposedToAll()
         //&& $iterations < 10
         && $time < 20
       ) {
    #echo "<strong>Starting while loop</strong><br />";
    // create Groups
    for($i = 0; $i < $numGroups; $i++) {
      ${'g'.$i} = new Group($groupMaxSize);
    }
    #echo "Created ".Group::count()." Group objects<br />";
    // put existing Items into Groups
    for($i = 0; $i < count($items); $i++) {
      $item = ${'i'.$i};
      #echo "\$i=$i loop: ".$item->getID()."<br />";
      // METHOD A code moved below and commented out
      /**
       * METHOD B
       * Now we're going to try making the items look at all the others and self pair.
       * I'm not sure if this will be any different than METHOD A, we'll see.
       */
      // make sure $item isn't already in one of the current groups
      $itemUnused = true;
      for($j = 0; $j < $numGroups; $j++) {
        #echo "AAA<br />";
        $group = ${'g'.$j};
        if ($group->hasMember($item)) {
          #echo '$itemUnused = false;<br />';
          $itemUnused = false;
        }
      }
      // find mates
      if ($itemUnused) {
        #echo '$itemUnused true<br />';
        $newMates = Array(); // hold prospective groupmates till we reach the max group size
        $newMates[] = $item; // start by adding the current item itself
        $threshold = 0; // max exposures allowed before grouping together
        while (count($newMates) < $groupMaxSize && $threshold <= count($items)) {
          #echo 'while<br />';
          for($j = 0; $j < count($items); $j++) {
            #echo 'forA'.$j.'<br />';
            if ($j != $i) {
              #echo 'forA if<br />';
              $testItem = ${'i'.$j};
              #echo '$item is '.$item->getID().' and $testItem is '.$testItem->getID().'<br />';
              $testItemUnused = true;
              if (in_array($testItem, $newMates)) {
                $testItemUnused = false;
              } else {
                for($k = 0; $k < $numGroups; $k++) {
                  #echo 'forB<br />';
                  $group = ${'g'.$k};
                  if ($group->hasMember($testItem)) {
                    #echo 'forB if<br />';
                    $testItemUnused = false;
                  }
                }
              }
              if ($testItemUnused) {
                #echo '$testItemUnused true<br />';
                if ($item->exposureCount($testItem) <= $threshold && count($newMates) < $groupMaxSize) {
                  $newMates[] = $testItem;
                  #echo "Adding $testItem to \$newMates, which now contains ".count($newMates)." items, max is ".$groupMaxSize."<br />";
                }
              }
            }
          }
          if (count($newMates) < $groupMaxSize) {
            $threshold++;
          }
        }
        #print_r($newMates);echo "<br />";
        //find group with space
        for($j = 0; $j < $numGroups; $j++) {
          $group = ${'g'.$j};
          while (!$group->isFull() && count($newMates) > 0) {
            $group->addMember($newMates[count($newMates)-1]);
            #echo "Added ".$newMates[count($newMates)-1]." to $group<br />";
            unset($newMates[count($newMates)-1]);
          }
        }
      }
    }
    //echo groupsInfo();
    $iterations++;
    $stop_time = MICROTIME(TRUE);
    $time = $stop_time - $start_time;
    //echo "After $iterations iterations, here is the item info:<br />".itemsInfo();
  }
  echo "<br />While loop took $time seconds.<br />";
  echo "After $iterations iterations, here is the item info:<br />".itemsInfo();
  if (!allExposedToAll()) {
    echo "We are not done yet.<br />";
  } else {
    echo "We are done!<br />";
  }
      
} catch (Exception $e) {
  $pathParts = preg_split('/\//',$e->getFile());
  $file = $pathParts[count($pathParts)-1];
  echo 'Exception in '.$file.', L. '.$e->getLine().': ',  $e->getMessage(), "\n";
}

      /*
      METHOD A
      
      Following code has been replaced. The strategy here was to add items to groups
      one by one, favoring the group with the most members needing to be met, and then
      if it's equal, favoring the group with the fewest cumulative time of meeting each
      group member. Either something is wrong, this is a bad approach or both. It was
      resulting in clearly more iterations than necessary.
      
      #echo "$i: Looking at $item<br />";
      $lowestExposedMembersCount = count($items); // set to highest value
      $lowestExposedTimesCount = $iterations; // set to highest value
      $bestGroup;
      // put $item in $bestGroup, with the $lowestExposedMembersCount
      for($j = 0; $j < $numGroups; $j++) {
        $group = ${'g'.$j};
        #if ($group->isFull()) echo "full<br />"; else echo "not full<br />";
        if (!$group->isFull()) {
          #echo "inside not full block<br />";
          $exposedMembersCount = 0;
          $exposedTimesCount = 0;
          $members = $group->getMembers();
          #print_r($members);
          foreach($members as $member) {
            #echo "Checking ".$item->getID()." for exposure to ".$member->getID()."<br />";
            #echo $item->getID().' exposureCount to '.$member->getID().': '.$item->exposureCount($member).'<br />';
            if ($item->exposureCount($member) > 0) {
              $exposedMembersCount++;
              $exposedTimesCount += $item->exposureCount($member);
            }
          }
          // first check if this group has a lower overall exposure factor
          // but still prioritize a group with more members unexposed
          if ($exposedMembersCount < $lowestExposedMembersCount || $exposedTimesCount < $lowestExposedTimesCount) {
            $lowestExposedMembersCount = $exposedMembersCount;
            $lowestExposedTimesCount = $exposedTimesCount;
            $bestGroup = $group;
          }
        }
      }
      echo "Adding ".$item." to $bestGroup<br />";
      $bestGroup->addMember($item);
      */

?>
