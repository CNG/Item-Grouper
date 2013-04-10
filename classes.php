<?php

/**
 * @author Charlie Gorichanaz
 * @copyright 2013-04-10
 */

class Item {
  private $id = null;
  private $exposures = Array();
  function __construct($id) {
    $this->setID($id);
  }
  public function __toString() {
    $exposures = '';
    foreach($this->exposures as $key => $value) {
      $exposures .= $key."".$value.",";
    }
    $exposures = substr($exposures, 0, -1);
    return "Item ".$this->id." (exposures: ".$this->exposureCount().")";
  }
  private function setID($id) {
    $this->id = $id;
  }
  public function getID() {
    return $this->id;
  }
  public function expose($item) {
    if( isset($this->exposures[$item->getID()]) ) {
      $this->exposures[$item->getID()] = $this->exposures[$item->getID()] + 1;
    } else {
      $this->exposures[$item->getID()] = 1;
    }
  }
  /**
  * Return number of exposures to Item supplied or number of Items exposed to.
  * @param Item $item Item against which this Item will be tests for exposure
  * @return int Number of exposures to supplied Item or number of Items exposed to
  */
  public function exposureCount($item = null) {
    if(isset($item)) {
      if(array_key_exists($item->getID(), $this->exposures)) {
        return $this->exposures[$item->getID()];
      } else {
        return 0;
      }
    } else {
      return count($this->exposures);
    }
  }
}

class Group {
  private $maxSize = 0;
  private $mems = Array();
  function __construct($maxSize = 0) {
    $this->setMaxSize($maxSize);
  }
  function __clone() {
    $clonedMems = Array();
    foreach($this->mems as $member){
      $clonedMems[] = clone $member;
    }
    $this->mems = $clonedMems;
  }
  private function setMaxSize($maxSize) {
    $this->maxSize = $maxSize;
  }
  public function getMaxSize() {
    return $this->maxSize;
  }
  public function getPopulation() {
    return count($this->mems);
  }
  public function getMembers() {
    return $this->mems;
  }
  public function clear() {
    $this->mems = Array();
  }
  public function isFull() {
    if ($this->getMaxSize() == 0 || $this->getPopulation() < $this->getMaxSize()) {
      return false;
    } else {
      return true;
    }
  }
  public function addMember($mem) {
    if (!$this->isFull()) {
      if ($this->hasMember($mem)) {
        throw new Exception("Cannot add $mem to $this since $mem is already a member.");
      } else {
        foreach($this->mems as $current_mem) {
          $current_mem->expose($mem);
          $mem->expose($current_mem);
        }
        $this->mems[] = $mem;
      }
    } else {
      throw new Exception("Cannot add $mem to $this since group already contains ".$this->getPopulation()." members.");
    }
  }
  public function hasMember($mem) {
    return in_array($mem, $this->mems);
  }
  // return total exposures of contained items
  public function exposureCount() {
    $tempTotal = 0;
    foreach($this->mems as $mem){
      $tempTotal += $mem->exposureCount();
    }
    return $tempTotal;
  }
  public function __toString() {
    $members = '';
    foreach($this->mems as $mem) {
      $members .= $mem.", ";
    }
    $members = substr($members, 0, -2);
    return "Group of ".$this->getPopulation().": $members";
  }
}

class Arrangement {
  private $groups = Array();
  private function addGroup($group) {
    $this->groups[] = $group;
  }
  function __construct($groups = Array()) {
    foreach($groups as $group){
      $this->addGroup($group);
    }
  }
  function __clone() {
    $clonedGroups = Array();
    foreach($this->groups as $group){
      $clonedGroups[] = clone $group;
    }
    $this->groups = $clonedGroups;
  }
  public function getGroups() {
    return $this->groups;
  }
  public function getPopulation() {
    $itemCount = 0;
    foreach($this->groups as $group){
      $itemCount += $group->getPopulation();
    }
    return $itemCount;
  }
  public function getItems() {
    $groups = $this->getGroups();
    $items = Array();
    foreach($groups as $group){
      $innerItems = $group->getMembers();
      foreach($innerItems as $item){
        $items[] = $item;
      }
    }
    return $items;
  }
  public function getEmptyGroups() {
    $groups = $this->getGroups();
    foreach($groups as $group){
      $group->clear();
    }
    return $groups;
  }
  public function shuffle() {
    $items = $this->getItems();
    $groups = $this->getEmptyGroups();
    shuffle($items);
    $groupIndex = 0;
    $lastGroup = count($groups) - 1;
    foreach($items as $item){
      while($groups[$groupIndex]->isFull()) {
        $groupIndex++;
        if($groupIndex > $lastGroup){
          throw new Exception("More items than fit in groups. This shouldn't happen.");
        }
      }
      $groups[$groupIndex]->addMember($item);
    }
    return $this;
  }
  // return average exposure of contained groups' contained items
  public function exposure() {
    $exposureCount = 0;
    foreach($this->groups as $group){
//  echo "hey".$group->exposureCount()."<br />";
      $exposureCount += $group->exposureCount();
    }
    return $exposureCount / $this->getPopulation() / ( $this->getPopulation() - 1 );
  }
  public function __toString() {
    $groups = '';
    foreach($this->groups as $group) {
      $memberObjects = $group->getMembers();
      $members = '';
      foreach($memberObjects as $member){
        $members .= $member->getID().", ";
      }
      $members = substr($members, 0, -2);
      $members = "($members)";
      $groups .= $members.", ";
    }
    $groups = substr($groups, 0, -2);
    return "Arrangement: $groups";
  }
}

