<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

abstract class ApiController extends Controller
{
    /**
     * @var ?User
     */
    protected ?User $user;
    /**
     * @var bool
     */
    protected bool $unauthorized = false;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        if (!$request->hasHeader('Login') || !$request->hasHeader('Auth-Token')) {
            $this->unauthorized = true;
            return;
        }
        $this->user = User::where('login', $request->header('Login'))
            ->where('remember_token', $request->header('Auth-Token'))
            ->first();

        if (empty($this->user)) {
            $this->unauthorized = true;
            return;
        }
    }
}
