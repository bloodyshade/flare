@props([
    'route',
    'title',
    'icon' => 'ra-player'
])

<a href="{{$route}}">
    <div class="card mt-5 p-10 mr-4 hover:shadow-lg hover:bg-light-200 hover:dark:bg-grey-900 hover:bg-opacity-60 cursor-pointer">
        <div class="text-2xl">
            <div class="border-solid border-2 border-opacity-35 text-blue-50 border-gray-500 rounded-full h-24 w-24 m-auto hover:bg-blue-300 hover:bg-opacity-10 hover:shadow-md">
                <div class="relative top-1/4">
                    <i class="ra {{$icon}} text-blue-500 text-5xl text-opacity-75 hover:text-opacity-95"></i>
                </div>
            </div>
            <div class="mt-5">
                <h4 class="text-gray-700 dark:text-light-200">
                    {{$title}}
                </h4>
            </div>
        </div>
        <p class="mt-10 mb-5 text-lg text-gray-800 dark:text-light-200">
            {{$slot}}
        </p>
    </div>
</a>
