@extends('layouts.app')

@section('content')
    <x-common.small-container>
        @foreach($sections as $section)
            <x-common.cards.card>
                <div class="prose-lg md:prose-xl max-w-full">
                    @markdown($section['content'])
                </div>
            </x-common.cards.card>

            @if (!is_null($section['view']))
                @if ($section['livewire'])
                    @if ($section['before'])
                        <div class="mb-2 mt-2">
                            @include($section['before'])
                        </div>
                    @endif

                <div class="mb-3 mt-3">
                    @livewire($section['view'], [
                        'only'   => $section['only']
                    ])
                </div>
                @else
                    false
                @endif
            @endif

        @endforeach
    </x-common.small-container>
@endsection
