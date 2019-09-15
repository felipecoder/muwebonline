<?php

use function src\slim;

//Variables
$app         = new \Slim\App(slim());
$container   = $app->getContainer();
$whoopsGuard = new \Zeuxisoo\Whoops\Provider\Slim\WhoopsGuard();
$whoopsGuard->setApp($app);
$whoopsGuard->setRequest($container['request']);
$whoopsGuard->install();

$container['view'] = function ($container) {
  $view = new \Slim\Views\Twig('views', [
    'debug' => true
  ]);

  $router = $container->get('router');
  $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
  $view->addExtension(new \Slim\Views\TwigExtension($router, $uri));

  return $view;
};

$container['flash'] = function () {
  return new \Slim\Flash\Messages();
};

function base_path()
{
  $uri = $_SERVER["REQUEST_URI"];
  return substr($uri, 0, strpos($uri, "index.php"));
}

$app->get("/", function ($request, $response, $args) {
  if (version_compare(PHP_VERSION, '5.6.0', '>=')) {
    $phpversion = true;
  } else {
    $phpversion = false;
  }

  if (extension_loaded('curl')) {
    $curl = true;
  } else {
    $curl = false;
  }

  if (function_exists('zip_open')) {
    $zip = true;
  } else {
    $zip = false;
  }

  if (ini_get('allow_url_fopen')) {
    $allow = true;
  } else {
    $allow = false;
  }

  $drivers = array();
  foreach (array("pdo_sqlsrv", "pdo_dblib", "pdo_odbc") as $driver) {
    if (extension_loaded($driver)) {
      $drivers[] = $driver;
    }
  }

  if (!empty($drivers)) {
    $pdo = true;
    $list_drivers = join(',', $drivers);
  } else {
    $pdo = false;
    $list_drivers = NULL;
  }


  $this->view->render($response, 'home.html', [
    'phpversion'   => $phpversion,
    'curl'         => $curl,
    'zip'          => $zip,
    'allow'        => $allow,
    'pdo'          => $pdo,
    'list_drivers' => $list_drivers,
    'base'         => base_path(),
  ]);
});

$app->map(['GET', 'POST'], "/config", function ($request, $response, $args) {
  if ($request->isPost()) {
    $post     = $request->getParsedBody();
    $driver   = $post['driver'];
    $host     = $post['host'];
    $port     = $post['port'];
    $username = $post['username'];
    $password = $post['password'];
    $dbname   = $post['dbname'];
    $domain   = $post['domain'];
    $sitelink = $post['sitelink'];
    $dir      = $post['dir'];
    $diradmin = $post['diradmin'];
    $dirimg   = $post['dirimg'];
    $dirlogs  = $post['dirlogs'];

    //create database file
    $database = fopen($_SERVER['DOCUMENT_ROOT'] . "/src/database.php", "w") or die("Unable to open file!");
    $txt = "<?php\n";
    fwrite($database, $txt);
    $txt = "putenv('MSSQL_DRIVER={$driver}');\n";
    fwrite($database, $txt);
    $txt = "putenv('MSSQL_HOST={$host}');\n";
    fwrite($database, $txt);
    $txt = "putenv('MSSQL_PORT={$port}');\n";
    fwrite($database, $txt);
    $txt = "putenv('MSSQL_USER={$username}');\n";
    fwrite($database, $txt);
    $txt = "putenv('MSSQL_PASS={$password}');\n";
    fwrite($database, $txt);
    $txt = "putenv('MSSQL_DBNAME={$dbname}');\n";
    fwrite($database, $txt);
    fclose($database);

    //create app file
    $appfile = fopen($_SERVER['DOCUMENT_ROOT'] . "/src/app.php", "w") or die("Unable to open file!");
    $txt = "<?php\n";
    fwrite($appfile, $txt);
    $txt = "putenv('DISPLAY_ERRORS=false');\n";
    fwrite($appfile, $txt);
    $txt = "putenv('DEBUG_BAR=false');\n";
    fwrite($appfile, $txt);
    $txt = "putenv('USE_IP_LOGIN=true');\n";
    fwrite($appfile, $txt);
    $txt = "putenv('DOMAIN={$domain}');\n";
    fwrite($appfile, $txt);
    $txt = "putenv('SITE_LINK={$sitelink}');\n";
    fwrite($appfile, $txt);
    $txt = "putenv('DIR={$dir}');\n";
    fwrite($appfile, $txt);
    $txt = "putenv('DIRADMIN={$diradmin}');\n";
    fwrite($appfile, $txt);
    $txt = "putenv('DIRIMG={$dirimg}');\n";
    fwrite($appfile, $txt);
    $txt = "putenv('DIRLOGS={$dirlogs}');\n";
    fwrite($appfile, $txt);
    fclose($appfile);

    try {
      switch ($driver) {
        case 'odbc':
          $dsn = "odbc:Driver={SQL Native Client};Server={$host};Port={$port};Database={$dbname}; Uid={$username};Pwd={$password};";
          $pdo = new PDO($dsn);
          break;
        case 'sqlsrv':
          $dsn = "{$driver}:server={$host},{$port};Database={$dbname}";
          $pdo = new PDO($dsn, $username, $password);
          break;
        case 'dblib':
          $dsn = "{$driver}:host={$host}:{$port};dbname={$dbname}";
          $pdo = new PDO($dsn, $username, $password);
          break;

        default:
          $dsn = "{$driver}:server={$host},{$port};Database={$dbname}";
          $pdo = new PDO($dsn, $username, $password);
          break;
      }

      $sql = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "sql" . DIRECTORY_SEPARATOR . "mwoinstall.sql");
      $queries = explode(";", $sql);
      foreach ($queries as $query) {
        $query = trim($query);
        if (empty($query)) {
          continue;
        }
        $data = $pdo->prepare($query);
        $data->execute();
      }

      $return = array(
        'error' => false,
        'success' => true,
        'message' => 'Configuração da aplicação e do banco de dados realizada com sucesso'
      );

      $this->flash->addMessage('response', $return);
      return $response->withRedirect(base_path() . 'index.php/admin', 301);
      exit();
    } catch (PDOException $e) {
      $return = array(
        'error' => true,
        'success' => false,
        'message' => 'Não foi possível conectar no banco de dados <br> ERROR: ' . $e->getMessage() . ''
      );

      $this->flash->addMessage('response', $return);
      return $response->withRedirect(base_path() . 'index.php/config', 301);
      exit();
    }
  } else {
    $messages = $this->flash->getMessages();

    if (isset($messages['response'])) {
      $return = $messages['response'];
    } else {
      $return = NULL;
    }
    $this->view->render($response, 'config.html', [
      'base' => base_path(),
      'return' => $return
    ]);
  }
});

