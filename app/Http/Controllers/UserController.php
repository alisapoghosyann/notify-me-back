<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\StoreUser;
use App\Mail\NotifyMail;
use App\Mail\VerificationCode;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Nexmo\Laravel\Facade\Nexmo;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'code', 'create']]);
    }

    public function register(StoreUser $request)
    {
        $res = mt_rand(100000, 999999);
        $email = $request->email;
        $user['email'] = $email;
        $user['name'] = $request->name;
        $user['lastname'] = $request->lastname;
        $user['phone'] = $request->phone;
        $user['password'] = bcrypt($request->password);
        $user['verification_code'] = $res;

        if (User::where('email', $email)->count() > 0) {
            return new JsonResponse([
                'emailError' => "That account already exists",
            ], 409);
        } else {
            User::create($user);

                Session::push('code', $res);
                Mail::to($email)->send(new VerificationCode($res));
                return new JsonResponse([
                   'message' => 'true',
                ],201);
            }
      }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function message(Request $request)
    {
        Mail::to(env('NO_REPLY'))->send(new NotifyMail($request));
        return new JsonResponse([
            'message' => 'Message Successfuly sent',
        ], 201);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function code(Request $request)
    {
        $res = mt_rand(100000, 999999);

        User::where('email', $request->email)->update([
            'verification_code' => $res
        ]);
        Mail::to($request->email)->send(new VerificationCode($res));
        return new JsonResponse([
            'message' => 'true',
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $user = User::where('verification_code', $request->code)->update([
            'status' => '1'
        ]);

        if ($user) {
            $item = User::where('email', $request->email)->first();
            try {
                if (!$token = JWTAuth::fromUser($item)) {
                    return response()->json(['error' => 'invalid_credentials'], 401);
                }
            } catch (JWTException $e) {
                return response()->json(['error' => 'could_not_create_token'], 500);
            }
            return $this->respondWithToken($token);
        } else {
            return new JsonResponse([
                'message' => 'false',
            ]);
        }
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

//    public function sendSmsNotificaition()
//    {
//        try {
//
//            $basic = new \Nexmo\Client\Credentials\Basic(getenv("NEXMO_KEY"), getenv("NEXMO_SECRET"));
//            $client = new \Nexmo\Client($basic);
//
//            $receiverNumber = "91846XXXXX";
//            $message = "This is testing from ItSolutionStuff.com";
//
//
//            $message = $client->message()->send([
//                'to' => $receiverNumber,
//                'from' => 'Vonage APIs',
//                'text' => $message
//            ]);
//
//            dd('SMS Sent Successfully.');
//
//        } catch (Exception $e) {
//            dd("Error: " . $e->getMessage());
//        }
//
//    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    protected function respondWithToken($token)
    {
        return new JsonResponse([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
