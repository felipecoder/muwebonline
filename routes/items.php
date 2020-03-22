<?php

use MWOItems\Core\Item;
use MWOItems\Items;

$app->get("/teste", function ($request, $response, $args) {
  $connection = array(
    "MSSQL_DRIVER" => getenv('MSSQL_DRIVER'),
    "MSSQL_HOST"   => getenv('MSSQL_HOST'),
    "MSSQL_PORT"   => getenv('MSSQL_PORT'),
    "MSSQL_USER"   => getenv('MSSQL_USER'),
    "MSSQL_PASS"   => getenv('MSSQL_PASS'),
    "MSSQL_DBNAME" => getenv('MSSQL_DBNAME'),
  );
  $mwoitems = new Items($connection);

  $items = $mwoitems->getItemsKOR('items/Item.txt')->parse();
  /*$total = count($items);
  $section = array(0, 0);
  $index = array(2, 14);
  for ($i = 0; $i < $total; $i++) {
    $section[] = $items[$i]["section"];
    $index[] = $items[$i]["id"];
    //echo "{$items[$i]["name"]} - {$items[$i]["section"]} - {$items[$i]["id"]} <br />";
  }
  
*/
  //$serial = $mwoitems->getItemSerial()->generate();

  $itemarray = array(
    "section"    => 0,
    "index"      => 0,
    "durability" => 255,
    "level"      => 15,
    "skill"      => false,
    "luck"       => true,
    "option"     => 7 * 4,
    "refine"     => false,
    "harmony"    => array(
      "type"  => 1,
      "level" => 2
    ),
    "excellents" => array(true, true, true, true, true, true),
    "sockets" => array(
      1 => array(
        "active" => false,
        "option" => 0
      ),
      2 => array(
        "active" => false,
        "option" => 0
      ),
      3 => array(
        "active" => false,
        "option" => 0
      ),
      4 => array(
        "active" => false,
        "option" => 0
      ),
      5 => array(
        "active" => false,
        "option" => 0
      ),
    )
  );

  //$item = $mwoitems->getItem($itemarray, 2);

  //$acients = $mwoitems->getItemAncient($item)->generate('items/SetItemType.txt', 'items/SetItemOption.txt');
  //$harmony = $mwoitems->getItemHarmony($item)->generate(array('items/JewelOfHarmonyType.txt', 'items/JewelOfHarmonyOption.txt'));

  //$options = $mwoitems->getItemsUtil(array('items/ItemOption.txt', 'items/ItemOptionName.txt'))->getoptiontype();
  //$sockets = $mwoitems->getItemsUtil(array('items/SocketItemType.txt', 'items/SocketItemOption.txt'))->getsockettype();
  //$refines = $mwoitems->getItemsUtil(array('items/380ItemType.txt', 'items/380ItemOption.txt'))->getrefinetype();
  //$skills = $mwoitems->getItemsUtil('items/Skill.txt')->getskills();

  echo "<pre>";
  print_r($items);

  $string = "Sword of Assassin";
  //$string = urlencode(utf8_encode($string));
  //$string = str_replace("+", "_", $string);
  //echo strtolower(str_replace("+", "_", urlencode(utf8_encode($string))));
});
