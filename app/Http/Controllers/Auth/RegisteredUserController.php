<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
	public function store_customer(Request $request)
	{
		//echo "<pre>";print_r($request->all());
		 $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirm', Rules\Password::defaults()],
        ]);
		
		$first_name  		= $request->first_name;
		$last_name  		= $request->last_name;
		$email  			= $request->email;
		$password  			= $request->password;
		$confirm_password  	= $request->confirm_password;
		$company_name  		= $request->company_name;
		$address  			= $request->address;
		$city  				= $request->city;
		$state  			= $request->state;
		$zipcode  			= $request->zipcode;
		$phone_number  		= $request->phone_number;
		$upload_tax_lisence = $request->upload_tax_lisence;
		
		$moidel = new User();
		$moidel->first_name = $request->first_name ?? null;
		$moidel->last_name = $request->last_name ?? null;
		$moidel->email = $request->email ?? null;
		$moidel->password = $request->password ?? null;
		$moidel->city = $request->city ?? null;
		$moidel->state = $request->state ?? null;
		$moidel->zipcode = $request->zipcode ?? null;
		$moidel->phone_number = $request->phone_number ?? null;
		$moidel->upload_tax_lisence = $request->upload_tax_lisenc ?? nulle;
		$moidel->save();
		
		
	}
}
