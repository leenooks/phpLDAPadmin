<?php

namespace App\Classes\LDAP\Attribute;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

use App\Classes\LDAP\Attribute;
use App\Classes\Template;
use App\Ldap\Entry;
use App\Traits\MD5Updates;

/**
 * Represents an attribute whose values are passwords
 */
final class Password extends Attribute
{
	use MD5Updates;

	public const obfuscate = '****************';

	protected(set) bool $no_attr_tags = TRUE;
	protected(set) int $max_values_count = 1;

	private const password_helpers = 'Classes/LDAP/Attribute/Password';
	public const commands = 'App\\Classes\\LDAP\\Attribute\\Password\\';

	protected static function helpers(): Collection
	{
		$helpers = collect();

		foreach (preg_grep('/^([^.])/',scandir(app_path(self::password_helpers))) as $file) {
			if (($file === 'Base.php') || (! str_ends_with(strtolower($file),'.php')))
				continue;

			$class = self::commands.preg_replace('/\.php$/','',$file);

			$helpers = $helpers
				->merge([$class::id()=>$class]);
		}

		return $helpers->sort();
	}

	/**
	 * Given an LDAP password syntax {xxx}yyyyyy, this function will return the object for xxx
	 *
	 * @param string $password
	 * @return Attribute\Password\Base|null
	 * @throws \Exception
	 */
	public static function hash(string $password): ?Attribute\Password\Base
	{
		$m = [];
		preg_match('/^{([a-zA-Z0-9]+)}(.*)$/',$password,$m);

		$hash = strtoupper($x=\Arr::get($m,1,'*clear*'));

		// If our hash in the password is not in upper case, then convert it, as we use uppercase hashes to find the right class
		if ($hash !== $x)
			$password = preg_replace('/^{'.$x.'}/','{'.$hash.'}',$password);

		if (($potential=static::helpers()->filter(fn($hasher)=>str_starts_with($hasher::key,$hash)))->count() > 1) {
			foreach ($potential as $item) {
				if ($item::subid($password))
					return new $item;
			}

			throw new \Exception(sprintf('Couldnt figure out a password hash for %s',$password));

		} elseif (! $potential->count()) {
			throw new \Exception(sprintf('Couldnt figure out a password hash for %s',$password));
		}

		return new ($potential->pop());
	}

	/**
	 * Return the object that will process a password
	 *
	 * @param string $id
	 * @return Attribute\Password\Base|null
	 */
	public static function hash_id(string $id): ?Attribute\Password\Base
	{
		return ($helpers=static::helpers())->has($id) ? new ($helpers->get($id)) : NULL;
	}

	public function render(string $attrtag,int $index,bool $edit=FALSE,bool $editable=FALSE,bool $new=FALSE,bool $updated=FALSE,?Template $template=NULL): View
	{
		return view('components.attribute.value.password')
			->with('o',$this)
			->with('dotkey',$dotkey=$this->dotkey($attrtag,$index))
			->with('value',$this->values->dot()->get($dotkey))
			->with('edit',$edit)
			->with('editable',$editable)
			->with('new',$new)
			->with('attrtag',$attrtag)
			->with('index',$index)
			->with('updated',$updated)
			->with('template',$template)
			->with('helpers',static::helpers()->map(fn($item,$key)=>['id'=>$key,'value'=>$key])->sort());
	}

	public function render_item_old(string $dotkey): ?string
	{
		$pw = parent::render_item_old($dotkey);

		return $pw
			? (((($x=$this->hash($pw)) && ($x->id() !== '*clear*')) ? sprintf('{%s}',$x->shortid()) : '')
				.self::obfuscate)
			: NULL;
	}

	public function render_item_new(string $dotkey): ?string
	{
		$pw = parent::render_item_new($dotkey);

		return $pw
			? ((($x=$this->hash($pw)) && ($x->id() === '*clear*')) ? sprintf('{%s}%s',$x->shortid(),self::obfuscate) : $pw)
			: NULL;
	}

	protected function setValuesHelper(Collection $values): Collection
	{
		$processed = collect();

		// If the attr tags are the same value as the md5 tag, then nothing has changed
		foreach ($this->keys as $key) {
			foreach ($values->get($key,[]) as $index => $value) {
				$helper = $values->dot()->get($key.Entry::TAG_HELPER.'.'.$index);

				$processed->put($key.'.'.$index,
					($value && $helper && ($helper !== self::hash($value)->id()))
						? self::hash_id($helper)->encode($value)
						: $value);
			}
		}

		return $processed->undot();
	}
}