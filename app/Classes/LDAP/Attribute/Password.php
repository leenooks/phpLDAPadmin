<?php

namespace App\Classes\LDAP\Attribute;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

use App\Classes\LDAP\Attribute\Password\Base;
use App\Classes\LDAP\Attribute;
use App\Traits\MD5Updates;

/**
 * Represents an attribute whose values are passwords
 */
final class Password extends Attribute
{
	use MD5Updates;
	private const password_helpers = 'Classes/LDAP/Attribute/Password';
	public const commands = 'App\\Classes\\LDAP\\Attribute\\Password\\';

	private static function helpers(): Collection
	{
		$helpers = collect();

		foreach (preg_grep('/^([^.])/',scandir(app_path(self::password_helpers))) as $file) {
			if (($file === 'Base.php') || (! str_ends_with(strtolower($file),'.php')))
				continue;

			$class = self::commands.preg_replace('/\.php$/','',$file);
			if ($helpers->count())
				$helpers->push('');

			$helpers = $helpers
				->merge([$class::id()=>$class]);
		}

		return $helpers;
	}

	/**
	 * Return the object that will process a password
	 *
	 * @param string $id
	 * @return Base|null
	 */
	public static function hash(string $id): ?Attribute\Password\Base
	{
		return ($helpers=static::helpers())->has($id) ? new ($helpers->get($id)) : NULL;
	}

	/**
	 * Given an LDAP password syntax {xxx}yyyyyy, this function will return xxx
	 *
	 * @param string $password
	 * @return string
	 */
	public static function hash_id(string $password): string
	{
		$m = [];
		preg_match('/^{([A-Z]+)}(.*)$/',$password,$m);

		return Arr::get($m,1,'Clear');
	}

	public function render(bool $edit=FALSE,bool $old=FALSE,bool $new=FALSE): View
	{
		return view('components.attribute.password')
			->with('o',$this)
			->with('edit',$edit)
			->with('old',$old)
			->with('new',$new)
			->with('helpers',static::helpers()->map(fn($item,$key)=>['id'=>$key,'value'=>$key]));
	}

	public function render_item_old(int $key): ?string
	{
		return Arr::get($this->oldValues,$key) ? str_repeat('x',8) : NULL;
	}

	public function render_item_new(int $key): ?string
	{
		return Arr::get($this->values,$key) ? str_repeat('x',8) : NULL;
	}
}