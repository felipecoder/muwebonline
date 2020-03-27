<?php

use App\Controllers\GMark;
use App\Database\DefaultDatabase;

$app->get("/logochar/{name}", function ($request, $response, $args) {

    $data = new DefaultDatabase();
    $logo = $data->getImageCharacter($args['name']);
    $logo = ($logo['mwo_image'] == NULL) ? getenv("DIRIMG") . 'users/default.png' : getenv("DIRIMG") . 'users/' . $logo['mwo_image'];
    readfile(getenv("SITE_LINK") . $logo);
});

$app->get("/logoguild/{mark}/{size}", function ($request, $response, $args) {

    $logo = new GMark();
    return $logo->setMark($args['mark'])->setSize($args['size'])->toImage();
});
