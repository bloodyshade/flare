@extends('layouts.app')

@section('content')
    <div class="mt-5 container justify-center w-full md:w-3/4">
        @foreach($sections as $section)
            <div class="prose-lg md:prose-xl max-w-full">
                @markdown($section['content'])
            </div>

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
    </div>
@endsection
