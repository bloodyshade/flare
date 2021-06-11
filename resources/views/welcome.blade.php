@extends('layouts.app')

@section('content')
    <div class="container flex flex-wrap items-center justify-center mt-2 py-16">
        <div class="w-full text-center">
            <h1 class="text-7xl text-gray-800 dark:text-light-200">Planes of Tlessa</h1>
            <p class="mt-3 text-2xl text-gray-700 dark:text-light-200">A world of possibilities!</p>
            <div class="mt-8 space-x-2">
                <x-common.buttons.link title="Join!"/>
                <x-common.buttons.link title="Releases" color="yellow"/>
                <x-common.buttons.link title="Help Section" color="green" link="{{route('info.page', [
                    'pageName' => 'home'
                ])}}"/>
            </div>
        </div>
        <div class="w-full text-center mt-16 flare-spacer-1">
            <img class="rounded" src="{{asset('promotion/game.png')}}" />
        </div>
        <x-welcome-page.text-section title="Stay Logged In">
            There is no set it and forget it. This game requires you be engaged. <br />
            Timers and such only last minutes at best, with attack and movement timers being set to seconds.
        </x-welcome-page.text-section>
        <div class="w-full text-center mt-16 flare-spacer-2">
            <div class="container flex flex-wrap">
                <div class="w-full md:w-1/3">
                    <x-welcome-page.info-box title="Equip Your Character" route="{{route('info.page', [
                        'pageName' => 'equipment'
                    ])}}">
                        Buy/sell weapons, armor, rings, artifacts and more and out fit your
                        character for the road ahead. Who knows what beasties you might find
                    </x-welcome-page.info-box>
                </div>
                <div class="w-full md:w-1/3">
                    <x-welcome-page.info-box title="Rule Kingdoms!" route="{{route('info.page', [
                        'pageName' => 'kingdoms'
                    ])}}" icon="ra-player-king">
                        In a game where there are no resets, can your kingdom survive?
                        Or will it be taken by those more powerful?
                    </x-welcome-page.info-box>
                </div>
                <div class="w-full md:w-1/3">
                    <x-welcome-page.info-box title="Go on adventures!" route="{{route('info.page', [
                        'pageName' => 'adventure'
                    ])}}" icon="ra-trail">
                        Travel to new locations and find out their mysteries by partaking in location based adventures!
                    </x-welcome-page.info-box>
                </div>
            </div>
        </div>
        <div class="w-full text-center mt-16 flare-spacer-2 text-grey-800 dark:text-light-200">
            <div class="container flex flex-wrap">
                <div class="w-full md:w-1/2 text-center mb-16 md:relative md:bottom-1/2 md:top-40 px-8">
                    <h2 class="text-5xl">See Where You Are Going</h2>
                    <p class="text-lg mb-16 mt-8">Adventure on a map by clicking the action buttons. Certain locations will have adventures and some you can set sail from, such as ports!</p>
                    <a class="
                        border-2 border-solid border-blue-600 dark:border-blue-900 dark:bg-blue-800 dark:text-light-200 font-bold text-lg
                        bg-blue-500 rounded-sm text-white px-4 py-2 hover:bg-blue-700 hover:border-bg-blue-800
                        dark:hover:bg-blue-600 dark:hover:border-bg-blue-400 hover:text-white dark:hover:text-light-200
                    " href="{{route('info.page', [
                        'pageName' => 'map',
                    ])}}">Learn More!</a>
                </div>
                <div class="w-full md:w-1/2">
                    <img src="{{asset('promotion/map.png')}}" />
                </div>
            </div>
        </div>
        <x-welcome-page.text-section title="Put that credit card away!" stackIcons="{{true}}" topIcon="fa-ban" bottomIcon="fa-credit-card">
            This game is free. This game has one philosophy: You want it? Earn it! <br />Every thing from the best gear,
            to the strongest kingdoms to ability to <br />travel from one plane to the next is all only attainable by playing the game
        </x-welcome-page.text-section>
        <div class="w-full text-center mt-16 flare-spacer-2">
            <div class="container flex flex-wrap">
                <div class="w-full md:w-1/3">
                    <x-welcome-page.info-box title="Crafting is simple" route="{{route('info.page', [
                        'pageName' => 'crafting'
                    ])}}" icon="ra-anvil">
                        No need to gather. You can just start crafting! Find tomes to get xp bonuses!
                    </x-welcome-page.info-box>
                </div>
                <div class="w-full md:w-1/3">
                    <x-welcome-page.info-box title="Enchant Gear!" route="{{route('info.page', [
                        'pageName' => 'enchanting'
                    ])}}" icon="ra-forging">
                        All you need is to destroy an item with an affix on it for the recipe! How easy is that!
                    </x-welcome-page.info-box>
                </div>
                <div class="w-full md:w-1/3">
                    <x-welcome-page.info-box title="Go on adventures!" route="{{route('info.page', [
                        'pageName' => 'Market Board'
                    ])}}" icon="ra-wooden-sign">
                        Buy and sell from the market board. Craft and Enchant items for others and make a profit!
                    </x-welcome-page.info-box>
                </div>
            </div>
        </div>
        <div class="container w-full md:w-3/4 justify-center flare-spacer-2">
            <h2 class="text-center text-gray-700 dark:text-light-200 text-5xl mb-16">
                <i class="far fa-question-circle"></i>
                FAQ
            </h2>

            <dl class="mt-6 text-gray-700 dark:text-light-200 text-lg">
                <dt>Are There Cash Shops?</dt>
                <dd>
                    No, and there never will be. You cannot buy anything in this game, no weapons, gear, armor,
                    advantages, nothing. You want it, you will earn it.
                </dd>
                <dt>Are there Adds?</dt>
                <dd>
                    No. There are no adds what so ever.
                </dd>
                <dt>Is it persistent?</dt>
                <dd>
                    Yes. You can log out if you are in the middle of an adventure or are launching an attack
                    on another kingdom. Assuming you have the right settings enabled, you will be
                    emailed when the action is finished.
                </dd>
                <dt>Is it idle?</dt>
                <dd>
                    No and yes. The majority of the game is not idle based, but aspects such as managing
                    your kingdom, or going on adventures are considered idle. Adventures can range from 10-60 minutes
                    in length and disable you from doing pretty much anything. You can log out and be emailed, when it's done.
                    Kingdoms are also idle based in the fact that it takes time to recruit, build and attack.
                </dd>
                <dt>Does it use energy systems?</dt>
                <dd>
                    No. Tlessa uses what's called: <a class="text-blue-500 dark:text-blue-300 dark:hover:text-blue-400" href="/information/time-gates">Time Gates</a>. These apply to actions you do and time you out
                    from doing that action again for a matter of seconds or minutes. However, the goal of Tlessa is
                    not to keep you engaged, so for example you could: Fight, Craft, Move and then wait for their respective timers
                    to end before doing the same thing. In the aforementioned example: Killing a monster gates you a 10 second time
                    out before being able to kill the monster again, but being killed by said monster, gives you a 20 second time out before being able
                    to revive.
                </dd>
                <dt>Are they're factions/guilds/clans?</dt>
                <dd>
                    No. In Tlessa, it's every person for them selves. There is no guild or clan system in Tlessa.
                </dd>
            </dl>
        </div>
    </div>
@endsection
