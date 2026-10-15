<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UsersController extends Controller
{
    public function all()
    {
        $users = User::paginate(10); // Adjust the number of users per page as needed

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.add');
    }

    public function store(Request $request)
    {
        // Handle the form submission and user creation logic here
        // You can access the request data using $request->input('field_name')
        // and perform the necessary operations to create a new user

        // Example:
        // $user = new User();
        // $user->name = $request->input('name');
        // $user->email = $request->input('email');
        // $user->password = bcrypt($request->input('password'));
        // $user->save();

    }

    public function edit($id)
    {
        return view('admin.users.edit', compact('id'));
    }

    public function update(Request $request, $id)
    {
        // Handle the update logic here
        // You can access the request data using $request->input('field_name')
        // and perform the necessary update operations on the user with the given $id

        // Example:
        // $user = User::findOrFail($id);
        // $user->name = $request->input('name');
        // $user->email = $request->input('email');
        // $user->save();

    }

    public function delete($id)
    {
        return view('admin.users.delete', compact('id'));
    }
}
