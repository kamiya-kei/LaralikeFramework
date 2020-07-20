<?php

define('LARALIKE_START', microtime(true));
define('BASE_PATH', __DIR__);
// define('PUBLIC_PATH', __DIR__);
define('VIEW_PATH', BASE_PATH.'/resources/views');
define('DB_PATH', BASE_PATH.'/database');
// localhostでアクセスしてる時はAPP_DEBUGがtrueになる
define('APP_DEBUG', strpos($_SERVER['SERVER_NAME'], 'localhost') !== false);
// define('APP_DEBUG', true); // 手動でAPP_DEBUGをtrueにする時用
define('CONFIG_PATH', BASE_PATH.'/config');
define('LOGS_PATH', BASE_PATH.'/storage/logs');

require_once BASE_PATH.'/vendor/autoload.php';

date_default_timezone_set('Asia/Tokyo'); //日本時刻
mb_language('Japanese');
mb_internal_encoding('UTF-8');
if (APP_DEBUG) {
  // デバッグ時
  error_reporting(E_ALL); // 全てのエラーを出力
  ini_set('zend.assertions', '1'); // assertを有効化
  ini_set('assert.exception', '1'); // assertで失敗した時にエラーになるようにする
  // エラー画面を見やすくする - filp/whoops
  $whoops = new Whoops\Run;
  $whoops->pushHandler(new Whoops\Handler\PrettyPageHandler);
  $whoops->register();
} else {
  // 本番環境
  error_reporting(0); // 全てのエラー出力をオフにする
  if (!in_array(ini_get('zend.assertions'), ['-1', '0'])) {
    ini_set('zend.assertions', '0'); // assertを無効化
  }
}

// env() - phpdotenv
$dotenv = Dotenv\Dotenv::createImmutable(CONFIG_PATH, '.env');
$dotenv->load();
function env(string $key, $default=null)
{
  return $_ENV[$key] ?? $default;
}

// view() - twig
function view(string $name, array $parameters = []): string
{
  $loader = new Twig\Loader\FilesystemLoader();
  $loader->addPath(VIEW_PATH);
  $twig = new Twig\Environment($loader, [
    'debug' => APP_DEBUG
  ]);
  $twig_fname = str_replace('.', '/', $name);
  $twig_file = $twig_fname . '.twig';
  if (!file_exists(VIEW_PATH.'/'.$twig_file)) {
    $twig_file = $twig_fname . '.html.twig';
    if (!file_exists(VIEW_PATH.'/'.$twig_file)) {
      assert(false, 'twig file not found: ' . $name);
      exit(0);
    }
  }
  return $twig->render($twig_file, $parameters);
}
// Route - LaralikeRouter
laralike\LaralikeRouter::setView(view::class); // ViewRoute

// DB - Eloquent(illuminate/database)
use Illuminate\Database\Capsule\Manager as DB;
$capsule = new DB;
$connections = require_once CONFIG_PATH.'/database.php';
foreach ($connections as $key => $config) {
  $capsule->addConnection($config, $key);
}
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
$capsule->setEventDispatcher(new Dispatcher(new Container));
$capsule->setAsGlobal();
$capsule->bootEloquent();

// ルーティングは以下ファイルに書く
require_once BASE_PATH.'/routes/web.php';
// require_once BASE_PATH.'/routes/api.php'; // 追加する場合ここに追記
