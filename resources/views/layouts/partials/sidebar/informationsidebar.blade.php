<!-- Menu Bar -->
<x-common.sidebar.base-menu>
    <x-common.sidebar.menu-link link="{{route('info.page', ['pageName' => 'home'])}}" title="Information" tippyName="Dashboard"/>
    <x-common.sidebar.menu-link link="{{route('info.page', ['pageName' => 'time-gates'])}}" title="Time Gates" icon="far fa-clock" tippyName="TimeGates"/>
    <x-common.sidebar.menu-link link="{{route('info.page', ['pageName' => 'rules'])}}" title="Core Rules" icon="fas fa-smoking-ban" tippyName="CoreRules"/>
    <x-common.sidebar.menu-link link="#no-link" title="Character Information" icon="ra ra-player" tippyName="CharacterInformation" pageName="character-info"/>
    <x-common.sidebar.menu-link link="#no-link" title="Map" icon="ra ra-scroll-unfurled" tippyName="MapInformation" pageName="map-info"/>
    <x-common.sidebar.menu-link link="#no-link" title="Kingdoms" icon="ra ra-guarded-tower" tippyName="KingdomInformation" pageName="kingdom-info"/>
    <x-common.sidebar.menu-link link="#no-link" title="List" icon="fas fa-list" tippyName="Lists" pageName="lists"/>

    <x-slot name="subMenu">
        <x-common.sidebar.sub-menu menu="character-info" title="Character Info">
            <x-common.sidebar.sub-menu-link link="{{route('info.page', ['pageName' => 'races-and-classes'])}}" title="Races and Classes"/>
            <x-common.sidebar.sub-menu-link link="{{route('info.page', ['pageName' => 'character-stats'])}}" title="Character Stats"/>
            <x-common.sidebar.sub-menu-link link="{{route('info.page', ['pageName' => 'skill-information'])}}" title="Skills"/>
            <x-common.sidebar.sub-menu-link link="{{route('info.page', ['pageName' => 'equipment'])}}" title="Equipment"/>
            <hr />
            <h6>User Related</h6>
            <x-common.sidebar.sub-menu-link link="{{route('info.page', ['pageName' => 'settings'])}}" title="Player Settings"/>
            <x-common.sidebar.sub-menu-link link="{{route('info.page', ['pageName' => 'notifications'])}}" title="Notifications"/>
            <hr />
            <h6>Trading</h6>
            <x-common.sidebar.sub-menu-link link="{{route('info.page', ['pageName' => 'market-board'])}}" title="Market Board"/>
            <hr />
            <h6>Crafting/Enchanting</h6>
            <x-common.sidebar.sub-menu-link link="{{route('info.page', ['pageName' => 'crafting'])}}" title="Crafting"/>
            <x-common.sidebar.sub-menu-link link="{{route('info.page', ['pageName' => 'enchanting'])}}" title="Enchanting"/>
        </x-common.sidebar.sub-menu>

        <x-common.sidebar.sub-menu menu="map-info" title="Map Info">
            <x-common.sidebar.sub-menu-link link="{{route('info.page', ['pageName' => 'movement'])}}" title="Movement"/>
            <x-common.sidebar.sub-menu-link link="{{route('info.page', ['pageName' => 'locations'])}}" title="Locations"/>
            <x-common.sidebar.sub-menu-link link="{{route('info.page', ['pageName' => 'adventure'])}}" title="Adventures"/>
            <x-common.sidebar.sub-menu-link link="{{route('info.page', ['pageName' => 'set-sail'])}}" title="Setting Sail"/>
            <x-common.sidebar.sub-menu-link link="{{route('info.page', ['pageName' => 'teleport'])}}" title="Teleport"/>
            <x-common.sidebar.sub-menu-link link="{{route('info.page', ['pageName' => 'traverse'])}}" title="Traverse"/>
            <x-common.sidebar.sub-menu-link link="{{route('info.page', ['pageName' => 'planes'])}}" title="Planes"/>
        </x-common.sidebar.sub-menu>

        <x-common.sidebar.sub-menu menu="kingdom-info" title="Kingdom Info">
            <x-common.sidebar.sub-menu-link link="{{route('info.page', ['pageName' => 'kingdoms'])}}" title="Kingdoms"/>
            <x-common.sidebar.sub-menu-link link="{{route('info.page', ['pageName' => 'attacking-kingdoms'])}}" title="Attacking Kingdoms"/>
        </x-common.sidebar.sub-menu>

        <x-common.sidebar.sub-menu menu="lists" title="Lists">
            <x-common.sidebar.sub-menu-link link="{{route('info.page', ['pageName' => 'monsters'])}}" title="Monsters"/>
        </x-common.sidebar.sub-menu>
    </x-slot>
</x-common.sidebar.base-menu>
