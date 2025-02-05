<div class="container-fluid">
    <div class="row page-titles">
        <div class="col-md-12 align-self-right">
            <a href="{{url()->previous()}}" class="btn btn-primary float-right ml-2">Back</a>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">{{$location->name}}</h4>
                    <p>{{$location->description}}</p>
                    <hr />
                    <dl>
                        <dt>Location X Coordinate:</dt>
                        <dd>{{$location->x}}</dd>
                        <dt>Location Y Coordinate:</dt>
                        <dd>{{$location->y}}</dd>
                        <dt>On Map:</dt>
                        <dd>{{$location->map->name}}</dd>
                        <dt>Is Port:</dt>
                        <dd>{{$location->is_port ? 'Yes' : 'No'}}</dd>
                        <dt>Increases Enemy Strength By:</dt>
                        <dd>{{!is_null($increasesEnemyStrengthBy) ? $increasesEnemyStrengthBy : 'None.'}}</dd>
                        <dt>Increases Drop Rate By:</dt>
                        <dd>{{$increasesDropChanceBy * 100}}%</dd>
                    </dl>
                    @if (!is_null($increasesEnemyStrengthBy))
                        <hr />
                        <h3>Items that can drop from this location.</h3>
                        <p>
                            Auto battle will not allow you to obtain these items. You must manually farm them. These have a 1/1,000,000 chance to drop.
                            Looting Skill Bonus is capped at 45%.
                        </p>
                        <p>
                            If this location is on a plane that effects enemy stats (Shadow Plane, Hell and Purgatory) then those stat modifications
                            will be taken into account along with the locations enemy modifications. Your gear, stats and level matter.
                        </p>
                        @livewire('admin.items.data-table', [
                            'specialLocation' => $location->id,
                            'type'            => 'quest'
                        ])
                    @endif
                    @if (auth()->user())
                        <hr />
                        @if (auth()->user()->hasRole('Admin'))
                            <a href="{{route('location.edit', [
                                'location' => $location->id,
                            ])}}" class="btn btn-primary mt-2">Edit Location</a>
                        @endif
                    @endif

                </div>
            </div>

            @if (!is_null($location->questRewardItem))
                <x-core.alerts.info-alert title="Game Tip">
                    <p>
                        If a location has a quest reward associated with it, all you have to do is physically
                        visit the location to get the quest reward.
                    </p>
                </x-core.alerts.info-alert>
                @include('game.items.partials.item-quest-details', ['item' => $location->questRewardItem])
            @else
                @auth
                    @if (auth()->user()->hasRole('Admin'))
                        <div class="alert alert-info"> This location has no quest item rewards. <a href="{{route('location.edit', [
                                        'location' => $location->id,
                                    ])}}">Assign one.</a> </div>
                    @endif
                @endauth
            @endif
        </div>
    </div>
</div>
