<?php

namespace MWOItems;

use MWOItems\Core\Ancient;
use MWOItems\Core\Harmony;
use MWOItems\Core\HEX;
use MWOItems\Core\Item;
use MWOItems\Core\KOR;
use MWOItems\Core\Serial;
use MWOItems\Core\Util;

class Items
{

  /**
   * @var array
   */
  private $connection = array();

  public function __construct($connection)
  {
    $this->setConnection($connection);
  }

  /**
   * Get get the KOR Class
   *
   * @return  MWOItems\Core\KOR;
   */
  public function getItemsKOR($file)
  {
    return new KOR($file);
  }

  /**
   * Get get the Ancient class
   *
   * @return  MWOItems\Core\Ancient;
   */
  public function getItemAncient(Item $item)
  {
    return new Ancient($item);
  }

  /**
   * Get get the Serial class
   *
   * @return  MWOItems\Core\Serial;
   */
  public function getItemSerial(Item $item)
  {
    return new Serial($item);
  }

  /**
   * Get get the HEX class
   *
   * @return  MWOItems\Core\HEX;
   */
  public function getItemHEX(Item $item)
  {
    return new HEX($item);
  }

  /**
   * Get get the Item class
   *
   * @return  MWOItems\Core\Item;
   */
  public function getItem($item, $dbversion)
  {
    return new Item($item, $dbversion, $this->getConnection());
  }

  /**
   * Get get the Harmony class
   *
   * @return  MWOItems\Core\Harmony;
   */
  public function getItemHarmony(Item $item)
  {
    return new Harmony($item);
  }

  /**
   * Get get the Util Class
   *
   * @return  MWOItems\Core\Util;
   */
  public function getItemsUtil($file)
  {
    return new Util($file);
  }

  /**
   * Get the value of connection
   *
   * @return  array
   */
  public function getConnection()
  {
    return $this->connection;
  }

  /**
   * Set the value of conection
   *
   * @param  array  $connection
   *
   * @return  self
   */
  public function setConnection($connection)
  {
    $this->connection = $connection;

    return $this;
  }
}