$app->map(['GET', 'POST'], "/admin", function ($request, $response, $args) {
  require_once '../src/database.php';
  if ($request->isPost()) {
    $post = $request->getParsedBody();

    if (
      empty($post['username']) or
      empty($post['password']) or
      empty($post['repassword']) or
      empty($post['ipaddress'])
    ) {
      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'Prencha todos os campos'
      );

      $this->flash->addMessage('response', $return);
      return $response->withRedirect(base_path() . 'index.php/admin', 301);
      exit();
    }

    $username   = $post['username'];
    $password   = $post['password'];
    $repassword = $post['repassword'];
    $ipaddress  = $post['ipaddress'];
    $driver     = getenv('MSSQL_DRIVER');
    $host       = getenv('MSSQL_HOST');
    $port       = getenv('MSSQL_PORT');
    $user       = getenv('MSSQL_USER');
    $pass       = getenv('MSSQL_PASS');
    $dbname     = getenv('MSSQL_DBNAME');

    if (strlen($password) < 4) {
      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'A senha é muita curta'
      );

      $this->flash->addMessage('response', $return);
      return $response->withRedirect(base_path() . 'index.php/admin', 301);
      exit();
    } elseif (strlen($repassword) < 4) {
      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'A confirmação de senha é muita curta'
      );

      $this->flash->addMessage('response', $return);
      return $response->withRedirect(base_path() . 'index.php/admin', 301);
      exit();
    } elseif ($password != $repassword) {
      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'As senhas não conferem'
      );

      $this->flash->addMessage('response', $return);
      return $response->withRedirect(base_path() . 'index.php/admin', 301);
      exit();
    }

    try {
      switch ($driver) {
        case 'odbc':
          $dsn = "odbc:Driver={SQL Native Client};Server={$host};Port={$port};Database={$dbname}; Uid={$user};Pwd={$pass};";
          $pdo = new PDO($dsn);
          break;
        case 'sqlsrv':
          $dsn = "{$driver}:server={$host},{$port};Database={$dbname}";
          $pdo = new PDO($dsn, $user, $pass);
          break;
        case 'dblib':
          $dsn = "{$driver}:host={$host}:{$port};dbname={$dbname}";
          $pdo = new PDO($dsn, $user, $pass);
          break;

        default:
          $dsn = "{$driver}:server={$host},{$port};Database={$dbname}";
          $pdo = new PDO($dsn, $user, $pass);
          break;
      }

      $data = $pdo->prepare("INSERT INTO mwo_accesspanel (username,password,access, ipaddress) VALUES ( :username, :password, '1', :ipaddress)");
      $data->bindValue(':username', $username);
      $data->bindValue(':password', password_hash($password, PASSWORD_DEFAULT));
      $data->bindValue(':ipaddress', $ipaddress);
      if ($data->execute()) {
        return $response->withRedirect(base_path() . 'index.php/success', 301);
        exit();
      } else {
        echo "Erro ao cadastrar";
        print_r($data->errorInfo());
      }
    } catch (PDOException $e) {
      $return = array(
        'error' => true,
        'success' => false,
        'message' => 'Não foi possível criar sua conta <br> ERROR: ' . $e->getMessage() . ''
      );

      $this->flash->addMessage('response', $return);
      return $response->withRedirect(base_path() . 'index.php/admin', 301);
      exit();
    }
  } else {
    $messages = $this->flash->getMessages();

    if (isset($messages['response'])) {
      $return = $messages['response'];
    } else {
      $return = NULL;
    }
    $this->view->render($response, 'admin.html', [
      'base'      => base_path(),
      'ipaddress' => $request->getServerParam('REMOTE_ADDR'),
      'return'    => $return,
    ]);
  }
});

$app->get("/success", function ($request, $response, $args) {
  require_once '../src/app.php';
  $base  = getenv('DIR');
  $admin = getenv('DIRADMIN');

  $this->view->render($response, 'success.html', [
    'base'  => $base,
    'admin' => $admin,
  ]);
});

$app->run();
