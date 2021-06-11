<!-- Menu Bar -->
<x-common.sidebar.base-menu>
    <x-common.sidebar.menu-link link="{{route('info.page', ['pageName' => 'home'])}}" title="Information" tippyName="Dashboard"/>
    <x-common.sidebar.menu-link link="{{route('info.page', ['pageName' => 'time-gates'])}}" title="Time Gates" icon="far fa-clock" tippyName="TimeGates"/>
    <x-common.sidebar.menu-link link="{{route('info.page', ['pageName' => 'rules'])}}" title="Core Rules" icon="fas fa-smoking-ban" tippyName="TimeGates"/>
    <x-common.sidebar.menu-link link="#no-link" title="Character Information" icon="ra ra-player" tippyName="CharacterInformation" pageName="character-info"/>

    <x-slot name="subMenu">
        <x-common.sidebar.sub-menu menu="character-info" title="Character Info">
            <x-common.sidebar.sub-menu-link link="{{route('info.page', ['pageName' => 'races-and-classes'])}}" title="Races and Classes" icon="ra ra-player"/>
            <x-common.sidebar.sub-menu-link link="{{route('info.page', ['pageName' => 'character-stats'])}}" title="Character Stats" icon="ra ra-muscle-up"/>
            <x-common.sidebar.sub-menu-link link="{{route('info.page', ['pageName' => 'skill-information'])}}" title="Skills" icon="ra ra-campfire"/>
            <x-common.sidebar.sub-menu-link link="{{route('info.page', ['pageName' => 'equipment'])}}" title="Equipment" icon="ra ra-archer"/>
        </x-common.sidebar.sub-menu>
    </x-slot>
</x-common.sidebar.base-menu>

{{--<ul id="sidebarnav">--}}
{{--    <li>--}}
{{--        <a href="{{route('info.page', [--}}
{{--            'pageName' => 'home'--}}
{{--          ])}}" aria-expanded="false">--}}
{{--          <i class="fas fa-question-circle"></i><span class="hide-menu ml-2">Information</span>--}}
{{--        </a>--}}
{{--    </li>--}}
{{--    <li class="nav-devider"></li>--}}
{{--    <li>--}}
{{--        <a href="{{route('info.page', [--}}
{{--            'pageName' => 'time-gates'--}}
{{--          ])}}"><i class="far fa-clock"></i> Time Gates</a>--}}
{{--    </li>--}}
{{--    <li class="nav-devider"></li>--}}
{{--    <li>--}}
{{--        <a href="{{route('info.page', [--}}
{{--            'pageName' => 'rules'--}}
{{--        ])}}"><i class="fas fa-smoking-ban"></i> Core Rules</a>--}}
{{--    </li>--}}
{{--    <li class="nav-devider"></li>--}}
{{--    <li>--}}
{{--      <a class="has-arrow" href="#" aria-expanded="false"><i class="ra ra-player"></i><span class="hide-menu">Character Information</span></a>--}}
{{--      <ul aria-expanded="false" class="collapse">--}}
{{--          <li><a href="{{route('info.page', ['pageName' => 'races-and-classes'])}}">Race and Class</a></li>--}}
{{--          <li><a href="{{route('info.page', ['pageName' => 'character-stats'])}}">Stats</a></li>--}}
{{--          <li><a href="{{route('info.page', ['pageName' => 'skill-information'])}}">Skills</a></li>--}}
{{--          <li><a href="{{route('info.page', ['pageName' => 'equipment'])}}">Equipment</a></li>--}}
{{--      </ul>--}}
{{--    </li>--}}
{{--    <li class="nav-devider"></li>--}}
{{--    <li>--}}
{{--      <a class="has-arrow" href="#" aria-expanded="false"><i class="ra ra-scroll-unfurled"></i><span class="hide-menu">Map</span></a>--}}
{{--      <ul aria-expanded="false" class="collapse">--}}
{{--          <li><a href="{{route('info.page', ['pageName' => 'movement'])}}">Movement</a></li>--}}
{{--          <li><a href="{{route('info.page', [--}}
{{--            'pageName' => 'locations'--}}
{{--          ])}}">Locations</a></li>--}}
{{--          <li><a href="{{route('info.page', [--}}
{{--            'pageName' => 'adventure'--}}
{{--          ])}}">Adventuring</a></li>--}}
{{--          <li><a href={{route('info.page', ['pageName' => 'set-sail'])}}>Setting Sail</a></li>--}}
{{--          <li><a href="{{route('info.page', ['pageName' => 'teleport'])}}">Teleporting</a></li>--}}
{{--          <li><a href="{{route('info.page', ['pageName' => 'traverse'])}}">Traverse</a></li>--}}
{{--          <li><a href="{{route('info.page', ['pageName' => 'planes'])}}">Planes</a></li>--}}
{{--      </ul>--}}
{{--    </li>--}}
{{--    <li class="nav-devider"></li>--}}
{{--    <li>--}}
{{--      <a class="has-arrow" href="#" aria-expanded="false"><i class="ra ra-guarded-tower"></i><span class="hide-menu">Kingdoms</span></a>--}}
{{--      <ul aria-expanded="false" class="collapse">--}}
{{--          <li><a href="{{route('info.page', ['pageName' => 'kingdoms'])}}">Kingdoms</a></li>--}}
{{--          <li><a href="{{route('info.page', ['pageName' => 'attacking-kingdoms'])}}">Attacking A Kingdom</a></li>--}}
{{--      </ul>--}}
{{--    </li>--}}
{{--    <li class="nav-devider"></li>--}}
{{--    <li>--}}
{{--        <a href="{{route('info.page', [--}}
{{--            'pageName' => 'settings'--}}
{{--          ])}}"><i class="fas fa-user-cog"></i> Player Settings</a>--}}
{{--    </li>--}}
{{--    <li class="nav-devider"></li>--}}
{{--    <li>--}}
{{--      <a href="{{route('info.page', [--}}
{{--          'pageName' => 'notifications'--}}
{{--        ])}}"><i class="fas fa-bell"></i> Notifications</a>--}}
{{--    </li>--}}
{{--    <li class="nav-devider"></li>--}}
{{--    <li>--}}
{{--        <a href="{{route('info.page', [--}}
{{--            'pageName' => 'market-board'--}}
{{--          ])}}"><i class="fas fa-sign"></i> Market Board</a>--}}
{{--    </li>--}}
{{--    <li class="nav-devider"></li>--}}
{{--    <li>--}}
{{--        <a class="has-arrow" href="#" aria-expanded="false"><i class="ra ra-anvil"></i><span class="hide-menu">Crafting/Enchanting</span></a>--}}
{{--        <ul aria-expanded="false" class="collapse">--}}
{{--            <li>--}}
{{--                <a href="{{route('info.page', ['pageName' => 'crafting'])}}">Crafting</a>--}}
{{--            </li>--}}
{{--            <li>--}}
{{--                <a href="{{route('info.page', ['pageName' => 'enchanting'])}}">Enchanting</a>--}}
{{--            </li>--}}
{{--        </ul>--}}
{{--    </li>--}}
{{--    <li>--}}
{{--        <a href="{{route('info.page', [--}}
{{--            'pageName' => 'monsters'--}}
{{--          ])}}"><i class="ra ra-eye-monster"></i> Monsters List</a>--}}
{{--    </li>--}}
{{--</ul>--}}
