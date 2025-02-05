<div>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="item-affix-base-damage-mod">Base Damage Mod: </label>
                <input type="number" steps="0.01" class="form-control" id="item-affix-base-damage-mod" name="item-affix-base-damage-mod" wire:model="itemAffix.base_damage_mod">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="item-affix-base-ac-mod">Base AC Mod: </label>
                <input type="number" steps="0.01" class="form-control" id="item-affix-base-ac-mod" name="item-affix-base-ac-mod" wire:model="itemAffix.base_ac_mod">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="item-affix-base-healing-mod">Base Healing Mod: </label>
                <input type="number" steps="0.01" class="form-control" id="item-affix-base-healing-mod" name="item-affix-base-healing-mod" wire:model="itemAffix.base_healing_mod">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="item-affix-str-mod">Str Mod: </label>
                <input type="number" steps="0.01" class="form-control" id="item-affix-str-mod" name="item-affix-str-mod" wire:model="itemAffix.str_mod">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="item-affix-dex-mod">Dex Mod: </label>
                <input type="number" steps="0.01" class="form-control" id="item-affix-dex-mod" name="item-affix-dex-mod" wire:model="itemAffix.dex_mod">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="item-affix-dur-mod">Dur Mod: </label>
                <input type="number" steps="0.01" class="form-control" id="item-affix-dur-mod" name="item-affix-dur-mod" wire:model="itemAffix.dur_mod">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="item-affix-int-mod">Int Mod: </label>
                <input type="number" steps="0.01" class="form-control" id="item-affix-int-mod" name="item-affix-int-mod" wire:model="itemAffix.int_mod">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="item-affix-chr-mod">Chr Mod: </label>
                <input type="number" steps="0.01" class="form-control" id="item-affix-chr-mod" name="item-affix-chr-mod" wire:model="itemAffix.chr_mod">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="item-affix-agi-mod">Agi Mod: </label>
                <input type="number" steps="0.01" class="form-control" id="item-affix-agi-mod" name="item-affix-agi-mod" wire:model="itemAffix.agi_mod">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="item-affix-chr-mod">Focus Mod: </label>
                <input type="number" steps="0.01" class="form-control" id="item-affix-chr-mod" name="item-affix-chr-mod" wire:model="itemAffix.focus_mod">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="item-affix-class-bonus-mod">Class Bonus Mod: </label>
                <input type="number" steps="0.01" class="form-control" id="item-affix-class-bonus-mod" name="item-affix-class-bonus-mod" wire:model="itemAffix.class_bonus">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group form-check-inline">
                <input type="checkbox" class="form-check-input" id="affix-reduces-enemy-stats" wire:model="itemAffix.reduces_enemy_stats">
                <label class="form-check-label" for="affix-reduces-enemy-stats">Can This Affix Reduce Enemy Stats?</label>
            </div>
        </div>
    </div>
    <div class="{{!is_array($itemAffix) ? !is_null($itemAffix) ? $itemAffix->reduces_enemy_stats ? '' : 'hide' : 'hide' : 'hide'}}">
        <hr />
        <div class="alert alert-info mt-2 mb-3">
            <p>
                The logic states that prefixes can reduce all stats and do not stack, while suffixes can reduce individual stats and do stack.
            </p>
            <p>
                These are all percentage based.
            </p>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="item-affix-str-reduction">Str Reduction: </label>
                    <input type="number" steps="0.01" class="form-control" id="item-affix-str-reduction" name="item-affix-str-reduction" wire:model="itemAffix.str_reduction">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="item-affix-dex-reduction">Dex Reduction: </label>
                    <input type="number" steps="0.01" class="form-control" id="item-affix-dex-reduction" name="item-affix-dex-reduction" wire:model="itemAffix.dex_reduction">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="item-affix-dur-reduction">Dur Reduction: </label>
                    <input type="number" steps="0.01" class="form-control" id="item-affix-dur-reduction" name="item-affix-dur-reduction" wire:model="itemAffix.dur_reduction">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="item-affix-int-reduction">Int Reduction: </label>
                    <input type="number" steps="0.01" class="form-control" id="item-affix-int-reduction" name="item-affix-int-reduction" wire:model="itemAffix.int_reduction">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="item-affix-chr-reduction">Chr Reduction: </label>
                    <input type="number" steps="0.01" class="form-control" id="item-affix-chr-reduction" name="item-affix-chr-reduction" wire:model="itemAffix.chr_reduction">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="item-affix-agi-reduction">Agi Reduction: </label>
                    <input type="number" steps="0.01" class="form-control" id="item-affix-agi-reduction" name="item-affix-agi-reduction" wire:model="itemAffix.agi_reduction">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="item-affix-chr-reduction">Focus Reduction: </label>
                    <input type="number" steps="0.01" class="form-control" id="item-affix-chr-reduction" name="item-affix-chr-reduction" wire:model="itemAffix.focus_reduction">
                </div>
            </div>
        </div>
        <hr />
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="item-affix-resistance-reduction">% Resistance Reduction: </label>
                <input type="number" steps="0.01" class="form-control" id="item-affix-resistance-reduction" name="item-affix-resistance-reduction" wire:model="itemAffix.resistance_reduction">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="item-affix-steal-life-amount">Steals % of enemy life: </label>
                <input type="number" steps="0.01" class="form-control" id="item-affix-steal-life-amount" name="item-affix-steal-life-amount" wire:model="itemAffix.steal_life_amount">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="item-affix-entrance-chance">Entrance Chance: </label>
                <input type="number" steps="0.01" class="form-control" id="item-affix-entrance-chance" name="item-affix-entrance-chance" wire:model="itemAffix.entranced_chance">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for="item-affix-skill-name">Affects Skill: </label>
                <select class="form-control required" name="item-affix-skill-name" wire:model="itemAffix.skill_name" >
                    <option value="">Please select</option>
                    @foreach($skills as $skill)
                        <option value="{{$skill->name}}">{{$skill->name}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="item-affix-skill-training-bonus">Skill Training Bonus: </label>
                <input type="number" steps="0.01" class="form-control required" id="item-affix-skill-training-bonus" name="item-affix-skill-training-bonus" wire:model="itemAffix.skill_training_bonus" >
                <span class="text-muted">Applies an xp bonus to the skill when training.</span><br />
                @error('skill_training_bonus') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="item-affix-skill-bonus">Skill Bonus: </label>
                <input type="number" steps="0.01" class="form-control required" id="item-affix-skill-bonus" name="item-affix-skill-bonus" wire:model="itemAffix.skill_bonus" >
                <span class="text-muted">Applies a character roll percentage when using said skill.</span><br />
                @error('skill_bonus') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="item-affix-skill-reduction">Skill Reduction: </label>
                <input type="number" steps="0.01" class="form-control required" id="item-affix-skill-reduction" name="item-affix-skill-reduction" wire:model="itemAffix.skill_reduction">
                <span class="text-muted">Reduces all enemy skills by %.</span><br />
                @error('skill_bonus') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="base_damage_mod_bonus">Increases Skill Base Damage By: </label>
                <input type="number" steps="0.01" class="form-control" id="base_damage_mod_bonus" name="base_damage_mod_bonus" wire:model="itemAffix.base_damage_mod_bonus">
                @error('itemAffix.base_damage_mod_bonus') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="base_healing_mod_bonus">Increases Skill Base Healing By: </label>
                <input type="number" steps="0.01" class="form-control" id="base_healing_mod_bonus" name="base_healing_mod_bonus" wire:model="itemAffix.base_healing_mod_bonus">
                @error('itemAffix.base_healing_mod_bonus') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="base_ac_mod_bonus">Increase Skill Base AC By: </label>
                <input type="number" steps="0.01" class="form-control" id="base_ac_mod_bonus" name="base_ac_mod_bonus" wire:model="itemAffix.base_ac_mod_bonus">
                @error('itemAffix.base_ac_mod_bonus') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="fight_time_out_mod_bonus">Increases Skill Fight Timeout By: </label>
                <input type="number" steps="0.01" class="form-control" id="fight_time_out_mod_bonus" name="fight_time_out_mod_bonus" wire:model="itemAffix.fight_time_out_mod_bonus">
                @error('itemAffix.fight_time_out_mod_bonus') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="move_time_out_mod_bonus">Increases Move Timeout By: </label>
                <input type="number" steps="0.01" class="form-control" id="move_time_out_mod_bonus" name="move_time_out_mod_bonus" wire:model="itemAffix.move_time_out_mod_bonus">
                @error('itemAffix.move_time_out_mod_bonus') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>
</div>
