<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Constants\Opponents;
use App\Services\SessionService;

class HomeController extends Controller
{
    private $sessionService;

    public function __construct(SessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    public function index()
    {
        // セッションリセット（チャット履歴を削除）
        $this->sessionService->invalidateSession();

        return view('home', ['opponents' => Opponents::all()]);
    }
}
