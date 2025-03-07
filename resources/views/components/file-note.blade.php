@if(file_exists($file))
	<div class="row pb-3">
		<div class="col-12 offset-md-2 col-md-8">
			<div class="mx-auto card text-white card-body bg-primary">
				<h5 class="text-white card-title"><i class="icon fa-2x fas fa-info pe-3"></i><span class="font-size-xlg">NOTE</span></h5>
				<span class="w-100 pb-0">
					{!! file_get_contents($file) !!}
				</span>
			</div>
		</div>
	</div>
@endif