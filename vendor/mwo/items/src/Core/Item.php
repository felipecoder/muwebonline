<?php

namespace MWOItems\Core;

class Item
{

  /**
   * @var string
   */
  private $section;

  /**
   * @var string
   */
  private $index;

  /**
   * @var bool
   */
  private $unique;

  /**
   * @var MWOItems\Core\Ancient;
   */
  private $ancient;

  /**
   * @var int
   */
  private $durability;

  /**
   * @var int
   */
  private $level;

  /**
   * @var bool
   */
  private $skill;

  /**
   * @var bool
   */
  private $luck;

  /**
   * @var int
   */
  private $option;

  /**
   * @var bool
   */
  private $refine;

  /**
   * @var MWOItems\Core\Serial;
   */
  private $serial;

  /**
   * @var array
   */
  private $excellent;

  /**
   * @var array
   */
  private $sockets;

  /**
   * @var MWOItems\Core\Harmony;
   */
  private $harmony;

  /**
   * @var int
   */
  private $dbversion;

  /**
   * @var string
   */
  private $connection;

  /**
   * @var MWOItems\Core\HEX;
   */
  private $hex;

  public function __construct($item = [], $dbversion, $connection)
  {
    $this->setDbversion($dbversion)
      ->setConnection($connection)
      ->setSection($item['section'])
      ->setIndex($item['index'])
      ->setUnique($item['section'])
      ->setAncient(new Ancient($this))
      ->setDurability($item['durability'])
      ->setLevel($item['level'])
      ->setSkill($item['skill'])
      ->setLuck($item['luck'])
      ->setOption($item['option'])
      ->setRefine($item['refine'])
      ->setSerial(new Serial($this))
      ->setExcellent($item['excellents'])
      ->setSockets($item['sockets'])
      ->setHarmony(new Harmony($this))
      ->setHex(new HEX($this));
    $this->getHarmony()->setType($item['harmony']['type'])
      ->setLevel($item['harmony']['level']);
  }

  /**
   * Get the value of section
   *
   * @return  string
   */
  public function getSection()
  {
    return $this->section;
  }

  /**
   * Set the value of section
   *
   * @param  string  $section
   *
   * @return  self
   */
  public function setSection($section)
  {
    $this->section = $section;

    return $this;
  }

  /**
   * Get the value of index
   *
   * @return  string
   */
  public function getIndex()
  {
    return $this->index;
  }

  /**
   * Set the value of index
   *
   * @param  string  $index
   *
   * @return  self
   */
  public function setIndex($index)
  {
    $this->index = $index;

    return $this;
  }

  /**
   * Get the value of unique
   *
   * @return  bool
   */
  public function getUnique()
  {
    return $this->unique;
  }

  /**
   * Set the value of unique
   *
   * @param  bool  $unique
   *
   * @return  self
   */
  public function setUnique($unique)
  {
    $unique = (($unique * 32) > 255) ? true : false;
    $this->unique = $unique;

    return $this;
  }

  /**
   * Get the value of ancient
   *
   * @return  MWOItems\Core\Ancient;
   */
  public function getAncient()
  {
    return $this->ancient;
  }

  /**
   * Set the value of ancient
   *
   * @param  MWOItems\Core\Ancient;  $ancient
   *
   * @return  self
   */
  public function setAncient(Ancient $ancient)
  {
    $this->ancient = $ancient;

    return $this;
  }

  /**
   * Get the value of durability
   *
   * @return  int
   */
  public function getDurability()
  {
    return $this->durability;
  }

  /**
   * Set the value of durability
   *
   * @param  int  $durability
   *
   * @return  self
   */
  public function setDurability($durability)
  {
    $this->durability = $durability;

    return $this;
  }

  /**
   * Get the value of level
   *
   * @return  int
   */
  public function getLevel()
  {
    return $this->level;
  }

  /**
   * Set the value of level
   *
   * @param  int  $level
   *
   * @return  self
   */
  public function setLevel($level)
  {
    $this->level = $level;

    return $this;
  }

