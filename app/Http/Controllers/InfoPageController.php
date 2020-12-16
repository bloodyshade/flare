<?php

namespace App\Http\Controllers;

use App\Flare\Models\Adventure;
use App\Flare\Models\GameClass;
use App\Flare\Models\GameRace;
use App\Flare\Models\GameSkill;
use Cache;
use Illuminate\Http\Request;
use Storage;

class InfoPageController extends Controller
{

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function viewPage(Request $request, string $pageName)
    {
        $files = Storage::disk('info')->files($pageName);

        if (empty($files)) {
            abort(404);
        }

        if (is_null(config('info.' . $pageName))) {
            abort(404);
        }

        $sections = [];

        for ($i = 0; $i < count($files); $i++) {
            if (explode('.', $files[$i])[1] === 'md') {
                $view     = null;
                $livewire = false;
                $only     = null;
                $index    = $i === 0 ? 0 : $i;
                $before   = null;

                if (isset(config('info.' . $pageName)[$index])) {
                    $view     = config('info.' . $pageName)[$index]['view'];
                    $livewire = config('info.' . $pageName)[$index]['livewire'];
                    $only     = config('info.' . $pageName)[$index]['only'];
                    $before   = config('info.' . $pageName)[$index]['insert_before_table'];
                }

                $sections[] = [
                    'content'  => Storage::disk('info')->get($files[$i]),
                    'view'     => $view,
                    'livewire' => $livewire,
                    'only'     => $only,
                    'before'   => $before,
                ];
            }
        }

        //dd($sections);

        return view('information.core', [
            'pageTitle' => $pageName,
            'sections'  => $sections,
        ]);
    }

    public function viewRace(Request $request, GameRace $race) {
        return view('information.races.race', [
            'race' => $race,
        ]);
    }

    public function viewClass(Request $request, GameClass $class) {
        return view('information.classes.class', [
            'class' => $class,
        ]);
    }

    public function viewSkill(Request $request, GameSkill $skill) {
        return view('information.skills.skill', [
            'skill' => $skill,
        ]);
    }

    public function viewAdventure(Request $request, Adventure $adventure) {

        return view('information.adventures.adventure', [
            'adventure' => $adventure,
        ]);
    }
}
