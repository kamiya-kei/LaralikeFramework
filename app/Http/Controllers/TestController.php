<?php

namespace App\Http\Controllers;

class TestController
{
  public function __construct()
  {
  }

  public function index()
  {
    return 'TEST ROOT!';
  }

  public function view()
  {
    return view('test', ['name' => 'lala']);
  }
}
