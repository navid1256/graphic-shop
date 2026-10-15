<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\Users\UpdateRequest;
use Illuminate\Http\RedirectResponse;
use App\Models\User;
use App\Http\Requests\Admin\Users\StoreRequest;

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

    public function store(StoreRequest $request): RedirectResponse
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
        $validatedData = $request->validated();
        $createdUser = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'mobile' => $validatedData['mobile'],
            'role' => $validatedData['role'],
            'password' => $validatedData['password'] ?? null,
        ]);

        if (! $createdUser) {
            // Handle the case where user creation fails
            return redirect()->back()->with('error', 'Failed to create user.');
        }

        return redirect()->route('admin.users.all')->with('success', 'User created successfully.');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    public function update(UpdateRequest $request, $id)
    {
        $user = User::findOrFail($id);
        $validatedData = $request->validated();
        $updatedUser = $user->update([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'mobile' => $validatedData['mobile'],
            'role' => $validatedData['role'],
            'password' => $validatedData['password'] ?? $user->password,
        ]);

        if (! $updatedUser) {
            // Handle the case where user update fails
            return redirect()->back()->with('error', 'Failed to update user.');
        }

        return redirect()->route('admin.users.all')->with('success', 'User updated successfully.');
    }

    public function delete($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return back()->with('success', 'User deleted successfully.');
    }
}
