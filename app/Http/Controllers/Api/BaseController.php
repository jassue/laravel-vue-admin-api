<?php

namespace App\Http\Controllers\Api;

use App\Domain\Common\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    use ApiResponse;
}
