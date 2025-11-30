<?php

namespace App\Controllers;

class BlockedController extends BaseController
{
    public function index()
    {
        return view('blocked'); // Will load the blocked view
    }
}
