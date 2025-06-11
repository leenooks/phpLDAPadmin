<?php

namespace App\Classes;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class Template
{
	private string $file;
	private array $template;
	private(set) bool $invalid = FALSE;
	private string $reason = '';

	public function __construct(string $file)
	{
		$td = Storage::disk(config('pla.template.dir'));

		$this->file = $file;

		try {
			$this->template = json_decode($td->get($file),null,512,JSON_OBJECT_AS_ARRAY|JSON_THROW_ON_ERROR);

		} catch (\JsonException $e) {
			$this->invalid = TRUE;
			$this->reason = $e->getMessage();
		}
	}

	public function __get(string $key): mixed
	{
		return match ($key) {
			'attributes' => collect(array_map('strtolower',array_keys(Arr::get($this->template,$key)))),
			'objectclasses' => collect(array_map('strtolower',Arr::get($this->template,$key))),
			'enabled' => Arr::get($this->template,$key,FALSE) && (! $this->invalid),
			'icon','regexp','title' => Arr::get($this->template,$key),

			default => throw new \Exception('Unknown key: '.$key),
		};
	}

	public function __isset(string $key): bool
	{
		return array_key_exists($key,$this->template);
	}

	public function __toString(): string
	{
		return $this->invalid ? '' : Arr::get($this->template,'title','No Template Name');
	}
}