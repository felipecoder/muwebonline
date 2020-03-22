<?php

namespace MWOItems\Core;

class KOR
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

  public function parse()
  {
    if (\file_exists($this->getFile()) == false) {
      return "The file {$this->getFile()} not exists";
      exit();
    }

    if (!($file = fopen($this->getFile(), "rb+"))) {
      return "Was not possible to open the file {$this->getFile()}, verify that the file has permissions";
      exit();
    }

    $section = -1;
    $key = null;
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
      if ($section == -1) {
        if (is_numeric($line)) {
          $section = $line;
        }
      } else {
        if (strtolower($line) == "end") {
          $section = -1;
          continue;
        }
        $columns = preg_split("/[\\s,]*\\\"([^\\\"]+)\\\"[\\s,]*|[\\s,]*'([^']+)'[\\s,]*|[\\s,]+/", $line, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        if ($key === null) {
          if (count($columns) <= 19) {
            $key = array(
              "section" => 0,
              "x"       => 1,
              "y"       => 2,
              "name"    => 6
            );
          } else {
            $key = array(
              "section" => 0,
              "x"       => 3,
              "y"       => 4,
              "name"    => 8,
              "skill"   => 2,
              "slot"    => 1
            );
          }
        }

        $data = array(
          "section" => $section,
          "index"   => $columns[0] - 1,
          "name"    => isset($columns[$key["name"]]) ? $columns[$key["name"]] : "",
          "width"   => isset($columns[$key["x"]]) ? $columns[$key["x"]] : 1,
          "height"  => isset($columns[$key["y"]]) ? $columns[$key["y"]] : 1,
          "skill"   => isset($key["skill"]) && isset($columns[$key["skill"]]) ? $columns[$key["skill"]] : 0,
          "slot"    => isset($key["slot"]) && isset($columns[$key["slot"]]) ? $columns[$key["slot"]] : 0
        );

        if ($section >= 0 && $section <= 5) {
          $data += array(
            "damage" => array(
              "min" => $columns[10],
              "max" => $columns[11]
            ),
            "attack_speed" => $columns[12],
            "durability"   => $columns[13],
            "magic"        => array(
              "durability" => $columns[14],
              "damage"     => $columns[15]
            ),
            "requirement" => array(
              "level"     => $columns[16],
              "strength"  => $columns[17],
              "dexterity" => $columns[18],
              "vitality"  => $columns[19],
              "energy"    => $columns[20],
              "command"   => $columns[21]
            ),
            "class" => array(
              "dw" => $columns[23],
              "dk" => $columns[24],
              "fe" => $columns[25],
              "mg" => $columns[26],
              "dl" => $columns[27],
              "su" => isset($columns[28]) ? $columns[28] : null,
              "rf" => isset($columns[29]) ? $columns[29] : null,
              "gl" => isset($columns[30]) ? $columns[30] : null
            )
          );
        } else {
          if ($section > 5 && $section <= 11) {
            $data += array(
              "defense"    => $columns[10],
              "durability" => $columns[12],
              "class"      => array(
                "dw" => $columns[20],
                "dk" => $columns[21],
                "fe" => $columns[22],
                "mg" => $columns[23],
                "dl" => $columns[24],
                "su" => isset($columns[25]) ? $columns[25] : null,
                "rf" => isset($columns[26]) ? $columns[26] : null,
                "gl" => isset($columns[27]) ? $columns[27] : null
              )
            );
            switch ($section) {
              case 6:
                $data += array("defense_success" => $columns[11]);
                break;
              case 7:
                $data += array("magic_defense" => $columns[11]);
                break;
              case 8:
                $data += array("magic_defense" => $columns[11]);
                break;
              case 9:
                $data += array("magic_defense" => $columns[11]);
                break;
              case 10:
                $data += array("attack_speed" => $columns[11]);
                break;
              case 11:
                $data += array("walk_speed" => $columns[11]);
                break;
            }
          } else {
            if ($section == 12) {
              $data += array(
                "defense"     => $columns[10],
                "durability"  => $columns[11],
                "requirement" => array(
                  "level"     => $columns[12],
                  "strength"  => $columns[13],
                  "dexterity" => $columns[14],
                  "energy"    => $columns[15],
                  "command"   => $columns[16]
                ),
                "class" => array(
                  "dw" => $columns[18],
                  "dk" => $columns[19],
                  "fe" => $columns[20],
                  "mg" => $columns[21],
                  "dl" => $columns[22],
                  "su" => isset($columns[23]) ? $columns[23] : null,
                  "rf" => isset($columns[24]) ? $columns[24] : null,
                  "gl" => isset($columns[25]) ? $columns[25] : null
                )
              );
            } else {
              if ($section == 13) {
                $data += array(
                  "durability" => $columns[10],
                  "resistance" => array(
                    "ice"    => $columns[11],
                    "poison" => $columns[12],
                    "light"  => $columns[13],
                    "fire"   => $columns[14],
                    "earth"  => $columns[15],
                    "wind"   => $columns[16],
                    "water"  => $columns[17]
                  ),
                  "class" => array(
                    "dw" => $columns[19],
                    "dk" => $columns[20],
                    "fe" => $columns[21],
                    "mg" => $columns[22],
                    "dl" => $columns[23],
                    "su" => isset($columns[23]) ? $columns[23] : null,
                    "rf" => isset($columns[24]) ? $columns[24] : null,
                    "gl" => isset($columns[25]) ? $columns[25] : null
                  )
                );
              } else {
                if ($section == 15) {
                  $data += array(
                    "requirement" => array(
                      "level"  => $columns[10],
                      "energy" => $columns[11]
                    ),
                    "class" => array(
                      "dw" => $columns[13],
                      "dk" => $columns[14],
                      "fe" => $columns[15],
                      "mg" => $columns[16],
                      "dl" => $columns[17],
                      "su" => isset($columns[18]) ? $columns[18] : null,
                      "rf" => isset($columns[19]) ? $columns[19] : null,
                      "gl" => isset($columns[20]) ? $columns[20] : null
                    )
                  );
                }
              }
            }
          }
        }
        $this->setItems($data);
        //$this->items[$section][$columns[0]] = $data;
      }
    }
    return $this->getItems();
    //return $this->items;
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
   * Get the value of items
   *
   * @return  array
   */
  public function getItems()
  {
    return $this->items;
  }

  /**
   * Set the value of items
   *
   * @param  array  $items
   *
   * @return  self
   */
  public function setItems($items = [])
  {
    $this->items[] = $items;

    return $this;
  }
}
