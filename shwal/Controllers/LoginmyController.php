<?php


namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Sms;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class LoginmyController extends Controller
{
    //проверка кода
    public function checkCodeLogin(Request $request)
    {
        if ($request->has('code') && $request->has('id_sms') && $request->phone) {

            // получить исключенные логины ***********************************
            $loginSelected = config('auth.loginSelected');
            if (isset($loginSelected[$request->phone])) {
                if ($loginSelected[$request->phone] == $request->code) {

                    $users = User::where('phone', $request->phone)->active()->select('id')->get();
                    if ($users) {
                        if (count($users) > 1) {
                            $user_data = [];
                            foreach ($users as $user) {
                                $roles = $user->roles;
                                if ($user->hasRole('admin')) {
                                    $user_data[] = [
                                        'id' => $user->id,
                                        'role' => 'admin'
                                    ];
                                } elseif ($user->hasRole('manager')) {
                                    $user_data[] = [
                                        'id' => $user->id,
                                        'role' => 'manager'
                                    ];
                                } else {
                                    $user_data[] = [
                                        'id' => $user->id,
                                        'role' => 'barmen'
                                    ];
                                }
                            }
                            return response()->json(['users' => $user_data]);
                        } else {
                            Auth::loginUsingId($users[0]->id, true);
                            return response()->json(['suc' => true]);
                        }
                    } else {
                        return response()->json(['err' => true], 404);
                    }
                } else {
                    return response()->json(['suc' => false]);
                }
            }
            $phone = trim($request->phone);
            // получить исключенные логины end **********************************
            $checkCode = Sms::where('id_sms', '=', $request->id_sms)->select('code')->orderBy('id', 'desc')->first();
            if ($checkCode->code == $request->code) {
                $users = User::where('phone', $phone)->active()->select('id')->get();
                if ($users) {

                    if (count($users) > 1) {
                        $user_data = [];
                        foreach ($users as $user) {
                            $roles = $user->roles;
                            if ($user->hasRole('admin')) {
                                $user_data[] = [
                                    'id' => $user->id,
                                    'role' => 'admin'
                                ];
                            } elseif ($user->hasRole('manager')) {
                                $user_data[] = [
                                    'id' => $user->id,
                                    'role' => 'manager'
                                ];
                            } else {
                                $user_data[] = [
                                    'id' => $user->id,
                                    'role' => 'barmen'
                                ];
                            }
                        }
                        return response()->json(['users' => $user_data]);
                    } else {
                        Auth::loginUsingId($users[0]->id, true);
                        return response()->json(['suc' => true]);
                    }

                } else {
                    return response()->json(['err' => true], 404);
                }
            } else {
                return response()->json(['suc' => false]);
            }
        } else {
            return response()->json(['err' => true], 404);
        }
    }

    public function checkRole(Request $request)
    {
        if ($request->has('role')) {
            Auth::loginUsingId($request->role, true);
            return response()->json(['suc' => true]);
        } else {
            return response()->json(['err' => true], 404);
        }
    }

}