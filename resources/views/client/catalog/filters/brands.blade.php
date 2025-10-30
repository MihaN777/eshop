<div>
    <h5 class="mb-4 text-sm 2xl:text-md font-bold">{{ $filter->title() }}</h5>

    @foreach($filter->values() as $id => $title)
        <div class="form-checkbox">
            <input name="{{ $filter->name($id) }}"
                   id="{{ $filter->id($id) }}"
                   value="{{ $id }}"
                   @checked($filter->requestValue($id))
                   type="checkbox"
            >

            <label for="{{ $filter->id($id) }}" class="form-checkbox-label" style="min-width:300px;">
                {{ $title }}
            </label>
        </div>
    @endforeach
</div>