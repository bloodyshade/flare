<?php

namespace App\Http\Controllers;

use App\Flare\Models\GameBuilding;
use App\Flare\Models\GameMap;
use App\Flare\Models\Npc;
use App\Flare\Models\PassiveSkill;
use App\Flare\Models\Quest;
use App\Flare\Traits\Controllers\MonstersShowInformation;
use App\Flare\Values\ItemEffectsValue;
use App\Flare\Values\LocationEffectValue;
use App\Game\Core\Values\View\ClassBonusInformation;
use Storage;
use Illuminate\Http\Request;
use App\Flare\Models\Adventure;
use App\Flare\Models\GameBuildingUnit;
use App\Flare\Models\GameClass;
use App\Flare\Models\GameRace;
use App\Flare\Models\GameSkill;
use App\Flare\Models\GameUnit;
use App\Flare\Models\Item;
use App\Flare\Models\ItemAffix;
use App\Flare\Models\Location;
use App\Flare\Models\Monster;
use App\Flare\Traits\Controllers\ItemsShowInformation;

class InfoPageController extends Controller
{

    use ItemsShowInformation, MonstersShowInformation;

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

        $files = $this->cleanFiles($files);

        for ($i = 0; $i < count($files); $i++) {
            if (explode('.', $files[$i])[1] === 'md') {
                $view          = null;
                $livewire      = false;
                $only          = null;
                $index         = $i === 0 ? 0 : $i;
                $before        = null;
                $showSkillInfo = false;
                $showDropDown  = false;
                $type          = null;
                $craftOnly     = false;


                $viewAttributes = isset(config('info.' . $pageName)[$index]['view_attributes']) ? config('info.' . $pageName)[$index]['view_attributes'] : null;

                if (isset(config('info.' . $pageName)[$index])) {
                    $view                = config('info.' . $pageName)[$index]['view'];
                    $viewAttributes      = $viewAttributes;
                    $livewire            = config('info.' . $pageName)[$index]['livewire'];
                    $only                = config('info.' . $pageName)[$index]['only'];
                    $type                = config('info.' . $pageName)[$index]['type'];
                    $craftOnly           = config('info.' . $pageName)[$index]['craft_only'];

                    if (isset(config('info.' . $pageName)[$index]['insert_before_table'])) {
                        $before = config('info.' . $pageName)[$index]['insert_before_table'];
                    }

                    if (isset(config('info.' . $pageName)[$index]['showSkillInfo'])) {
                        $showSkillInfo = config('info.' . $pageName)[$index]['showSkillInfo'];
                    }

                    if (isset(config('info.' . $pageName)[$index]['showDropDown'])) {
                        $showDropDown = config('info.' . $pageName)[$index]['showDropDown'];
                    }

                    if (isset(config('info.' . $pageName)[$index]['type'])) {
                        $type = config('info.' . $pageName)[$index]['type'];
                    }
                }

                $sections[] = [
                    'content'         => Storage::disk('info')->get($files[$i]),
                    'view'            => $view,
                    'view_attributes' => $viewAttributes,
                    'livewire'        => $livewire,
                    'only'            => $only,
                    'type'            => $type,
                    'craftOnly'       => $craftOnly,
                    'before'          => $before,
                    'showSkillInfo'   => $showSkillInfo,
                    'showDropDown'    => $showDropDown,
                ];
            }
        }

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
            'classBonus' => (new ClassBonusInformation())->buildClassBonusDetailsForInfo($class->name),
        ]);
    }

    public function viewMap(GameMap $map) {

        $effects = match ($map->name) {
            'Labyrinth'    => ItemEffectsValue::LABYRINTH,
            'Dungeons'     => ItemEffectsValue::DUNGEON,
            'Shadow Plane' => ItemEffectsValue::SHADOWPLANE,
            'Hell'         => ItemEffectsValue::HELL,
            'Purgatory'    => ItemEffectsValue::PURGATORY,
            default        => '',
        };

        return view('information.maps.map', [
            'map'        => $map,
            'itemNeeded' => Item::where('effect', $effects)->first(),
            'mapUrl'     => Storage::disk('maps')->url($map->path),
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

    public function viewMonsters() {
        return view('information.monsters.monsters', [
            'gameMapNames' => GameMap::all()->pluck('name')->toArray(),
        ]);
    }

    public function viewMonster(Request $request, Monster $monster) {
        return $this->renderMonsterShow($monster, 'information.monsters.monster');
    }

    public function viewLocation(Request $request, Location $location) {
        $increasesEnemyStrengthBy = null;
        $increasesDropChanceBy    = 0.0;

        if (!is_null($location->enemy_strength_type)) {
            $increasesEnemyStrengthBy = LocationEffectValue::getIncreaseName($location->enemy_strength_type);
            $increasesDropChanceBy    = (new LocationEffectValue($location->enemy_strength_type))->fetchDropRate();
        }

        $questItemDetails = [];

        if (!is_null($location->questRewardItem)) {
            $questItemDetails = $this->itemShowDetails($location->questRewardItem);
        }

        return view('information.locations.location', array_merge([
            'location'                 => $location,
            'increasesEnemyStrengthBy' => $increasesEnemyStrengthBy,
            'increasesDropChanceBy'    => $increasesDropChanceBy,
        ], $questItemDetails));
    }

    public function viewUnit(Request $request, GameUnit $unit) {
        $belongsToKingdomBuilding = GameBuildingUnit::where('game_unit_id', $unit->id)->first();

        if (!is_null($belongsToKingdomBuilding)) {
            $belongsToKingdomBuilding = $belongsToKingdomBuilding->gameBuilding;
        }

        return view('information.units.unit', [
            'unit'          => $unit,
            'building'      => $belongsToKingdomBuilding,
            'requiredLevel' => GameBuildingUnit::where('game_building_id', $belongsToKingdomBuilding->id)
                                               ->where('game_unit_id', $unit->id)
                                               ->first()->required_level
        ]);
    }

    public function viewBuilding(GameBuilding $building) {
        return view('information.buildings.building', [
            'building' => $building
        ]);
    }

    public function viewItem(Request $request, Item $item) {
        return $this->renderItemShow('information.items.item', $item);
    }

    public function viewAffix(Request $request, ItemAffix $affix) {
        return view('information.affixes.affix', [
            'itemAffix' => $affix
        ]);
    }

    public function viewNpc(Npc $npc) {
        return view('information.npcs.npc', [
            'npc' => $npc
        ]);
    }

    public function viewQuest(Quest $quest) {
        $skill = null;

        if ($quest->unlocks_skill) {
            $skill = GameSkill::where('type', $quest->unlocks_skill_type)->where('is_locked', true)->first();
        }

        return view('information.quests.quest', [
            'quest'       => $quest,
            'lockedSkill' => $skill,
        ]);
    }

    public function viewPassiveSkill(PassiveSkill $passiveSkill) {
        return view('information.passive-skills.skill', [
            'skill' => $passiveSkill,
        ]);
    }

    protected function cleanFiles(array $files): array {
        $clean = [];

        foreach ($files as $index => $path) {
            if (explode('.', $path)[1] === 'DS_Store') {
                // @codeCoverageIgnoreStart
                unset($files[$index]);  // => We do not need this tested. Test environment would never have a mac specific file.
                // @codeCoverageIgnoreEnd
            } else {
                $clean[] = $path;
            }
        }

        return $clean;
    }
}
