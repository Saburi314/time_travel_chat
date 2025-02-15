<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Opponent;
use App\Services\UserTokenService;

class HomeController extends Controller
{
    private $userTokenService;

    public function __construct(UserTokenService $userTokenService)
    {
        $this->userTokenService = $userTokenService;
    }

    public function index(Request $request)
    {
        // 🔹 `user_token` をサービスから取得
        $userToken = $this->userTokenService->getUserToken($request);

        // 🔹 DB から `opponents` を取得
        $opponents = Opponent::all();

        return view('home', compact('opponents', 'userToken'));
    }
}
