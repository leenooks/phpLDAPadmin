<!-- $o=Attribute::class -->
<div class="input-group">
	<input type="text" class="form-control mb-1" value="{{ \Carbon\Carbon::createFromTimestamp(strtotime($value))->format(config('pla.datetime_format','Y-m-d H:i:s')) }}" disabled>
</div>