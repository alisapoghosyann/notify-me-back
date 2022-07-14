<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUser;
use App\Mail\NotifyMail;
use App\Mail\VerificationCode;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use phpDocumentor\Reflection\Types\False_;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

        public function register(StoreUser $request)
        {
            $user = new User;
            $email = $request ->email;
            $user['email'] = $email;
            $user['name'] = $request->name;
            $user['lastname'] = $request->lastname;
            $user['phone']= $request->phone;
            $user['password']= bcrypt($request->password);

            Session::push('user', $user);

            if (User::where('email', $email)->count() > 0) {
                return new JsonResponse([
                    'emailError'=>"That account already exists",
                ],409);
            }else{
                $res = mt_rand(100000,999999);

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
            'message'=>'Message Successfuly sent',
        ],201);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function code(Request $request)
    {
        $res = mt_rand(100000,999999);

        Mail::to($request->email)->send(new VerificationCode($res));
        return new JsonResponse([
            'message'=>$res,
            'user'=>Session::get('user'),
        ],201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if($request -> code = Session::get('code')){
            User::create(Session::get('user'));
            Session::flush('user');
            return  new JsonResponse([
                'message' => 'true',
            ]);
        }else{
            return  new JsonResponse([
                'message' => 'false',
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
