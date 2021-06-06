@props([
  'title',
  'stackIcons' => false,
  'topIcon'    => '',
  'bottomIcon' => '',
])

<div class="w-full text-center mt-16 flare-spacer-2">
    <h3 class="text-4xl text-grey-800 dark:text-light-200">
        @if ($stackIcons)
            <span class="fa-stack">
                <i class="far {{$bottomIcon}} fa-stack-1x"></i>
                <i class="fas {{$topIcon}} fa-stack-2x text-red-600"></i>
            </span>
        @endif
        {{$title}}
    </h3>
    <p class="mt-3 text-lg text-gray-700 dark:text-light-200 {{$stackIcons ? 'mt-12' : ''}}">
        {{$slot}}
    </p>
</div>
