@extends('layouts.app')

@section('content')
  <div class="tw-w-full lg:tw-w-3/4 tw-m-auto">
    <x-core.page-title
        title="Buying"
        route="{{route('game')}}"
        link="Game"
        color="primary"
    ></x-core.page-title>

    <div class="tw-m-auto">
        <div id="shop">
            <x-core.cards.card>
                @if ($isLocation)
                    <p>
                        You enter the old and musty shop. Along the walls you an see various weapons, armor
                        and other types of items that might benefit you on your journeys.
                    </p>
                    <p>
                        Counting your gold, you walk in with confidence, knowing you will walk out with
                        better gear. Knowing... That your enemies stand no chance.
                    </p>
                    <p>
                        As you enter, you see an old man behind a worn out counter. He smiles warmly at you. Welcoming you:
                    </p>

                    <p><strong>Shop Keeper</strong>: <em>Hello! Welcome! what can I get you?</em></p>
                @else
                    <p>On your journey you come across a merchant on the road. He is carrying his bag full of trinkets and goodies.</p>
                    <p>As you approach, he takes off his backpack and warmly greets you:</p>
                    <p><strong>Shop Keeper</strong>: <em>These roads are dangerous, my friend! What can I get you?</em></p>
                @endif
            </x-core.cards.card>

            <x-core.cards.card css="tw-mt-5 tw-mb-5">
              <p><strong>Your Gold</strong>: <span class="color-gold">{{number_format($gold)}}</span></p>
            </x-core.cards.card>

            @livewire('admin.items.data-table', [
                'character'             => $character,
                'craftOnly'             => false,
                'showSkillInfo'         => false,
                'showOtherCurrencyCost' => false
            ])
        </div>
    </div>
  </div>
@endsection
