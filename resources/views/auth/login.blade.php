@extends('layouts.app')

@section('content')

    <x-common.small-container>
        <x-common.cards.card-with-title title="Login">
            <form method="POST" action="{{ route('login') }}">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                        Email Address
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                           id="email" name="email" type="text" placeholder="Username" required autocomplete="email" autofocus @error('email') is-invalid @enderror>
                    @error('email')
                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                    @enderror
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                        Password
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                           id="email" name="password" type="text" required autocomplete="current-password" autofocus @error('email') is-invalid @enderror>
                    @error('email')
                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                    @enderror
                </div>
                <div class="flex items-center justify-between">
                    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                        Sign In
                    </button>
                    <span class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800" ">
                        <a href="{{ route('password.request') }}">Forgot Password?</a>
                        <a href="{{ route('un.ban.request') }}" class="px-2">Banned Unfairly?</a>
                    </span>
                </div>
            </form>
        </x-common.cards.card-with-title>
    </x-common.small-container>
@endsection
