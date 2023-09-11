<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use Illuminate\Http\Request;	

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {	
		DB::enableQueryLog();
		$user = Auth::user();		
		$idUsuario = $user->id;
		if(!empty($idUsuario))
		{
            $forallapps = DB::table('config_app')
            ->select('nombre', 'url', 'icono', 'externo')
            ->where('estado', '=', 2);
            
			$apps = DB::table('config_app_access AS cap')
			->join('config_app AS ca','cap.idAplicacion', '=', 'ca.idAplicacion')
			->select('ca.nombre', 'ca.url', 'ca.icono', 'ca.externo')
            ->where('cap.idUsuario', '=', $idUsuario)
            ->union($forallapps)
			->get();
			return view('home', ['apps' => $apps]);
			//return "<b>".bcrypt("Prigo#18")."</b>";
		}
		else
		{
			return view('home');
		}
    }
    
    public function newindex()
    {	
		DB::enableQueryLog();
		$user = Auth::user();		
		$idUsuario = $user->id;
		if(!empty($idUsuario))
		{
			$apps = DB::table('config_app_access AS cap')
			->join('config_app AS ca','cap.idAplicacion', '=', 'ca.idAplicacion')
			->select('ca.nombre', 'ca.url', 'ca.icono')
			->where('cap.idUsuario', '=', $idUsuario)
			->get();
			return view('newhome', ['apps' => $apps]);
		}
		else
		{
			return view('newhome');
		}
    }

	public function showChangePasswordForm(){
        return view('auth.changepassword');
    }
	
    public function changePassword(Request $request){
 
        if (!(Hash::check($request->get('current-password'), Auth::user()->password))) {
            // The passwords matches
            return redirect()->back()->with("error","Your current password does not matches with the password you provided. Please try again.");
        }
 
        if(strcmp($request->get('current-password'), $request->get('password')) == 0){
            //Current password and new password are same
            return redirect()->back()->with("error","New Password cannot be same as your current password. Please choose a different password.");
        }
 
        $validatedData = $this->validate($request,[
            'current-password' => 'required',
            'password' => 'required|string|min:6|confirmed',
        ]);
 
        //Change Password
        $user = Auth::user();
        $user->password = bcrypt($request->get('password'));
        //$user->save();
 
        return redirect()->back()->with("success","Password changed successfully !");
 
    }
}
