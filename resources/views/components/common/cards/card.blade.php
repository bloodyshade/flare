@props([
    'additionalClasses' => '',
])

<div class="card px-8 py-8 {{$additionalClasses}}">
    {{$slot}}
</div>
