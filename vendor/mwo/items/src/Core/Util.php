<?php

namespace MWOItems\Core;

class Util
{
  /**
   * @var string
   */
  private $file;

  /**
   * @var array
   */
  private $items = array();

  public function __construct($file)
  {
    $this->setFile($file);
  }

  public function getsockettype()
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
        $data[] = array(
          "section" => $info[0],
          "index"   => $info[1],
          "max"     => $info[2],
          "sockets" => json_encode($this->getsocketoption($info[0], $info[2])),
        );
      }
    }
    fclose($file);
    return $data;
  }

  private function getsocketoption($section, $max = 5)
  {
    $data = array();
    if ($section <= 5) {
      $allow = array(1, 3, 5);
    } else {
      $allow = array(2, 4, 6);
    }

    if (\file_exists($this->getFile()[1]) == false) {
      return "The file {$this->getFile()[1]} not exists";
      exit();
    }

    if (!($file = fopen($this->getFile()[1], "rb+"))) {
      return "Was not possible to open the file {$this->getFile()}, verify that the file has permissions";
      exit();
    }

    $category = -1;
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
          break;
        }
        $info = preg_split("/[\\s,]*\\\"([^\\\"]+)\\\"[\\s,]*|[\\s,]*'([^']+)'[\\s,]*|[\\s,]+/", $line, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        for ($i = 0; $i < $max; $i++) {
          $value = $info[0] + $i * 50;
          $initial = 4;
          if (9 < count($info)) {
            $initial = 5;
          }
          $option = $info[$i + $initial];
          if (in_array($info[1], $allow)) {
            $type = "-";
            switch ($info[1]) {
              case 1:
                $type = "Fire";
                break;
              case 2:
                $type = "Water";
                break;
              case 3:
                $type = "Ice";
                break;
              case 4:
                $type = "Wind";
                break;
              case 5:
                $type = "Lightning";
                break;
              case 6:
                $type = "Earth";
                break;
            }
            $complement = "";
            if (in_array($info[0], array(0, 5, 10, 12, 13, 14, 16, 17, 20, 22, 23, 30, 32))) {
              $complement = "%";
            }
            if (!isset($data[$type])) {
              $data[$type] = array();
            }
            $data[$type][] = array("type" => $type, "name" => $info[3] . " +" . $option . $complement, "value" => $value);
          }
        }
      }
    }

    return $data;
  }

  public function getoptiontype()
  {
    if (\file_exists($this->getFile()[0]) == false) {
      return "The file {$this->getFile()[0]} not exists";
      exit();
    }

    if (!($file = fopen($this->getFile()[0], "rb+"))) {
      return "Was not possible to open the file {$this->getFile()[0]}, verify that the file has permissions";
      exit();
    }

    $options = array();
    while (!feof($file)) {
      $info = fscanf($file, "%d %d %d %d %d %s %s %s %s");
      if (!strpos($info[0], "//") && isset($info[0])) {
        $options[] = array(
          "index"       => $info[0],
          "optionindex" => $info[1],
          "value"       => $info[2],
          "minrange"    => $info[3],
          "maxrange"    => $info[4],
          "skill"       => $info[5],
          "luck"        => $info[6],
          "option"      => $info[7],
          "newoption"   => $info[8],
          "name"        => (isset($this->getoptionname()[$info[1]])) ? $this->getoptionname()[$info[1]]["name"] : "",
        );
      }
    }

    return $options;
  }

  private function getoptionname()
  {
    if (\file_exists($this->getFile()[1]) == false) {
      return "The file {$this->getFile()[1]} not exists";
      exit();
    }

    if (!($file = fopen($this->getFile()[1], "rb+"))) {
      return "Was not possible to open the file {$this->getFile()[1]}, verify that the file has permissions";
      exit();
    }

    $names = array();
    while (!feof($file)) {
      $info = fscanf($file, "%d %d \"%[^\"]\"");
      if (!strpos($info[0], "//") && isset($info[0])) {
        $names[$info[1]] = array(
          "index"  => $info[0],
          "option" => $info[1],
          "name"   => $info[2],
        );
      }
    }

    return $names;
  }

  public function getrefinetype()
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
      $info = fscanf($file, "%d %d %d %s %d %s");

      if (!strpos($info[0], "//") && isset($info[0])) {
        $data[] = array(
          "section" => $info[0],
          "index"   => $info[1],
          "options" => json_encode(array(
            1 => array(
              "option" => $info[2],
              "value"  => $info[3],
              "name"   => $this->getrefineoption($info[2]),
            ),
            2 => array(
              "option" => $info[4],
              "value"  => $info[5],
              "name"   => $this->getrefineoption($info[4]),
            ),
          )),
        );
      }
    }
    fclose($file);
    return $data;
  }

  private function getrefineoption($section)
  {

    if (\file_exists($this->getFile()[1]) == false) {
      return "The file {$this->getFile()[1]} not exists";
      exit();
    }

    if (!($file = fopen($this->getFile()[1], "rb+"))) {
      return "Was not possible to open the file {$this->getFile()[1]}, verify that the file has permissions";
      exit();
    }

    while (!feof($file)) {
      $info = fscanf($file, "%d \"%[^\"]\" %d");
      if ($section == $info[0]) {
        $data = array(
          "section" => $info[0],
          "name"    => $info[1],
          "value"   => $info[2],
        );
      }
    }
    fclose($file);
    return $data;
  }

  public function getskills()
  {
    $data = array();

    if (\file_exists($this->getFile()) == false) {
      return "The file {$this->getFile()} not exists";
      exit();
    }

    if (!($file = fopen($this->getFile(), "rb+"))) {
      return "Was not possible to open the file {$this->getFile()}, verify that the file has permissions";
      exit();
    }

    while (!feof($file)) {
      $info = fscanf($file, "%d \"%[^\"]\" %d %d %d");
      if (!strpos($info[0], "//") && isset($info[0])) {
        $data[] = array(
          "index" => $info[0],
          "name"  => $info[1] . (0 < $info[3] ? " (Mana:" . $info[3] . ")" : ""),
        );
      }
    }
    fclose($file);
    return $data;
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
}
