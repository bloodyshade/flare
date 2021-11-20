@props([
    'title' => '',
    'route' => '#',
    'color' => 'primary',
    'link'  => ''
])

<x-core.grids.two-column>
    <x-slot name="columnOne">
        <h4 class="mt-2">{!! $title !!}</h4>
    </x-slot>
    <x-slot name="columnTwo">
        <a href="{{$route}}" class="btn btn-{{$color}} float-right ml-2">{{$link}}</a>
        {{$slot}}
    </x-slot>
</x-core.grids.two-column>
