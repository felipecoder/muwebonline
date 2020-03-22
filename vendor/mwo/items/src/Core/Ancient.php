<?php

namespace MWOItems\Core;

class Ancient
{
  /**
   * @var string
   */
  private $filetype;

  /**
   * @var string
   */
  private $fileoption;

  /**
   * @var string
   */
  private $_connection;

  public function __construct(Item $item)
  {
    $this->_connection = $item->getConnection();
  }

  private function get($section, $index)
  {
    //Variables
    $data = new Database($this->_connection);
    $ancient = $data->getancient($section, $index);

    if (isset($ancient['ancient']) && !empty($ancient)) {
      return $ancient['ancient'];
    } else {
      return 0;
    }
  }

  public function generate($filetype, $fileoption)
  {
    //Set Files
    $this->setFiletype($filetype)
      ->setFileoption($fileoption);

    //Variable
    $ancients = $this->read();

    return $ancients;
  }

  public function read()
  {

    if (!file_exists($this->getFiletype())) {
      return "The file {$this->getFiletype()} not exists";
      exit();
    }
    if (!file_exists($this->getFileoption())) {
      return "The file {$this->getFileoption()} not exists";
      exit();
    }
    $data = array();
    if (!($file = fopen($this->getFiletype(), "rb+"))) {
      return "Was not possible to open the file {$this->getFiletype()}, verify that the file has permissions";
      exit();
    }
    while (!feof($file)) {
      $types = fscanf($file, "%d %d %d %d %d");
      if (isset($types[0]) && !strpos($types[0], "//")) {
        if (!($file2 = fopen($this->getFileoption(), "rb+"))) {
          return "Was not possible to open the file {$this->getFileoption()}, verify that the file has permissions";
          exit();
        }
        while (!feof($file2)) {
          $infos = fscanf($file2, "%d \"%[^\"]\" %d %d %d %d %d %d %d %d %d %d %d %d %d %d %d %d %d %d %d %d %d %d %d %d %d %d %d %d %d %d %d %d %d");
          if (isset($infos[0]) && !strpos($infos[0], "//") && in_array($infos[0], array($types[3], $types[4]))) {
            $ix = $infos[0] == $types[3] ? 1 : 2;
            $data[] = array("section" => (int) $types[0], "index" => (int) $types[1], "ancient" => (int) $ix, "name" => $infos[1]);
          }
        }
      }
    }
    fclose($file);
    if (isset($file2)) {
      fclose($file2);
    }
    return $data;
  }

  /**
   * Get the value of filetype
   *
   * @return  string
   */
  public function getFiletype()
  {
    return $this->filetype;
  }

  /**
   * Set the value of filetype
   *
   * @param  string  $filetype
   *
   * @return  self
   */
  public function setFiletype($filetype)
  {
    $this->filetype = $filetype;

    return $this;
  }

  /**
   * Get the value of fileoption
   *
   * @return  string
   */
  public function getFileoption()
  {
    return $this->fileoption;
  }

  /**
   * Set the value of fileoption
   *
   * @param  string  $fileoption
   *
   * @return  self
   */
  public function setFileoption($fileoption)
  {
    $this->fileoption = $fileoption;

    return $this;
  }
}
