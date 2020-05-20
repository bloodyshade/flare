<div class="modal fade" id="slot-{{$slot->id}}" tabindex="-1" role="dialog" aria-labelledby="DestroyLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Destroy</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you weant to do this? TRhis action cannot be undone. You will lose the item.</p>


                <form id="destroy-item" action="{{route('game.destroy.item')}}" method="POST">
                    @csrf

                    <input type="hidden" name="slot_id" value="{{$slot->id}}" />
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                <a class="btn btn-danger" href="{{route('game.destroy.item')}}"
                   onclick="event.preventDefault();
                                 document.getElementById('destroy-item').submit();">
                    {{ __('Destroy') }}
                </a>
            </div>
        </div>
    </div>
</div>
