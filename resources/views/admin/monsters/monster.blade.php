@extends('layouts.app')

@section('content')

<div class="container-fluid ">
    <div class="container justify-content-center">
        @include('admin.monsters.partials.monster', ['monster' => $monster])
    </div>
</div>
@endsection
