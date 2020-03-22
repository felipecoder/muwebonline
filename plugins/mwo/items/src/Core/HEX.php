<?php

namespace MWOItems\Core;

class HEX
{
  /**
   * @var MWOItems\Core\Item;
   */
  private $item;

  public function __construct(Item $item)
  {
    $this->setItem($item);
  }

  public function create()
  {
    $hex = "";
    $hex .= $this->createbytes1();
    $hex .= $this->createbytes2();
    $hex .= $this->createbytes3();
    $hex .= $this->createbytes4to7();
    $hex .= $this->createbytes8();
    if ($this->getItem()->getDbversion() <= 2) {
      $hex .= $this->createbytes9();
      $hex .= "00";
    } else {
      $hex .= $this->createbytes9();
      $hex .= $this->createbytes10();
      $hex .= $this->createbytes11();
      $hex .= $this->createbytes12to16();
    }
    if (3 < $this->getItem()->getDbversion()) {
      $hex .= $this->createbytes17to21();
      $hex .= str_repeat("F", 24);
    }
    return strtoupper($hex);
  }

  private function createbytes1()
  {
    $this->getItem();
    if ($this->getItem()->getDbversion() < 2) {
      return $this->correct(dechex(($this->getItem()->getIndex() & 31 | $this->getItem()->getSection() << 5 & 224) & 255));
    }
    $dec = $this->getItem()->getIndex();
    if ($this->getItem()->getUnique() && 255 <= $dec) {
      $dec -= 256;
    }
    return $this->correct(dechex($dec));
  }

  private function createbytes2()
  {
    $level = $this->getItem()->getLevel() * 8;
    $level += $this->getItem()->getSkill() ? 128 : 0;
    $level += $this->getItem()->getLuck()  ? 4 : 0;
    switch ($this->getItem()->getOption()) {
      case 4:
        $level += 1;
        break;
      case 8:
        $level += 2;
        break;
      case 12:
        $level += 3;
        break;
      case 16:
        $level += 0;
        break;
      case 20:
        $level += 1;
        break;
      case 24:
        $level += 2;
        break;
      case 28:
        $level += 3;
        break;
    }
    return $this->correct(dechex($level));
  }

  private function createbytes3()
  {
    return $this->correct(dechex($this->getItem()->getDurability()));
  }

  private function createbytes4to7()
  {
    $serial = $this->getItem()->getSerial()->generate();
    if (8 < strlen($serial)) {
      $serial = substr($serial, 0, 8);
    }
    return $this->correct($serial, 8);
  }

  private function createbytes8()
  {
    $excellent = 0;
    $excellent += $this->getItem()->getUnique() ? 128 : 0;
    $excellent += 16 <= $this->getItem()->getOption() ? 64 : 0;
    $excellent += $this->getItem()->getExcellent(0) ? 1 : 0;
    $excellent += $this->getItem()->getExcellent(1) ? 2 : 0;
    $excellent += $this->getItem()->getExcellent(2) ? 4 : 0;
    $excellent += $this->getItem()->getExcellent(3) ? 8 : 0;
    $excellent += $this->getItem()->getExcellent(4) ? 16 : 0;
    $excellent += $this->getItem()->getExcellent(5) ? 32 : 0;
    return $this->correct(dechex($excellent));
  }

  private function createbytes9()
  {
    $ancient = $this->getItem()->getAncient()->get($this->getItem()->getSection(), $this->getItem()->getIndex());
    //$ancient = 1;
    switch ($ancient) {
      case 1:
        return "05";
        break;
      case 2:
        return "0A";
        break;
      default:
        return "00";
        break;
    }
  }

  private function createbytes10()
  {
    return substr($this->correct(dechex($this->getItem()->getSection())), 1) . ($this->getItem()->getRefine() ? 8 : 0);
  }

  private function createbytes11()
  {
    $harmony  = dechex($this->getItem()->getHarmony()->getType());
    $harmony .= dechex($this->getItem()->getHarmony()->getLevel());
    return $harmony;
  }

  private function createbytes12to16()
  {
    $sockets = "";
    $max = 3;
    $sockets .= $this->correct(dechex(0));
    return $this->correct($sockets, 10);
  }

  private function createbytes17to21()
  {
    $serial = $this->getItem()->getSerial()->generate();
    if (8 < strlen($serial)) {
      $serial = substr($serial, 8, 8);
    }
    return $this->correct($serial, 8);
  }

  private function correct($string, $size = 2)
  {
    return str_pad($string, $size, 0, STR_PAD_LEFT);
  }

  /**
   * Get the value of item
   *
   * @return  MWOItems\Core\Item;
   */
  public function getItem()
  {
    return $this->item;
  }

  /**
   * Set the value of item
   *
   * @param  MWOItems\Core\Item;  $item
   *
   * @return  self
   */
  public function setItem(Item $item)
  {
    $this->item = $item;

    return $this;
  }
}
