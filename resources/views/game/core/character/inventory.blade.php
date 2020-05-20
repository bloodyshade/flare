@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Character Inventory</h4>
                    <hr />

                    <div class="row">
                        <div class="col-md-6">
                            <h6>Inventory</h6>
                            @include('game.core.partials.inventory', [
                                'inventory' => $inventory,
                                'actions'   => 'manage',
                            ])
                        </div>

                        <div class="col-md-6">
                            <h6>Quest Items</h6>
                            @if ($questItems->isEmpty())
                                <div class="alert alert-info">You have no quest items.</div>
                            @else
                                <?php dump($questItems); ?>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Equiped Items</h4>
                    <hr />
                    <strong>Max Attack: </strong> {{$characterInfo['maxAttack']}}
                    <hr />
                    <table class="table table-bordered text-center">
                        <thead class="thead-dark">
                            <tr>
                                <th>Name</th>
                                <th>Base Damage</th>
                                <th>Type</th>
                                <th>Position</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @foreach($equipped as $equippedItem)
                                <tr>
                                    <td>{{$equippedItem->item->name}}</td>
                                    <td>{{$equippedItem->item->base_damage}}</td>
                                    <td>{{$equippedItem->item->type}}</td>
                                    <td>{{$equippedItem->position}}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
