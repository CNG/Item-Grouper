<?php

/**
 * @author Charlie Gorichanaz
 * @copyright 2012-02-27
 */

class Item {
  private static $qty = 0;
  private $id = null;
  private $exposures = Array();
  function __construct($id) {
    $this->setID($id);
    self::$qty++;
  }
  function __destruct() {
    self::$qty--;
  }
  public function count() {
    return self::$qty;
  }
  public function __toString() {
    $exposures = '';
    foreach($this->exposures as $key => $value) {
      $exposures .= $key."".$value.",";
    }
    $exposures = substr($exposures, 0, -1);
    return "Item ".$this->id." (exposures: ".$exposures.")";
  }
  private function setID($id) {
    $this->id = $id;
  }
  public function getID() {
    return $this->id;
  }
  /**
   * Return number of exposures to Item supplied or number of Items exposed to.
   * @param Item $item Item against which this Item will be tests for exposure
   * @return int Number of exposures to supplied Item or number of Items exposed to
   */
  public function exposureCount($item = NULL) {
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
  public function expose($item) {
    $this->exposures[$item->getID()] = $this->exposureCount($item) + 1;
  }
  public function deexpose($item) {
    if ($this->exposureCount($item) > 1) {
      $this->exposures[$item->getID()] = $this->exposureCount($item) - 1;
    } else if ($this->exposureCount($item) == 1) {
      unset($this->exposures[$item->getID()]);
    } else {
      throw new Exception("Cannot unexpose $this to $item since $this has never been exposed to $item.");
    }
  }
}

class Group {
  private $maxSize = 0;
  private $mems = Array();
  private static $qty = 0;
  function __construct($maxSize = 0) {
    $this->setMaxSize($maxSize);
    self::$qty++;
  }
  function __destruct() {
    self::$qty--;
  }
  public function count() {
    return self::$qty;
  }
  public function setMaxSize($maxSize) {
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
  private function memberPosition($mem) {
    return array_search($mem, $this->mems);
  }
  public function removeMember($mem) {
    if ($this->hasMember($mem)) {
      unset($this->mems[$this->memberPosition($mem)]);
      foreach($this->mems as $current_mem) {
        $current_mem->deexpose($mem);
        $mem->deexpose($current_mem);
      }
    } else {
      throw new Exception("Cannot remove $mem from $this since $mem is not a member.");
    }
  }
  public function __toString() {
    $members = '';
    foreach($this->mems as $mem) {
      $members .= $mem.", ";
    }
    $members = substr($members, 0, -2);
    return "Group of ".$this->getPopulation()." (max ".$this->getMaxSize()."): ".$members;
  }
}

?>