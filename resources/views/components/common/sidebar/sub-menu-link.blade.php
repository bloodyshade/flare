@props([
    'link'  => '#',
    'title' => 'Sample',
    'icon'  => ''
])


<a href="{{$link}}">
    <span class="{{$icon}}"></span>
    {{$title}}
</a>
