<?php

ob_start();
session_start();

require_once 'vendor/autoload.php';
require_once 'src/app.php';
require_once 'src/database.php';
require_once 'src/slim.php';
require_once 'src/update.php';
require_once 'routes/load.php';
