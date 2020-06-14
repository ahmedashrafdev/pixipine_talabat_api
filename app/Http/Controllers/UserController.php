<?php

namespace App\Http\Controllers;

use App\Address;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\Client;

class UserController extends Controller
{
    public function getOrders(Request $request)
    {
        $search = isset($request->search) ? $request->search : '';

        $orders = DB::select('call getOrders(? , ? , ? , ? , ? , ?) ',
            [
                $request->offset,
                $request->no,
                'id',
                $search,
                'ASC',
                $request->user()->id,
            ]);
        return $orders;
    }
    public function login(Request $request)
    {

        $request->validate([
            'email' => 'required|email|max:255',
            'password' => 'required|max:255',
        ]);
        if (User::where('email', $request->email)->count() == 0) {
            throw ValidationException::withMessages(['email' => 'email_not_found']);
        }
        return $this->loginAction($request);
    }

    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|max:255',
            'fname' => 'required|max:255',
            'lname' => 'required|max:255',
            'phone' => 'required|max:255',
        ]);
        $email = $request->email;
        $password = $request->password;
        $user = DB::insert('call  setUser(?,?,?,?,?,?) ',
            [
                $request->fname,
                $request->lname,
                $request->email,
                $request->phone,
                $request->land_line,
                bcrypt($request->password),
            ]);
        if (!$user) {
            return response()->json(['success' => 'false', 'message' => 'registration_faild']);
        }

        $login = $this->loginAction(['email' => $email, 'password' => $password]);
        return $login;

    }

    protected function loginAction($request)
    {
        $passwordGrantClient = Client::find(env('PASSPORT_CLIENT_ID', 2));

        $data = [
            'grant_type' => 'password',
            'client_id' => $passwordGrantClient->id,
            'client_secret' => $passwordGrantClient->secret,
            'username' => $request['email'],
            'password' => $request['password'],
            'scope' => '*',
        ];

        $tokenRequest = Request::create('oauth/token', 'post', $data);
        $response = app()->handle($tokenRequest);
        if ($response->getStatusCode() >= 400) {
            throw ValidationException::withMessages(['password' => 'password_not_correct']);
        }

        return $response;
    }
    public function updateUser(Request $request)
    {
        $user = $request->user();
        $validate = $request->validate([
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'required|max:255',
            'fname' => 'required|max:255',
            'password' => 'nullable|min:6',
            'lname' => 'required|max:255',
            'land_line' => 'nullable|max:255',
        ]);
        $password = isset($request->password) ? bcrypt($request->password) : bcrypt($request->user()->password);
        // dd($request->fname);
        $user = DB::update('call updateUser(?,?,?,?,?,?,?)', [
            $request->user()->id,
            $request->fname,
            $request->lname,
            $request->email,
            $request->phone,
            $request->land_line || "",
            $password,
        ]);
        return response()->json(['success' => 'true', 'message' => 'user data updated successfully']);
    }
    public function addAddress(Request $request)
    {
        // return $request->user();
        $validatedData = $request->validate([
            'state_id' => 'required',
            'title_address' => 'nullable|max:255',
            'block' => 'required|max:255',
            'building' => 'required|max:255',
            'postal' => 'nullable|max:255',
            'avenue' => 'required|max:255',
            'floor' => 'required|max:255',
            'apartment' => 'required|max:255',
            'phone' => 'nullable|max:255',
        ]);

        $validatedData['user_id'] = $request->user()->id;
        $this->attachAddress($validatedData);
        return json_encode(["success" => true, "message" => 'address attached successfully to the user']);

    }

    public function getUser(Request $request)
    {
        $user = DB::select('call  getUser(?) ',
            [
                $request->user()->id,
            ]);
        // dd($user[0]);
        return response()->json($user[0]);
    }

    protected function attachAddress($request)
    {
        Address::create($request);
    }
    public function getUserAddresses(Request $request)
    {
        //offset , limit , sortby , search ,  sort func , category , author , language , age
        $addresses = DB::select('call getUserAdresses(?) ',
            [
                $request->user()->id,
            ]
        );
        return response()->json($addresses);
    }

    public function updateAddress(Request $request, $id)
    {
        Address::find($id)->update($request->all());
        return json_encode(["success" => true, "message" => 'address updated successfully']);
    }

    public function deleteAddress($id)
    {
        Address::destroy($id);
        return json_encode(["success" => true, "message" => 'address deleted successfully']);
    }
}
