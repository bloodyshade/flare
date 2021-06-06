<div class="flex items-center ml-auto">

    <label class="switch switch_outlined mr-3" data-toggle="tooltip" data-tippy-content="Toggle Dark Mode">
        <input id="darkModeToggler" type="checkbox">
        <span></span>
    </label>

    <ul class="flex space-x-6 text-lg">
        <li>
            <a class="text-gray-600 hover:text-gray-500" href="{{route('login')}}">Login</a>
        </li>
        <li>
            <a class="text-gray-600 hover:text-gray-500" href="{{route('register')}}">Register</a>
        </li>
    </ul>
</div>
