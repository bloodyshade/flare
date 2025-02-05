<?php

namespace App\Game\Core\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class GameController extends Controller {

    public function __construct() {
        $this->middleware('auth');
    }

    public function game() {
        return view('game.game', [
            'user' => auth()->user(),
        ]);
    }
}
