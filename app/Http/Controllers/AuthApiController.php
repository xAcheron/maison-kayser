<?php

namespace App\Http\Controllers;


use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class AuthApiController extends Controller
{
    public function login(Request $request)
    {
        /* $request->validate([
            'email'       => 'required|string|email',
            'password'    => 'required|string',
            'remember_me' => 'boolean',
        ]);*/

        $credentials = $request->only(['email', 'password']);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }
        $user = $request->user();

        if ($user->idTipo == 2) {
            $idProveedor = DB::table('pedidos_proveedor_contacto')
                ->select('idProveedor')
                ->where('email', $user->email)
                ->get();
        }
        // dd($user);
        // $tokenResult = $user->createToken('Personal Access Token');
        // $token = $tokenResult->token;
        // if ($request->remember_me) {
        //     $token->expires_at = Carbon::now()->addWeeks(4);
        // }
        // $token->save();
        return response()->json([
            'id' => Auth::user()->id,
            'nombre' => Auth::user()->name,
            'empresa' => Auth::user()->idEmpresa,
            'area' => Auth::user()->idArea,
            'idTipo' => Auth::user()->idTipo,
            'idProveedor' => $idProveedor[0]->idProveedor ?? 0,
            'access_token' => $user->remember_token,
            'token_type'   => 'Bearer',
            'expires_at'   => Carbon::parse(
                // $tokenResult->token->expires_at
            )
                ->toDateTimeString(),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json(['message' =>
        'Successfully logged out']);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
