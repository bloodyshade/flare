<table>
    <thead>
    <tr>
        <th>name</th>
        <th>description</th>
        <th>attack</th>
        <th>defence</th>
        <th>can_not_be_healed</th>
        <th>is_settler</th>
        <th>reduces_morale_by</th>
        <th>can_heal</th>
        <th>heal_percentage</th>
        <th>siege_weapon</th>
        <th>attacks_walls</th>
        <th>attacks_buildings</th>
        <th>defender</th>
        <th>attacker</th>
        <th>primary_target</th>
        <th>fall_back</th>
        <th>travel_time</th>
        <th>wood_cost</th>
        <th>clay_cost</th>
        <th>stone_cost</th>
        <th>iron_cost</th>
        <th>required_population</th>
        <th>time_to_recruit</th>
    </tr>
    </thead>
    <tbody>
    @foreach($units as $unit)
        <tr>
            <td>{{$unit->name}}</td>
            <td>{{$unit->description}}</td>
            <td>{{$unit->attack}}</td>
            <td>{{$unit->defence}}</td>
            <td>{{$unit->can_not_be_healed}}</td>
            <td>{{$unit->is_settler}}</td>
            <td>{{$unit->reduces_morale_by}}</td>
            <td>{{$unit->can_heal}}</td>
            <td>{{$unit->heal_percentage}}</td>
            <td>{{$unit->siege_weapon}}</td>
            <td>{{$unit->attacks_walls}}</td>
            <td>{{$unit->attacks_buildings}}</td>
            <td>{{$unit->defender}}</td>
            <td>{{$unit->attacker}}</td>
            <td>{{$unit->primary_target}}</td>
            <td>{{$unit->fall_back}}</td>
            <td>{{$unit->travel_time}}</td>
            <td>{{$unit->wood_cost}}</td>
            <td>{{$unit->clay_cost}}</td>
            <td>{{$unit->stone_cost}}</td>
            <td>{{$unit->iron_cost}}</td>
            <td>{{$unit->required_population}}</td>
            <td>{{$unit->time_to_recruit}}</td>
        </tr>
    @endforeach
    </tbody>
</table>