<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportRequest extends FormRequest
{
	public function authorize()
	{
		return TRUE;
	}

	public function rules()
	{
		return [
			'frame' => 'required|string|in:import',
			'file' => 'nullable|extensions:ldif|required_without:text',
			'text'=> 'nullable|prohibits:file|string|min:16',
		];
	}
}