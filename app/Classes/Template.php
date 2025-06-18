<?php

namespace App\Classes;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Template
{
	private string $file;
	private array $template;
	private(set) bool $invalid = FALSE;
	private(set) string $reason = '';

	public function __construct(string $file)
	{
		$td = Storage::disk(config('pla.template.dir'));

		$this->file = $file;

		try {
			// @todo Load in the proper attribute objects and objectclass objects
			// @todo Make sure we have a structural objectclass, or make the template invalid
			$this->template = json_decode($td->get($file),null,512,JSON_OBJECT_AS_ARRAY|JSON_THROW_ON_ERROR);

		} catch (\JsonException $e) {
			$this->invalid = TRUE;
			$this->reason = $e->getMessage();
		}
	}

	public function __get(string $key): mixed
	{
		return match ($key) {
			'attributes' => collect(Arr::get($this->template,$key))->keys(),
			'enabled' => Arr::get($this->template,$key,FALSE) && (! $this->invalid),
			'icon','regexp','title' => Arr::get($this->template,$key),
			'name' => Str::replaceEnd('.json','',$this->file),
			'objectclasses' => collect(Arr::get($this->template,$key)),
			'order' => collect(Arr::get($this->template,'attributes'))->map(fn($item)=>$item['order']),

			default => throw new \Exception('Unknown key: '.$key),
		};
	}

	public function __isset(string $key): bool
	{
		return array_key_exists($key,$this->template);
	}
}