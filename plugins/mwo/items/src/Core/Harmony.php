<?php

namespace MWOItems\Core;

class Harmony
{
  /**
   * @var MWOItems\Core\Item;
   */
  private $item;

  /**
   * @var string
   */
  private $file;

  /**
   * @var string
   */
  private $_connection;

  /**
   * @var array
   */
  private $options;

  /**
   * @var string
   */
  private $type;

  /**
   * @var string
   */
  private $level;

  public function __construct(Item $item)
  {
    $this->setItem($item)
      ->_connection = $item->getConnection();
  }

  public function generate($file)
  {
    //Set Files
    $this->setFile($file);

    //Variable
    for ($i = 1; $i < 4; $i++) {
      $this->getharmonyoption($i);
    }
    $harmonys = $this->getharmonytype();

    return $harmonys;
  }

  public function getharmonytype()
  {
    $data = array();

    if (\file_exists($this->getFile()[0]) == false) {
      return "The file {$this->getFile()[0]} not exists";
      exit();
    }

    if (!($file = fopen($this->getFile()[0], "rb+"))) {
      return "Was not possible to open the file {$this->getFile()}, verify that the file has permissions";
      exit();
    }

    while (!feof($file)) {
      $info = fscanf($file, "%d %d %d");

      if (!strpos($info[0], "//") && isset($info[0])) {
        if ($info[0] < 5) {
          $hsection = 1;
        } else {
          if ($info[0] == 5) {
            $hsection = 2;
          } else {
            $hsection = 3;
          }
        }

        $data[] = array(
          "section"  => $info[0],
          "index"    => $info[1],
          "level"    => $info[2],
          "harmonys" => json_encode($this->getOptions()[$hsection]),
        );
      }
    }
    fclose($file);
    return $data;
  }

  public function getharmonyoption($hsection)
  {

    if (!file_exists($this->getFile()[1])) {
      return "The file {$this->getFile()[1]} not exists";
      exit();
    }

    if (!($file = fopen($this->getFile()[1], "rb+"))) {
      return "Was not possible to open the file {$this->getFiletype()}, verify that the file has permissions";
      exit();
    }

    $category = -1;
    $data = array();
    while (!feof($file)) {
      $line = fgets($file);
      $line = trim($line, " \t\r\n");
      if (substr($line, 0, 2) == "//" || substr($line, 0, 2) == "#" || $line == "") {
        continue;
      }
      if (($pos = strpos($line, "//")) !== false) {
        $line = substr($line, 0, $pos);
      }
      $line = trim($line, " \t\r\n");
      if ($category == -1) {
        if (is_numeric($line)) {
          $category = $line;
        }
      } else {
        if (strtolower($line) == "end") {
          $category = -1;
          continue;
        }
        if ($category == $hsection) {
          $columns = preg_split("/[\\s,]*\\\"([^\\\"]+)\\\"[\\s,]*|[\\s,]*'([^']+)'[\\s,]*|[\\s,]+/", $line, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
          $levels = array();
          $index = 0;
          $i = 1;
          while ($i <= 29) {
            if ($columns[$i] != 0) {
              $levels[] = array("level" => (int) $index, "money" => (int) $columns[$i]);
            }
            $index++;
            $i += 2;
          }
          $data[] = array("index" => (int) $columns[0], "name" => $columns[1] . " +%d", "levels" => $levels);
        } else {
          continue;
        }
      }
    }
    $this->setOptions($hsection, $data);
    return $this->getOptions();
  }

  /**
   * Get the value of file
   *
   * @return  string
   */
  public function getFile()
  {
    return $this->file;
  }

  /**
   * Set the value of file
   *
   * @param  string  $file
   *
   * @return  self
   */
  public function setFile($file)
  {
    $this->file = $file;

    return $this;
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

  /**
   * Get the value of options
   *
   * @return  array
   */
  public function getOptions()
  {
    return $this->options;
  }

  /**
   * Set the value of options
   *
   * @param  array  $options
   *
   * @return  self
   */
  public function setOptions($hsection, $options = [])
  {
    $this->options[$hsection] = $options;

    return $this;
  }

  /**
   * Get the value of type
   *
   * @return  string
   */
  public function getType()
  {
    return $this->type;
  }

  /**
   * Set the value of type
   *
   * @param  string  $type
   *
   * @return  self
   */
  public function setType($type)
  {
    $this->type = $type;

    return $this;
  }

  /**
   * Get the value of level
   *
   * @return  string
   */
  public function getLevel()
  {
    return $this->level;
  }

  /**
   * Set the value of level
   *
   * @param  string  $level
   *
   * @return  self
   */
  public function setLevel($level)
  {
    $this->level = $level;

    return $this;
  }
}
