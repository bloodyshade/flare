@props([
    'link'         => '#',
    'icon'         => 'fas fa-question-circle',
    'title'        => 'Sample',
    'tippyName'    => 'Sample',
    'pageName'     => '',
])

<a href="{{$link}}" class="link" data-target="{{$pageName !== '' ?  '[data-menu='.$pageName.']' : '' }}" data-toggle="tooltip-menu" data-tippy-content="{{$tippyName}}">
    <span class="icon {{$icon}}"></span>
    <span class="title">{{$title}}</span>
</a>
