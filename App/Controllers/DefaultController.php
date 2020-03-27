<?php

namespace App\Controllers;

use App\Database\DefaultDatabase;

class DefaultController
{
    public function getAll()
    {
        //Classes
        $data = new DefaultDatabase();

        //variables
        $config_details = $data->getConfig('details');
        $config_details = json_decode($config_details, true);
        $social_link    = $data->getConfig('sociallinks');
        $social_link    = json_decode($social_link, true);
        $config_captcha = $data->getConfig('captcha');
        $config_captcha = json_decode($config_captcha, true);

        return array(
            'menus'           => $this->getMenus(),
            'rankings'        => $this->getRankings(),
            'coins_user'      => $this->getCoins(),
            'events_json'     => $this->getEvents('json'),
            'events_array'    => $this->getEvents(),
            'kingofmu'        => $this->getKingOfMu(),
            'castlesiege'     => $this->getCastleSiege(),
            'total_onlines'   => $data->getTotalOnline(),
            'staff'           => $data->getStaff(),
            'slides'          => $data->getSlides(),
            'title_site'      => $config_details[0]['value'],
            'server_name'     => $config_details[2]['value'],
            'server_slogan'   => $config_details[3]['value'],
            'server_version'  => $config_details[4]['value'],
            'server_drop'     => $config_details[5]['value'],
            'server_xp'       => $config_details[6]['value'],
            'server_bugbless' => $config_details[7]['value'],
            'facebook_link'   => $social_link[0]['value'],
            'twitter_link'    => $social_link[1]['value'],
            'instagram_link'  => $social_link[2]['value'],
            'discord_link'    => $social_link[3]['value'],
            'youtube_link'    => $social_link[4]['value'],
            'whatsapp_link'   => $social_link[5]['value'],
            'teamspeak_link'  => $social_link[6]['value'],
            'recaptcha_site'  => $config_captcha[1]['value'],
        );
    }

    public function getMenus()
    {
        //Classes
        $data = new DefaultDatabase();

        //Variables
        $menus = $data->getMenus(0);

        if (empty($menus)) {
            $menus_return = NULL;
        } else {
            foreach ($menus as $key => $value) {
                $menus_return[] = array(
                    'ID'       => $value['ID'],
                    'name'     => $value['name'],
                    'label'    => $value['label'],
                    'link'     => $value['link'],
                    'parentid' => $value['parentid'],
                    'status'   => $value['status'],
                    'children' => $data->getMenus($value['ID'])
                );
            }
        }

        return $menus_return;
    }

    public function getRankings()
    {
        //Classes
        $data = new DefaultDatabase();

        //Variables
        $rankings = $data->getRankings();

        if (empty($rankings)) {
            $rankings_return = NULL;
        } else {
            foreach ($rankings as $key => $value) {
                $rankings_return[] = array(
                    'ID'      => $value['ID'],
                    'name'    => $value['name'],
                    'column'  => $value['column'],
                    'type'    => $value['type'],
                    'ranking' => $data->createRanking($value['database'], $value['table'], $value['column'], $value['max'], $value['custom'])
                );
            }
        }

        return $rankings_return;
    }

    public function getCoins()
    {
        //Classes
        $data = new DefaultDatabase();

        //Variables
        $coins = $data->getCoinsConfigs();
        $user  = (isset($_SESSION['usernameuser'])) ? $data->getUser($_SESSION['usernameuser']) : NULL;

        if (isset($_SESSION['usernameuser'])) {
            if (empty($coins)) {
                $coins_return = NULL;
            } else {
                foreach ($coins as $key => $value) {
                    $coins_return[] = array(
                        'ID'     => $value['ID'],
                        'name'   => $value['name'],
                        'column' => $value['column'],
                        'value'  => $data->getCoinsUser($value['database'], $value['table'], $value['column'], $user['memb___id'])
                    );
                }
            }
        } else {
            $coins_return = NULL;
        }

        return $coins_return;
    }

    public function getEvents($type = 'array')
    {
        //Classes
        $data = new DefaultDatabase();

        //Variables
        $events = $data->getEvents();

        if (empty($events)) {
            $events_return = NULL;
        } else {
            foreach ($events as $key => $value) {
                $events_return[] = array(
                    0 => $value['name'],
                    1 => explode(",", $value['time']),
                );
            }
        }

        switch ($type) {
            case 'array':
                return $events_return;
                break;
            case 'json':
                return json_encode($events_return);
                break;

            default:
                return $events_return;
                break;
        }
    }

    public function getKingOfMu()
    {
        //Classes
        $data = new DefaultDatabase();

        //Variables
        $getkingofmu  = $data->getKingOfMu();
        $config_class = $data->getConfig('classcodes');
        $config_class = json_decode($config_class, true);

        if ($getkingofmu['active'] == 1) {
            if ($getkingofmu['mode'] == 'manual') {
                $kingofmu = $data->getCharacterKingManual($getkingofmu['database'], $getkingofmu['table'], $getkingofmu['character']);
                foreach ($config_class as $key => $value) {
                    if ($kingofmu['Class'] == $value['value']) {
                        $class_character = $value['label'];
                        break;
                    } else {
                        $class_character = 'Unknow';
                    }
                }

                $wins = array('wins' => $getkingofmu['wins']);
                $classname = array('classname' => $class_character);
                $active = array('active' => $getkingofmu['active']);
                $kingofmu = array_merge($kingofmu, $wins, $classname, $active);
            } else {
                $kingofmu = $data->getCharacterKingAuto($getkingofmu['database'], $getkingofmu['table'], $getkingofmu['custom'], $getkingofmu['orderby']);
                foreach ($config_class as $key => $value) {
                    if ($kingofmu['Class'] == $value['value']) {
                        $class_character = $value['label'];
                        break;
                    } else {
                        $class_character = 'Unknow';
                    }
                }

                $wins = array('wins' => $getkingofmu['wins']);
                $classname = array('classname' => $class_character);
                $active = array('active' => $getkingofmu['active']);
                $kingofmu = array_merge($kingofmu, $wins, $classname, $active);
            }
        } else {
            $kingofmu = array('active' => $getkingofmu['active']);
        }

        return $kingofmu;
    }

    public function getCastleSiege()
    {
        //Classes
        $data = new DefaultDatabase();

        //Variables
        $castlesiege = $data->getCastleSiege();

        if ($castlesiege['active'] == 1) {
            if ($castlesiege['mode'] == 'manual') {
                $getcastlesiege = $data->getCastleSiegeManual($castlesiege['guild']);
                $logo = getenv("DIR") . "logoguild/" . $getcastlesiege["G_Mark"] . "/" . 120;

                $logo = array("logo" => $logo);
                $members = array("TotalMembers" => $data->getTotalMembersGuild($getcastlesiege['G_Name']));

                $castlesiegereturn = array_merge($castlesiege, $getcastlesiege, $logo, $members);
            } else {
                $getcastlesiege = $data->getCastleSiegeAuto();
                $logo = getenv("DIR") . "logoguild/" . $getcastlesiege["G_Mark"] . "/" . 120;

                $logo = array("logo" => $logo);

                $castlesiegereturn = array_merge($castlesiege, $getcastlesiege, $logo);
            }
        }

        return $castlesiegereturn;
    }
}
