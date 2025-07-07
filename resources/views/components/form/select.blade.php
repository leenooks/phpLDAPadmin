@isset($name)
	<input type="hidden" id="{{ $id ?? $name }}_disabled" name="{{ $name }}" value="" disabled>
@endisset

<div class="input-group">
	<x-select :id="$id ?? NULL" :name="$name ?? NULL" :options="$options ?? []" :value="$value ?? NULL" :class="$class ?? NULL"/>
</div>