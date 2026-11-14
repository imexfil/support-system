<label>{{ __($label) }}
    <select name="{{ $field }}">
        @foreach($choices as $value => $text)
            <option value="{{ $value }}" @selected(old($field, $selected) === $value)>{{ __($text) }}</option>
        @endforeach
    </select>
</label>
