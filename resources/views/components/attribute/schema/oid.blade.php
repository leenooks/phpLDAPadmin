{!! $o->values
	->transform(function($item) use ($o) {
		return preg_match('/[0-9]+\.[0-9]+\.[0-9]+/',$item)
			? sprintf('<abbr class="pb-1" title="%s"><i class="fas fa-list-ol pe-2"></i>%s</abbr>%s<p class="mb-0">%s</p>',
				$item,
				$o->get($item,'title'),
				($x=$o->get($item,'ref')) ? sprintf('<abbr class="ps-2" title="%s"><i class="fas fa-comment-dots"></i></abbr>',$x) : '',
				$o->get($item,'desc'),
			)
			: $item;
	})->join('<br>') !!}