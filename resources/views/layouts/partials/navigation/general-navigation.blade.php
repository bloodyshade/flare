<!-- Top Bar -->
<header class="top-bar">

    @guest
        @if($helpSideBar)
            <button type="button" class="menu-toggler la la-bars" data-toggle="menu"></button>
        @endif
    @else
        <!-- Menu Toggler -->
        <button type="button" class="menu-toggler la la-bars" data-toggle="menu"></button>
    @endguest


    <!-- Brand -->
        <span class="brand"><a href="/">Planes of Tlessa</a></span>

    <!-- Right -->
    @guest
        @include('layouts.partials.navigation.guest-navigation-section')
    @else
        @include('layouts.partials.navigation.user-navigation-section')
    @endguest
</header>

@guest
    @if($helpSideBar)
        @include('layouts.partials.sidebar.informationsidebar')
    @endif
@else
    @if($helpSideBar)
        @include('layouts.partials.sidebar.informationsidebar')
    @else
        @include('layouts.partials.sidebar.playersidebar')
    @endif
@endguest
