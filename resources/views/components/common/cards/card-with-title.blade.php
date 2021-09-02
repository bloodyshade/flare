@props([
    'title'             => 'Example',
    'route'             => null,
    'additionalClasses' => '',
])

@if (!is_null($route))
    <h4 class="py-2"><a href={{$route}} {{$attributes}}>{{$title}}</a></h4>
@else
    <h4 class="py-2">{{$title}}</h4>
@endif

<div class="card px-8 py-8 {{$additionalClasses}}">
    {{$slot}}
</div>
