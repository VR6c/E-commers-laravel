<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/admin/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    /**
     * Attempt to log the user into the application with defensive check against unhashed passwords.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(\Illuminate\Http\Request $request)
    {
        try {
            $user = \App\Models\User::where($this->username(), $request->input($this->username()))->first();
            if ($user && ! empty($user->password)) {
                $info = password_get_info($user->password);
                if ($info['algo'] === 0) {
                    if ($request->input('password') === $user->password) {
                        $user->password = $request->input('password');
                        $user->save();
                        $this->guard()->login($user, $request->filled('remember'));

                        return true;
                    }

                    return false;
                }
            }

            return $this->guard()->attempt(
                $this->credentials($request), $request->filled('remember')
            );
        } catch (\RuntimeException $e) {
            return false;
        }
    }
}
