<?php

namespace App\Http\Controllers;

use App\Mail\NotifyMail;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Mail;




use function MongoDB\BSON\toRelaxedExtendedJSON;

class AdminController extends Controller
{

    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:5,19',
            'email' => 'email:rfc,dns',
            'password' => 'required|confirmed|string|min:6',
            'img' => 'required|image|size:max:300|dimensions:max_width=200,max_height=300'
        ]);
        $email = $request->email;


        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $limit = Admin::all()->count();
        if($limit <= 2) {
            $admin = Admin::create(array_merge(
                $validator->validated(),
                ['password' => bcrypt($request->password)]
            ));

            $res = 'Thank you for registering';

            Mail::to($email)->send(new NotifyMail($res));
            return new JsonResponse([
                'message'=>"Thank you for registering",
                'admin' => $admin,
                'data' => User::withTrashed()->orderBy('id','desc')->get()->toArray()
            ],200);

        }else{
            return response()->json([
                'message' => "Admin couldn't registered",
                'user' => 'The registration limit has been reached'
            ]);
        }
    }

    public function deleteUser(Request $request)
    {
        User::find($request->id)->delete();
        $users =  User::query()->where('id',$request->id);
        $res = "You are deleted";

        foreach ($users as $user){
            Mail::to($user->email)->send(new NotifyMail($res));
            return new JsonResponse([
                'message'=>$res,
                'data' => User::withTrashed()->orderBy('id','desc')->get()->toArray()
            ],200);
        }
    }

    public function blockUser(Request $request)
    {
        User::query()->where('id',$request->id)->update([
            'block'=>0,
        ]);

        $users =  User::query()->where('id',$request->id);
        $res = "You're bloked";

        foreach ($users as $user){
            Mail::to($user->email)->send(new NotifyMail($res));
            return new JsonResponse([
                'message'=>$res,
                'data' => User::withTrashed()->orderBy('id','desc')->get()->toArray()
            ],200);
        }
    }

    public function activeUser(Request $request)
    {
        User::onlyTrashed()->where('id', $request->id)->restore();
        $users =  User::query()->where('id',$request->id);
        $res = "You are restored";

        foreach ($users as $user){
            Mail::to($user->email)->send(new NotifyMail($res));
            return new JsonResponse([
                'message'=>$res,
                'data' => User::withTrashed()->orderBy('id','desc')->get()->toArray()
            ],200);
        }
    }
}
