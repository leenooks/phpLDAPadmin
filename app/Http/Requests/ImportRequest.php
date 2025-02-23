<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportRequest extends FormRequest
{
	public function rules(): array
	{
		return [
			'file' => 'nullable|extensions:ldif|required_without:text',
			'text'=> 'nullable|prohibits:file|string|min:16',
		];
	}
}