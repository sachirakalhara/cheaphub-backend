<?php

namespace App\Http\Controllers\API\Auth;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\AuthUserResource;
use App\Models\User\PasswordReset;
use App\Models\User\User;
use App\Repositories\User\Interface\UserLevelRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Mail\MailQueue;
use App\Models\Payment\Wallet;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class AuthController extends Controller
{
    private $userLevelRepository;

    public function __construct(UserLevelRepositoryInterface $userLevelRepository)
    {
        $this->userLevelRepository = $userLevelRepository;
    }

    public function register(Request $request)
    {
        $request->validate([
            'fname' => 'required|string',
            'lname' => 'required|string',
            'terms_and_conditions' => 'required|boolean',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|confirmed'
        ]);

        $user = new User();
        $user->fname = $request->fname;
        $user->lname = $request->lname;
        $user->display_name = $request->fname.' '.$request->lname;
        $user->terms_and_conditions = $request->terms_and_conditions;
        $user->verification_code = Str::random(60) . uniqid() . time();
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->user_level_id = $this->userLevelRepository->findByScope('customer')->first()->id;
        $user->assignRole('customer');
        if ($user->save()) {
        
            $wallet = new Wallet();
            $wallet->user_id = $user->id;
            $wallet->currency = 'USD';
            $wallet->balance = 0;
            $wallet->save();

    

           $this->sendConfirmationMail($user);

            $user->givePermissionTo('role ' . $user->userLevel->scope);
            activity('user')->causedBy($user)->performedOn($user)->log('registered');
            return response()->json([
                'user' => new AuthUserResource($user),
                'status_code' => 201
            ], 201);
        } else {
            return response()->json([
                'message' => 'Common error',
                'error' => 'common_error',
                'status_code' => 500
            ], 500);
        }
    }


    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ]);

        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return response()->json([
                'message' => 'Invalid credintials',
                'error' => 'invalid_credintials',
                'status_code' => 401
            ], 401);
        }

        $user = $request->user();
        if (!$user->active) {
            return response()->json([
                'message' => 'User blocked',
                'error' => 'user_blocked',
                'status_code' => 401
            ], 401);
        }

    
        if ($request->userLevel == 'super_admin' && $user->userLevel->scope != 'super_admin') {
            return response()->json([
                'message' => 'User Unauthorized',
                'error' => 'user_unauthorized',
                'status_code' => 401
            ], 401);
        }
        
        if ($request->userLevel == 'customer' && $user->userLevel->scope != 'customer') {
            return response()->json([
                'message' => 'User Unauthorized',
                'error' => 'user_unauthorized',
                'status_code' => 401
            ], 401);
        }

        if ($user->email_verified_at == null) {
            return response()->json([
                'message' => "Email not verified",
                'error' => 'common_error',
                'status_code' => 401
            ], 401);

        } else {
           $tokenData = $user->createToken('MyApp', [$user->userLevel->scope]);
            $token = $tokenData->accessToken;
            if ($request->remember_me) {
                $token->expires_at = Carbon::now()->addWeeks(1);
            }

            if ($token->save()) {
                return response()->json([
                    'user' => new AuthUserResource($user),
                    'accessToken' => $tokenData->plainTextToken,
                    'permissions' => $this->allPermissions(),
                    'token_type' => 'Bearer',
                    'expires_at' => Carbon::parse($token->expires_at)->toDayDateTimeString(),
                    'status_code' => 200
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Common error',
                    'error' => 'common_error',
                    'status_code' => 500
                ], 500);
            }
        }
    }

    public function allPermissions()
    {

        $user = Auth::user();
        $all_permissions = [];

        if ($user->userLevel->scope == "super_admin") {
            foreach (Permission::all() as $permission) {
                if (!in_array($permission->name, $all_permissions, true)) {
                    array_push($all_permissions, $permission->name);
                }
            }
        } else {
            foreach ($user->getPermissionsViaRoles() as $value) {
                if (!in_array($value->name, $all_permissions, true)) {
                    array_push($all_permissions, $value->name);
                }
            }
        }

        return $all_permissions;
    }

    public function resendConfirmationMail($user_id)
    {
        $user = User::find($user_id);
        $user->verification_code = Str::random(60) . uniqid() . time();
        $user->save();
        if ($this->sendConfirmationMail($user)) {
            return response()->json([
                'message' => 'Re-Send Successfully',
                'status_code' => 200
            ], 200);
        } else {
            return response()->json([
                'message' => 'Common error',
                'error' => 'common_error',
                'status_code' => 404
            ], 404);
        }
    }

    public function sendConfirmationMail($user)
    {
        Mail::to($user->email)
            ->queue(new MailQueue([
                'subject' => 'Confirm Your Email',
                'template' => 'confirm_email',
                'user' => $user,
                'confirmation_link' => $this->confirmationLink($user)
            ]));
        return true;
    }

    public function confirmMail($user_id, $key)
    {

        $user = User::find($user_id);

        if ($key == $this->confirmKey($user)) {
            $user->verification_code = null;
            $user->email_verified_at = now();
            $user->save();
            return redirect(env('CLIENT_URL') . '/login');
        } else {
            return redirect(env('CLIENT_URL') . '/404');
        }
    }

    public function confirmationLink($user)
    {
        return env('APP_URL') . 'api/v1/confirm-email/' . $user->id . '/' . $this->confirmKey($user);
    }

    public function confirmKey($user)
    {
        return sha1($user->email) . sha1($user->verification_code);
    }

    public function password_create(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'recaptcha' => 'required'
        ]);

        $url = 'https://www.google.com/recaptcha/api/siteverify';

        $data = [
            'secret' => env('RECAPTCHA_SECRET'),
            'response' => $request->recaptcha
        ];

        $options = [
            'http' => [
                'header' => 'Content-type: application/x-www-form-urlencoded\r\n',
                'method' => 'POST',
                'content' => http_build_query($data)
            ]
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $recaptchaResult = json_decode($result);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => "Cannot find user email",
                'error' => 'cannot_find_user_email',
                'status_code' => 404
            ], 404);
        }

        $passwordReset = PasswordReset::updateOrCreate(['email' => $user->email], [
            'email' => $user->email,
            'token' => Str::random(60)
        ]);

        if (($recaptchaResult->success == true) && ($recaptchaResult->score > 0.5)) {
            if ($user && $passwordReset) {
                Mail::to($user->email)->queue(new MailQueue([
                    'subject' => 'Reset Your Password',
                    'template' => 'password_reset',
                    'user' => $user,
                    'token' => $this->password_reset_link($passwordReset)
                ]));
            }
        } else {
            //checked
            return response()->json([
                'message' => "Common error",
                'error' => 'common_error',
                'status_code' => 404
            ], 404);
        }


        return response()->json([
            'message' => 'emailed_reset_pw_link'
        ]);
    }

    public function password_reset_link($passwordReset)
    {
        return env('CLIENT_URL') . '/reset-password/' . $passwordReset->token;
    }

    public function password_find($token)
    {
        $passwordReset = PasswordReset::where('token', $token)->first();

        if (!$passwordReset) {
            return response()->json([
                'message' => "Invalid password reset link",
                'error' => 'invalid_password_reset_link',
                'status_code' => 404
            ], 404);
        }

        if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
            $passwordReset->delete();
            return response()->json([
                'message' => "Common error",
                'error' => 'common_error',
                'status_code' => 404
            ], 404);
        }

        return response()->json($passwordReset);
    }

    public function password_reset(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string|confirmed',
            'token' => 'required|string'
        ]);

        $passwordReset = PasswordReset::where([
            ['token', $request->token],
            ['email', $request->email]
        ])->first();

        if (!$passwordReset) {
            return response()->json([
                'message' => "Invalid password reset link",
                'error' => 'invalidpassword_reset_link',
                'status_code' => 404
            ], 404);
        }

        $user = User::where('email', $passwordReset->email)->first();

        if (!$user) {
            return response()->json([
                'message' => "Cannot find user email",
                'error' => 'cannot_find_user_email',
                'status_code' => 404
            ], 404);
        }

        $user->password = bcrypt($request->password);
        $user->save();

        if ($passwordReset->delete()) {
            Mail::to($user->email)->queue(new MailQueue([
                'subject' => 'Password Reset Successfully',
                'template' => 'password_reset_success',
                'user' => $user,
                'login_link' => $this->login_link()
            ]));
        }

        activity('user')->causedBy($user)->performedOn($user)->log('password_rested');

        return response()->json($user);
    }

    public function login_link()
    {
        return env('CLIENT_URL') . '/login';
    }

    public function ChangeMail($user_id, $key)
    {
        $user = User::find($user_id);

        if ($key == $this->confirmKey($user)) {

            Mail::to($user->email)->queue(new MailQueue([
                'subject' => 'Smart Drive Email Changed',
                'template' => 'email_changed',
                'user' => $user,
                'new_email' => $user->new_email
            ]));

            $user->verification_code = null;
            $user->email_verified_at = now();
            $user->email = $user->new_email;
            $user->new_email = null;
            $user->save();

            return redirect(env('CLIENT_URL') . '/login');
        } else {
            return redirect(env('CLIENT_URL') . '/404');
        }
    }


    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        activity('user')->causedBy($request->user())->performedOn($request->user())->log('logout');

        return response()->json([
            'message' => 'Logout Successfully',
            'status_code' => 200
        ], 200);
    }
}
