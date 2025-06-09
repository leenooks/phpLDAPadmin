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
			'attributes' => array_map('strtolower',array_keys(Arr::get($this->template,$key))),
			'objectclasses' => array_map('strtolower',Arr::get($this->template,$key)),
			'enabled' => Arr::get($this->template,$key,FALSE),
			'icon','regexp' => Arr::get($this->template,$key),

			default => throw new \Exception('Unknown key: '.$key),
		};
	}

	public function __toString(): string
	{
		return $this->invalid ? '' : Arr::get($this->template,'title','No Template Name');
	}
}