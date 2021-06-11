@props(['menu', 'title'])

<div class="menu-detail" data-menu="{{$menu}}">
    <div class="menu-detail-wrapper">
        <h6 class="uppercase">{{$title}}</h6>
        {{$slot}}
    </div>
</div>
