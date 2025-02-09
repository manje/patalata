@props(['disabled' => false, 'id' => 'editor', 'name' => 'content', 'value' => ''])

<div>
    <textarea class="w-full h-64" id="{{ $name }}" name="{{ $name }}" {{ $disabled ? 'disabled' : '' }} hidden>{!! $value !!}</textarea>
    <div class='editortoast' id='editor-{{ $name }}' dataid="{{$name}}">{{ $value }}</div>
</div>

