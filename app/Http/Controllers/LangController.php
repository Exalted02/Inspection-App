<?php
  
namespace App\Http\Controllers;
  
use Illuminate\Http\Request;
use App;
 use App\Models\News_blog;
class LangController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function index()
    {
        return view('lang');
    }
  
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function change(Request $request)
    {
        App::setLocale($request->lang);
        session()->put('locale', $request->lang);
		// Get the current slug and look for its translation
        
		return redirect()->back();
		
		//$redirectTo = $request->get('redirect_to', //url()->previous());
        //return redirect($redirectTo);
    }
}
