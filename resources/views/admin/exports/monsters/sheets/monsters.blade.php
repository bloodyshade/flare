<table>
    <thead>
    <tr>
        <th>id</th>
        <th>name</th>
        <th>str</th>
        <th>dur</th>
        <th>dex</th>
        <th>chr</th>
        <th>int</th>
        <th>agi</th>
        <th>focus</th>
        <th>ac</th>
        <th>accuracy</th>
        <th>casting_accuracy</th>
        <th>dodge</th>
        <th>criticality</th>
        <th>is_celestial_entity</th>
        <th>gold_cost</th>
        <th>gold_dust_cost</th>
        <th>can_cast</th>
        <th>can_use_artifacts</th>
        <th>max_level</th>
        <th>damage_stat</th>
        <th>xp</th>
        <th>drop_check</th>
        <th>gold</th>
        <th>shards</th>
        <th>health_range</th>
        <th>attack_range</th>
        <th>max_spell_damage</th>
        <th>max_artifact_damage</th>
        <th>max_affix_damage</th>
        <th>healing_percentage</th>
        <th>spell_evasion</th>
        <th>artifact_annulment</th>
        <th>affix_resistance</th>
        <th>entrancing_chance</th>
        <th>devouring_light_chance</th>
        <th>devouring_darkness_chance</th>
        <th>published</th>
        <th>quest_item_id</th>
        <th>quest_item_drop_chance</th>
        <th>game_map_id</th>

    </tr>
    </thead>
    <tbody>
    @foreach($monsters as $monster)
        <tr>
            <td>{{$monster->id}}</td>
            <td>{{$monster->name}}</td>
            <td>{{$monster->str}}</td>
            <td>{{$monster->dur}}</td>
            <td>{{$monster->dex}}</td>
            <td>{{$monster->chr}}</td>
            <td>{{$monster->int}}</td>
            <td>{{$monster->agi}}</td>
            <td>{{$monster->focus}}</td>
            <td>{{$monster->ac}}</td>
            <td>{{$monster->accuracy}}</td>
            <td>{{$monster->casting_accuracy}}</td>
            <td>{{$monster->dodge}}</td>
            <td>{{$monster->criticality}}</td>
            <th>{{$monster->is_celestial_entity}}</th>
            <th>{{$monster->gold_cost}}</th>
            <th>{{$monster->gold_dust_cost}}</th>
            <th>{{$monster->can_cast}}</th>
            <th>{{$monster->can_use_artifacts}}</th>
            <td>{{$monster->max_level}}</td>
            <td>{{$monster->damage_stat}}</td>
            <td>{{$monster->xp}}</td>
            <td>{{$monster->drop_check}}</td>
            <td>{{$monster->gold}}</td>
            <td>{{$monster->shards}}</td>
            <td>{{$monster->health_range}}</td>
            <td>{{$monster->attack_range}}</td>
            <td>{{$monster->max_spell_damage}}</td>
            <td>{{$monster->max_artifact_damage}}</td>
            <td>{{$monster->max_affix_damage}}</td>
            <td>{{$monster->healing_percentage}}</td>
            <td>{{$monster->spell_evasion}}</td>
            <td>{{$monster->artifact_annulment}}</td>
            <td>{{$monster->affix_resistance}}</td>
            <td>{{$monster->entrancing_chance}}</td>
            <td>{{$monster->devouring_light_chance}}</td>
            <td>{{$monster->devouring_darkness_chance}}</td>
            <td>{{$monster->published}}</td>
            <td>{{(!is_null($monster->questItem)) ? $monster->questItem->id : null}}</td>
            <td>{{$monster->quest_item_drop_chance}}</td>
            <td>{{$monster->game_map_id}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
