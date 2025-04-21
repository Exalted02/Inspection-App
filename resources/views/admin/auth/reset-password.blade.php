<x-admin-guest-layout>
    <h2 class="text-center text-xl font-semibold">Reset Password</h2>
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
    <form action="{{ route('admin_reset_password_submit') }}" method="post">
        @csrf
        
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">
		<div>
			<x-input-label for="password" :value="__('Password')" />
			<x-text-input id="password" class="block mt-1 w-full" type="password" name="password" />
			<x-input-error :messages="$errors->get('password')" class="mt-2" />
		</div>
		<div class="mt-4">
			<x-input-label for="password_confirmation" :value="__('Confirm Password')" />
			<x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" />
			<x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
		</div>
		<div class="flex items-center justify-end mt-4">
			<x-primary-button class="ml-3">
				{{ __('Submit') }}
			</x-primary-button>
		</div>
    </form>
    
</x-admin-guest-layout>