class Scenario {
  private $arrangements = Array();
  function __construct($items = null, $numGroups = 0) {
    if ($items && $numGroups) {
      /**
       * NOTE: If remainder is not 0, it is also the number of groups that must be
       * the maximum size. Otherwise the groups won't be as even as possible.
       */
      $groupsAtMax = count($items) % $numGroups;
      $groupMinSize = ( count($items) - $groupsAtMax ) / $numGroups;
      $groupMaxSize = $groupMinSize + 1;
      // create groups with appropriate maximums
      $groups = Array();
      for($i = 0; $i < $groupsAtMax; $i++) {
        $groups[] = new Group($groupMaxSize);
      }
      for($i = $groupsAtMax; $i < $numGroups; $i++) {
        $groups[] = new Group($groupMinSize);
      }
      // add items to groups
      $groupIndex = 0;
      $lastGroup = count($groups) - 1;
      foreach($items as $item){
        while($groups[$groupIndex]->isFull()) {
          $groupIndex++;
          if($groupIndex > $lastGroup){
            throw new Exception("More items than fit in groups. This shouldn't happen.");
          }
        }
        $groups[$groupIndex]->addMember($item);
      }
      // create arrangement with groups and save
      $this->addArrangement(new Arrangement($groups));
    } else {
      throw new Exception("Must provide item list and number of groups.");
    }
  }
  private function addArrangement($arrangement) {
    $this->arrangements[] = $arrangement;
  }
  private function getLastArrangement() {
    return $this->arrangements[ count($this->arrangements) - 1 ];
  }
  public function getArrangementCount() {
    return count($this->arrangements);
  }
  // return last average exposure of contained groups' contained items
  public function exposure() {
    return $this->getLastArrangement()->exposure();
  }
  public function nextArrangement($method = 1) {
    switch($method){
      case 1:
        // assign items to groups smartly
        $arrangement = clone $this->getLastArrangement();
        $items = $arrangement->getItems();
        $groups = $arrangement->getEmptyGroups();
        /*
        NOT FINISHED
        Add items to groups based on the group with most members item hasn't met yet
        */
        // create arrangement with groups and save
        $this->addArrangement(new Arrangement($groups));
        break;
      case 2:
        // assign items to groups randomly, calculating exposure and keeping highest one
        $arrangement = clone $this->getLastArrangement();
        $highExposure = $arrangement->shuffle()->exposure();
        $loopIterations = count($arrangement->getGroups()) * $arrangement->getPopulation() * 50;
        echo "Starting $loopIterations tests<br />";
        for($i = 0; $i < $loopIterations; $i++){
          $tempArrangement = clone $this->getLastArrangement();
          $tempArrangement->shuffle();
          if($tempArrangement->exposure() > $highExposure){
            $arrangement = $tempArrangement;
            $highExposure = $arrangement->exposure();
            echo "New high on test $i: $highExposure<br />";
          }
        }
        $this->addArrangement($arrangement);
        echo "<br />";
        break;
      case 3:
        // assign items to groups randomly
        $arrangement = clone $this->getLastArrangement();
        $this->addArrangement($arrangement->shuffle());
        break;
    }
  }
  public function __toString() {
    $arrangements = '';
    foreach($this->arrangements as $arrangement) {
      $arrangements .= $arrangement.",<br />";
    }
    $arrangements = substr($arrangements, 0, -7);
    return "Scenario:<br />$arrangements";
  }
}

?>