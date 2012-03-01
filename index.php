<?php

/**
 * @author Charlie Gorichanaz
 * @copyright 2012-02-27
 */

include('classes.php');

$itemList = "A B C D E F G H";
#$itemList = "A B C D E F G H I J K L M N O P Q R";
#$itemList .= " S T U V W X Y Z";
$numGroups = 2;
if(isset($_GET['i'])) { $itemList = preg_replace("/[^\w\d\s]+/","",$_GET['i']); }
if(isset($_GET['g'])) { $numGroups = intval($_GET['g']); }

$itemIDs = preg_split("/[\s,]+/", $itemList);

/**
 * NOTE: If $remainder is not 0, it is also the number of groups that must be 
 * the maximum size. Otherwise the groups won't be as even as possible.
 */
$remainder = count($itemIDs) % $numGroups;
$groupMinSize = ( count($itemIDs) - $remainder ) / $numGroups;
$groupMaxSize = $groupMinSize;
if ($remainder > 0) { $groupMaxSize++; }

function exposedToAll($item, $items){
  $exposureCount = 0;
  foreach($items as $testItem){
    if($item != $testItem && $testItem->exposureCount($item) > 0) {
      $exposureCount++;
    }
  }
  return $exposureCount == count($items) - 1; // minus one because we didn't count exposure to self
}

function allExposedToAll($items) {
  foreach($items as $item){
    if(!exposedToAll($item, $items)){
      return false;
    }
  }
  return true;
}

function groupsInfo($groups) {
  $output = '';
  foreach($groups as $group){
    $output .= $group."<br />";
  }
  return $output;
}

function itemsInfo($items) {
  $output = '';
  foreach($items as $item){
    $output .= $item."<br />";
  }
  return $output;
}

try {

  // create Items
  $items = Array();
  for($i = 0; $i < count($itemIDs); $i++) {
    $items[] = new Item($itemIDs[$i]);
  }
  echo "Created ".Item::count()." Item objects<br />";
  
  $start_time = MICROTIME(TRUE);
  $time;
  $iterations = 0;
  while(
         !allExposedToAll($items)
         #&& $iterations < 10
         && $time < 2
       ) {
    echo "<strong>Starting while loop</strong><br />";
    // create Groups
    $groups = Array();
    $groupsAtMax = $remainder;
    for($i = 0; $i < $numGroups; $i++) {
      if($groupsAtMax > 0) {
        $groups[] = new Group($groupMaxSize);
        $groupsAtMax--;
      } else {
        $groups[] = new Group($groupMinSize);
      }
    }
    /**
     * METHOD C
     * This method should get very close to the solution:
     * 1. Copy Item array and shuffle on each iteration. (The effect of this might 
     *    be minimal after the next step, especially after a few iterations.)
     * 2. Sort Items by number of Items to which they have been exposed, in
     *    ascending order.
     * 3. Take the first Item and check for exposure to each other Item, 
     *    starting at the end with the least exposed Items. Each time an 
     *    unexposed Item is happened upon, add to an array of groupmates until
     *    the maximum group size has been reached. If last item is examined and
     *    the maximum group size has not been reached, increase the threshold.
     * 4. Add the Items to a Group and remove the Items from the pending array.
     * 5. Repeat until all Items are in Groups.
     * 
     * Ideally I think we would look at all possible max group size combinations
     * of the available items and favor the group with the maximum overall number
     * of potential new exposures to be made. Then take the remaining items and 
     * do the same, until none are left. This would be a greatly more resource
     * intensive operation, and may not yield much better results, especially for
     * smaller item counts.
     */
    // STEP 1
    $workingItems = $items;
    shuffle($workingItems);
    #print_r($workingItems); echo "<br />";
    // STEP 2
    $exposureCounts = Array(); // allow sorting Items by their exposure counts
    foreach($workingItems as $item){
      $exposureCounts[] = $item->exposureCount();
    }
    array_multisort($exposureCounts, $workingItems);
    #print_r($workingItems); echo "<br />";
    // STEPS 3, 4 and 5
    foreach($groups as $group) {
      #echo "<strong>Working on filling $group</strong><br />";
      // STEP 3
      $itemA = $workingItems[0]; // First item is lowest exposed item
      unset($workingItems[0]); // removes key, doesn't reindex
      $workingItems = array_values($workingItems);
      $group->addMember($itemA);
      $threshold = 0; // start looking for Items that have never been exposed to current Item
      #print_r($workingItems); echo "<br />";
      while(count($workingItems) > 0 && !$group->isFull() && $threshold < count($items)) {
        $workingItemsCount = count($workingItems); // because we are changing the size within loop
        for($i = 0; $i < $workingItemsCount; $i++){
          #echo "LOOP $i: $workingItems[$i]<br />";
          $itemB = $workingItems[$i];
          #echo "BEGIN $itemB<br />";
          #if(!$group->isFull()) { echo "<strong>A:</strong> $group is not full<br />"; }
          #if($itemA != $itemB) { echo "<strong>B:</strong> $itemA is not $itemB<br />"; }
          #if($itemA->exposureCount($itemB) <= $threshold) { echo "<strong>C:</strong> ".$itemA->exposureCount($itemB)." exposures below threshold $threshold<br />"; }
          if(!$group->isFull() && $itemA != $itemB && $itemA->exposureCount($itemB) <= $threshold) {
            #echo "Adding $itemB because size $groupMaxSize not reached, $itemA is not $itemB and ".$itemA->exposureCount($itemB)." exposures below threshold $threshold<br />";
            $group->addMember($itemB);
            unset($workingItems[$i]);
            #$workingItems = array_values($workingItems);
          }
          #echo "END $itemB<br />";
        }
        $threshold++;
        $workingItems = array_values($workingItems);
      }
      echo "Group info: $group<br />";
    }
      
    //echo groupsInfo($groups);
    $iterations++;
    $stop_time = MICROTIME(TRUE);
    $time = $stop_time - $start_time;
    #echo "After $iterations iterations, here is the item info:<br />".itemsInfo($items);
  }
  echo "<br />While loop took $time seconds.<br />";
  echo "After $iterations iterations, here is the item info:<br />".itemsInfo($items);
  if (!allExposedToAll($items)) {
    echo "We are not done yet.<br />";
  } else {
    echo "We are done!<br />";
  }
      
} catch (Exception $e) {
  $pathParts = preg_split('/\//',$e->getFile());
  $file = $pathParts[count($pathParts)-1];
  echo 'Exception in '.$file.', L. '.$e->getLine().': ',  $e->getMessage(), "\n";
}

      /**
       * METHOD A
       * Following code has been replaced. The strategy here was to add items to groups
       * one by one, favoring the group with the most members needing to be met, and then
       * if it's equal, favoring the group with the fewest cumulative time of meeting each
       * group member. Either something is wrong, this is a bad approach or both. It was
       * resulting in clearly more iterations than necessary.
       */
      /*
      #echo "$i: Looking at $item<br />";
      $lowestExposedMembersCount = count($itemIDs); // set to highest value
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

      /**
       * METHOD B
       * Now we're going to try making the items look at all the others and self pair.
       * I'm not sure if this will be any different than METHOD A, we'll see.
       */
      /*
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
        while (count($newMates) < $groupMaxSize && $threshold <= count($itemIDs)) {
          #echo 'while<br />';
          for($j = 0; $j < count($itemIDs); $j++) {
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
      */
?>
