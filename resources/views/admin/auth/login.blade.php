<x-admin-guest-layout>
	@if($errors->any())
		@foreach($errors->all() as $error)
			<li>{{ $error }}</li>
		@endforeach
	@endif
	@if(Session::has('error'))
		<li>{{ Session::get('error') }}</li>
	@endif
	@if(Session::has('success'))
		<li>{{ Session::get('success') }}</li>
	@endif
	<form action="{{ route('admin_login_submit') }}" method="post">
		@csrf
		<div>
			<x-input-label for="email" :value="__('Email')" />
			<x-text-input id="email" class="block mt-1 w-full" type="email" name="email" />
			<x-input-error :messages="$errors->get('email')" class="mt-2" />
		</div>
		<div class="mt-4">
			<x-input-label for="password" :value="__('Password')" />

			<x-text-input id="password" class="block mt-1 w-full"
							type="password"
							name="password"/>

			<x-input-error :messages="$errors->get('password')" class="mt-2" />
		</div>
		<div class="flex items-center justify-end mt-4">
			<a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('admin_forget_password') }}">
				{{ __('Forgot your password?') }}
			</a>
			<x-primary-button class="ml-3">
				{{ __('Log in') }}
			</x-primary-button>
		</div>
	</form>
</x-admin-guest-layout>