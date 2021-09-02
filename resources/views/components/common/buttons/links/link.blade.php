@props([
    'link' => '#',
    'title' => 'Link',
    'color' => 'blue'
])

@switch ($color)
    @case ('blue')
        @php
          $color = 'border-blue-700 bg-blue-600 hover:border-blue-800
            hover:bg-blue-700 dark:border-blue-800 dark::bg-blue-800
            dark:hover:border-blue-700 dark:hover:bg-blue-700';
        @endphp
        @break
    @case ('yellow')
        @php
          $color = 'border-yellow-700 bg-yellow-600 hover:border-yellow-800
            hover:bg-yellow-700 dark:border-yellow-800 dark:bg-yellow-800
            dark:hover:border-yellow-700 dark:hover:bg-yellow-700';
        @endphp
        @break
    @case ('green')
        @php
          $color = 'border-green-700 bg-green-600 hover:border-green-800
            hover:bg-green-700 dark:border-green-800 dark:bg-green-800
            dark:hover:border-green-700 dark:hover:bg-green-700';
        @endphp
        @break
    @default
        @php
          $color = 'border-blue-700 bg-blue-600 hover:border-blue-800
            hover:bg-blue-700 dark:border-blue-800 dark:bg-blue-800
            dark:hover:border-blue-700 dark:hover:bg-blue-700';
        @endphp
@endswitch

<a href="{{$link}}" class="
    border-2 py-2 px-4 font-bold text-lg text-white hover:shadow-lg
    hover:text-white text-center rounded-sm {{$color}}"
>
    {{$title}}
</a>
