@props([
    'link' => '#',
    'title' => 'Link',
    'color' => 'blue',
])

<a href="{{$link}}" class="
    border-2 border-{{$color}}-400 py-2 px-4 font-bold text-lg text-white bg-{{$color}}-500
    hover:border-{{$color}}-500 hover:bg-{{$color}}-600 hover:shadow-lg
    hover:text-white text-center rounded-md dark:border-{{$color}}-500 dark::bg-{{$color}}-600 dark:text-light-200
    dark:hover:border-{{$color}}-400 dark:hover:bg-{{$color}}-500"
>
    {{$title}}
</a>