  /**
   * Get the value of skill
   *
   * @return  bool
   */
  public function getSkill()
  {
    return $this->skill;
  }

  /**
   * Set the value of skill
   *
   * @param  bool  $skill
   *
   * @return  self
   */
  public function setSkill($skill)
  {
    $this->skill = $skill;

    return $this;
  }

  /**
   * Get the value of luck
   *
   * @return  bool
   */
  public function getLuck()
  {
    return $this->luck;
  }

  /**
   * Set the value of luck
   *
   * @param  bool  $luck
   *
   * @return  self
   */
  public function setLuck($luck)
  {
    $this->luck = $luck;

    return $this;
  }

  /**
   * Get the value of option
   *
   * @return  int
   */
  public function getOption()
  {
    return $this->option;
  }

  /**
   * Set the value of option
   *
   * @param  int  $option
   *
   * @return  self
   */
  public function setOption($option)
  {
    $this->option = $option;

    return $this;
  }

  /**
   * Get the value of refine
   *
   * @return  bool
   */
  public function getRefine()
  {
    return $this->refine;
  }

  /**
   * Set the value of refine
   *
   * @param  bool  $refine
   *
   * @return  self
   */
  public function setRefine($refine)
  {
    $this->refine = $refine;

    return $this;
  }

  /**
   * Get the value of serial
   *
   * @return  MWOItems\Core\Serial;
   */
  public function getSerial()
  {
    return $this->serial;
  }

  /**
   * Set the value of serial
   *
   * @param  MWOItems\Core\Serial;  $serial
   *
   * @return  self
   */
  public function setSerial(Serial $serial)
  {
    $this->serial = $serial;

    return $this;
  }

  /**
   * Get the value of excellent
   *
   * @return  array
   */
  public function getExcellent($position)
  {
    return $this->excellent[$position];
  }

  /**
   * Set the value of excellent
   *
   * @param  array  $excellent
   *
   * @return  self
   */
  public function setExcellent($excellent)
  {
    $this->excellent = $excellent;

    return $this;
  }

  /**
   * Get the value of sockets
   *
   * @return  array
   */
  public function getSockets()
  {
    return $this->sockets;
  }

  /**
   * Set the value of sockets
   *
   * @param  array  $sockets
   *
   * @return  self
   */
  public function setSockets($sockets)
  {
    $this->sockets = $sockets;

    return $this;
  }

  /**
   * Get the value of harmony
   *
   * @return  MWOItems\Core\Harmony;
   */
  public function getHarmony()
  {
    return $this->harmony;
  }

  /**
   * Set the value of harmony
   *
   * @param  MWOItems\Core\Harmony;  $harmony
   *
   * @return  self
   */
  public function setHarmony(Harmony $harmony)
  {
    $this->harmony = $harmony;

    return $this;
  }

  /**
   * Get the value of dbversion
   *
   * @return  int
   */
  public function getDbversion()
  {
    return $this->dbversion;
  }

  /**
   * Set the value of dbversion
   *
   * @param  int  $dbversion
   *
   * @return  self
   */
  public function setDbversion($dbversion)
  {
    $this->dbversion = $dbversion;

    return $this;
  }

  /**
   * Get the value of connection
   *
   * @return  string
   */
  public function getConnection()
  {
    return $this->connection;
  }

  /**
   * Set the value of connection
   *
   * @param  string  $connection
   *
   * @return  self
   */
  public function setConnection($connection)
  {
    $this->connection = $connection;

    return $this;
  }

  /**
   * Get the value of hex
   *
   * @return  MWOItems\Core\HEX;
   */
  public function getHex()
  {
    return $this->hex;
  }

  /**
   * Set the value of hex
   *
   * @param  MWOItems\Core\HEX;  $hex
   *
   * @return  self
   */
  public function setHex(HEX $hex)
  {
    $this->hex = $hex;

    return $this;
  }
}
