<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    // Base controller extends Laravel routing controller so middleware() works
}
