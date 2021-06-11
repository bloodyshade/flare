<div class="flex items-center ml-auto">

    <!-- Dark Mode -->
    <label class="switch switch_outlined" data-toggle="tooltip" data-tippy-content="Toggle Dark Mode">
        <input id="darkModeToggler" type="checkbox">
        <span></span>
    </label>

    <!-- Fullscreen -->
    <button id="fullScreenToggler" type="button"
            class="hidden lg:inline-block btn-link ml-3 px-2 text-2xl leading-none la la-expand-arrows-alt"
            data-toggle="tooltip" data-tippy-content="Fullscreen"></button>


    <!-- Notifications -->
    <div class="dropdown self-stretch">
        <button type="button"
                class="relative flex items-center h-full btn-link ml-1 px-2 text-2xl leading-none la la-bell"
                data-toggle="custom-dropdown-menu" data-tippy-arrow="true" data-tippy-placement="bottom-end">
                <span
                    class="absolute top-0 right-0 rounded-full border border-primary -mt-1 -mr-1 px-2 leading-tight text-xs font-body text-primary">3</span>
        </button>
        <div class="custom-dropdown-menu">
            <div class="flex items-center px-5 py-2">
                <h5 class="mb-0 uppercase">Notifications</h5>
                <button class="btn btn_outlined btn_warning uppercase ml-auto">Clear All</button>
            </div>
            <hr>
            <div class="p-5 hover:bg-primary-100 dark:hover:bg-primary-900">
                <a href="#">
                    <h6 class="uppercase">Heading One</h6>
                </a>
                <p>Lorem ipsum dolor, sit amet consectetur.</p>
                <small>Today</small>
            </div>
            <hr>
            <div class="p-5 hover:bg-primary-100 dark:hover:bg-primary-900">
                <a href="#">
                    <h6 class="uppercase">Heading Two</h6>
                </a>
                <p>Mollitia sequi dolor architecto aut deserunt.</p>
                <small>Yesterday</small>
            </div>
            <hr>
            <div class="p-5 hover:bg-primary-100 dark:hover:bg-primary-900">
                <a href="#">
                    <h6 class="uppercase">Heading Three</h6>
                </a>
                <p>Nobis reprehenderit sed quos deserunt</p>
                <small>Last Week</small>
            </div>
        </div>
    </div>

    <!-- User Menu -->
    <div class="dropdown">
        <button class="flex items-center ml-4 text-gray-700" data-toggle="custom-dropdown-menu"
                data-tippy-arrow="true" data-tippy-placement="bottom-end">
            <span class="avatar">JD</span>
        </button>
        <div class="custom-dropdown-menu w-64">
            <div class="p-5">
                <h5 class="uppercase">Character Name</h5>
            </div>
            <hr>
            <div class="p-5">
                <a href="#"
                   class="flex items-center text-gray-700 dark:text-gray-500 hover:text-primary dark:hover:text-primary">
                    <span class="la la-user-circle text-2xl leading-none mr-2"></span>
                    Help
                </a>
                <a href="#"
                   class="flex items-center text-gray-700 dark:text-gray-500 hover:text-primary dark:hover:text-primary mt-5">
                    <span class="la la-key text-2xl leading-none mr-2"></span>
                    Settings
                </a>
            </div>
            <hr>
            <div class="p-5">
                <a href="#"
                   class="flex items-center text-gray-700 dark:text-gray-500 hover:text-primary dark:hover:text-primary">
                    <span class="la la-power-off text-2xl leading-none mr-2"></span>
                    Logout
                </a>
            </div>
        </div>
    </div>
</div>
