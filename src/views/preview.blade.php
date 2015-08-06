<div id="{{ $id }}"{!! $css !!}></div>
@foreach($load_data as $load_values)
    <input class="{{ $id_hidden_name }}" type="hidden" name="{{ $id_hidden_name }}[]" value="{{ $load_values['id'] }}">
@endforeach