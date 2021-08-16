<table>
    <thead>
    <tr>
        <th>item_suffix_id</th>
        <th>item_prefix_id</th>
        <th>market_sellable</th>
        <th>name</th>
        <th>type</th>
        <th>description</th>
        <th>default_position</th>
        <th>base_damage</th>
        <th>base_healing</th>
        <th>base_ac</th>
        <th>cost</th>
        <th>gold_dust_cost</th>
        <th>shards_cost</th>
        <th>base_damage_mod</th>
        <th>base_healing_mod</th>
        <th>base_ac_mod</th>
        <th>str_mod</th>
        <th>dur_mod</th>
        <th>dex_mod</th>
        <th>chr_mod</th>
        <th>int_mod</th>
        <th>agi_mod</th>
        <th>focus_mod</th>
        <th>effect</th>
        <th>can_craft</th>
        <th>can_drop</th>
        <th>skill_level_required</th>
        <th>skill_level_trivial</th>
        <th>crafting_type</th>
        <th>skill_name</th>
        <th>skill_bonus</th>
        <th>base_damage_mod_bonus</th>
        <th>base_healing_mod_bonus</th>
        <th>base_ac_mod_bonus</th>
        <th>fight_time_out_mod_bonus</th>
        <th>move_time_out_mod_bonus</th>
        <th>skill_training_bonus</th>
        <th>craft_only</th>
        <th>usable</th>
        <th>damages_kingdoms</th>
        <th>kingdom_damage</th>
        <th>lasts_for</th>
        <th>stat_increase</th>
        <th>increase_stat_by</th>
        <th>affects_skill_type</th>
        <th>increase_skill_bonus_by</th>
        <th>increase_skill_training_bonus_by</th>
        <th>can_resurrect</th>
        <th>resurrection_chance</th>
        <th>spell_evasion</th>
        <th>artifact_annulment</th>
    </tr>
    </thead>
    <tbody>
    @foreach($items as $item)
        <tr>
            <td>{{is_null($item->itemSuffix) ? null :$item->itemSuffix->name}}</td>
            <td>{{is_null($item->itemPrefix) ? null :$item->itemPrefix->name}}</td>
            <td>{{$item->market_sellable}}</td>
            <td>{{$item->name}}</td>
            <td>{{$item->type}}</td>
            <td>{{$item->description}}</td>
            <td>{{$item->default_position}}</td>
            <td>{{$item->base_damage}}</td>
            <td>{{$item->base_healing}}</td>
            <td>{{$item->base_ac}}</td>
            <td>{{$item->cost}}</td>
            <td>{{$item->gold_dust_cost}}</td>
            <td>{{$item->shards_cost}}</td>
            <td>{{$item->base_damage_mod}}</td>
            <td>{{$item->base_healing_mod}}</td>
            <td>{{$item->base_ac_mod}}</td>
            <td>{{$item->str_mod}}</td>
            <td>{{$item->dur_mod}}</td>
            <td>{{$item->dex_mod}}</td>
            <td>{{$item->chr_mod}}</td>
            <td>{{$item->int_mod}}</td>
            <td>{{$item->agi_mod}}</td>
            <td>{{$item->focus_mod}}</td>
            <td>{{$item->effect}}</td>
            <td>{{$item->can_craft}}</td>
            <td>{{$item->can_drop}}</td>
            <td>{{$item->skill_level_required}}</td>
            <td>{{$item->skill_level_trivial}}</td>
            <td>{{$item->crafting_type}}</td>
            <td>{{$item->skill_name}}</td>
            <td>{{$item->skill_bonus}}</td>
            <td>{{$item->base_damage_mod_bonus}}</td>
            <td>{{$item->base_healing_mod_bonus}}</td>
            <td>{{$item->base_ac_mod_bonus}}</td>
            <td>{{$item->fight_time_out_mod_bonus}}</td>
            <td>{{$item->move_time_out_mod_bonus}}</td>
            <td>{{$item->skill_training_bonus}}</td>
            <td>{{$item->craft_only}}</td>
            <td>{{$item->usable}}</td>
            <td>{{$item->damages_kingdoms}}</td>
            <td>{{$item->kingdom_damage}}</td>
            <td>{{$item->lasts_for}}</td>
            <td>{{$item->stat_increase}}</td>
            <td>{{$item->increase_stat_by}}</td>
            <td>{{$item->affects_skill_type}}</td>
            <td>{{$item->increase_skill_bonus_by}}</td>
            <td>{{$item->increase_skill_training_bonus_by}}</td>
            <td>{{$item->can_resurrect}}</td>
            <td>{{$item->resurrection_chance}}</td>
            <td>{{$item->spell_evasion}}</td>
            <td>{{$item->asrtifact_annulment}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
