<?php

use \laralike\LaralikeRouter as Route;

Route::get('/', function () {
  return 'Hello World Laralike Framework !';
});

Route::prefix('test')->group(function () {
  Route::get('/', 'TestController@index');
  Route::get('/view', 'TestController@view');
});
