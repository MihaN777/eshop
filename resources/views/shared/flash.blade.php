@if ($message = flash()->get())
    <div class="{{ $message->class() }}" style="{{ $message->style() }}">
        {{ $message->message() }}
    </div>
@endif
