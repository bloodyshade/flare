@props([
    'title'             => 'Example',
    'route'             => null,
    'additionalClasses' => '',
])

@if (!is_null($route))
    <h4 class="px-2"><a href={{$route}} {{$attributes}}>{{$title}}</a></h4>
@else
    <h4 class="px-2">{{$title}}</h4>
@endif

<div class="card {{$additionalClasses}}">
    <div class="card-body">
        {{$slot}}
    </div>
</div>
