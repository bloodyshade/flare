<!-- Top Bar -->
<header class="top-bar">

    @guest
    @else
        <!-- Menu Toggler -->
        <button type="button" class="menu-toggler la la-bars" data-toggle="menu"></button>
    @endguest


    <!-- Brand -->
    <span class="brand">Planes of Tlessa</span>

    <!-- Right -->
    @guest
        @include('layouts.partials.navigation.guest-navigation-section')
    @else
        @include('layouts.partials.navigation.user-navigation-section')
    @endguest
</header>

@guest
@else
    @include('layouts.partials.sidebar.playersidebar')
@endguest
