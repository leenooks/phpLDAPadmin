@extends('layouts.dn')

@section('page_title')
	<table class="table table-borderless">
		<tr>
			<td><div class="page-title-icon f32"><i class="fas fa-upload"></i></div></td>
			<td class="top text-start align-text-top p-2"><strong>@lang('LDIF Import')</strong><br><small>@lang('To Server') <strong>{{ $server->name }}</strong></small></td>
		</tr>
	</table>
@endsection

@section('main-content')
	<div class="row">
		<div class="offset-1 col-10">
			<div class="main-card mb-3 card">
				<form id="import-form" action="{{ url('import/process/ldif') }}" method="POST" enctype="multipart/form-data">
					@csrf
					<input type="hidden" name="key" value="{{ Crypt::encryptString('*import|_NOP') }}">

					<div class="card-header">
						@lang('LDIF Import')
					</div>

					<div class="card-body">
						<div class="row">
							<div class="col">
								<div class="form-group">
									<label for="ldif-text" class="pb-2"><strong>@lang('Paste in your LDIF here')</strong></label>
									<textarea class="form-control text-monospace @error('text') is-invalid @enderror" name="text" rows="10">{{ old('text') }}</textarea>

									<div class="invalid-feedback pb-2">
										@error('text')
											{{ $message }}
										@enderror
									</div>
								</div>
							</div>
						</div>

						<div class="row pt-5">
							<div class="col">
								<div class="form-group">
									<label for="ldif-file" class="pb-2"><strong>@lang('Or upload LDIF file')</strong></label><br>
									<input type="file" class="form-control-file @error('file') is-invalid @enderror" name="file" accept=".ldif"><br>
									<small class="form-text text-muted @error('file') is-invalid @enderror">@lang('Maximum file size') <strong>{{ ini_get('upload_max_filesize') }}</strong></small>
									<div class="invalid-feedback pb-2">
										@error('file')
											{{ $message }}
										@enderror
									</div>
								</div>
							</div>
						</div>
					</div>
				</form>

				<div class="card-footer">
					<span class="ms-auto">
						<x-form.submit :action="__('Process')" form="import-form"/>
					</span>
				</div>
			</div>
		</div>
	</div>
@endsection