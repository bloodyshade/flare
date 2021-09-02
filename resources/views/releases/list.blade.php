@extends('layouts.app')

@section('content')
    <x-common.small-container>
        <h1>Releases</h1>
        <hr />

        @forelse($releases as $release)
            <x-common.cards.card-with-title title="Version: {{$release->version}}">
                <h3>{{$release->name}}</h3>
                <div class="prose-lg md:prose-xl max-w-full">
                    @markdown($release->body)
                </div>
                <hr />
                <a href="{{$release->url}}" class="float-right btn btn-primary btn-sm">Read More <i class="fas fa-external-link-alt"></i></a>
            </x-common.cards.card-with-title>
        @empty
            <x-common.cards.card-with-title title="No Releases">
                <div class="prose-lg md:prose-xl max-w-full">
                    There hasn't been any releases yet. Please check back later.
                </div>
            </x-common.cards.card-with-title>
        @endforelse

        {{ $releases->links() }}
    </x-common.small-container>
@endsection
