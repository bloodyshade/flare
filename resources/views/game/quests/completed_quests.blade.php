@extends('layouts.app')

@section('content')
    <div class="justify-content-center">
        <x-core.page-title
            route="{{route('game')}}"
            link="Game"
            color="primary"
            title="Completed Quests"
        ></x-core.page-title>

        <div class="card">
            <div class="card-body">
                @livewire('character.completed-quests.data-table', [
                    'character' => $character,
                ])
            </div>
        </div>
    </div>
@endsection