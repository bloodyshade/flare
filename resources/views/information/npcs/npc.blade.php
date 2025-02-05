@extends('layouts.information')

@section('content')
    <div class="tw-w-full lg:tw-w-3/4 tw-m-auto tw-mt-20 tw-mb-10">
        <x-core.page-title
          title="{{$npc->name}}"
          route="{{url()->previous()}}"
          link="Back"
          color="primary"
        ></x-core.page-title>

        <hr />
        @include('admin.npcs.partials.show', ['npc' => $npc])
    </div>
@endsection
