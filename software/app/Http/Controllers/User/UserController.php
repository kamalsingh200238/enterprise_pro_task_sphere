<?php

namespace App\Http\Controllers\User;

use App\Enums\FlashMessageType;
use App\Enums\FlashMessageVariant;
use App\Helpers\FlashMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Show all users
     */
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->input('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(20);

        return Inertia::render('admin/ShowAllUsers', [
            'users' => $users,
            'search' => $request->input('search', '')
        ]);
    }

    /**
     * Show the form to create new user
     */
    public function create()
    {
        return Inertia::render('admin/CreateUser');
    }

    /**
     * Store a newly created user in database.
     */
    public function store(StoreUserRequest $request)
    {
        // get the validated request data
        $validated = $request->validated();

        // create a new user
        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $validated['role']
        ]);

        return redirect()->route('users.show-all')
            ->with('flash', new FlashMessage(
                'Created User Successfully',
                FlashMessageVariant::Success,
                FlashMessageType::Normal,
            )->toArray());
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        return Inertia::render('admin/ShowUser', [
            'user' => $user
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    // public function update(Request $request, User $user)
    // {
    //     $validated = $request->validate([
    //         'name' => 'required|string|max:255',
    //         'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
    //         'password' => 'sometimes|string|min:8|confirmed',
    //         'is_admin' => 'sometimes|boolean',
    //     ]);

    //     $user->name = $validated['name'];
    //     $user->email = $validated['email'];

    //     if ($request->filled('password')) {
    //         $user->password = Hash::make($validated['password']);
    //     }

    //     $user->is_admin = $request->filled('is_admin');
    //     $user->save();

    //     return redirect()->route('users.show');
    // }

    /**
     * Soft delete the specified user.
     */
    // public function destroy(User $user)
    // {
    //     // Prevent self-deletion
    //     if ($user->id === auth()->id()) {
    //         return back()->with('error', 'You cannot delete yourself');
    //     }

    //     $user->delete();

    //     return redirect()->route('admin.users.index')
    //         ->with('message', 'User deleted successfully');
    // }

    /**
     * Restore the soft-deleted user.
     */
    // public function restore($id)
    // {
    //     // We need to use $id instead of route binding because the user is soft deleted
    //     $user = User::onlyTrashed()->findOrFail($id);
    //     $user->restore();

    //     return redirect()->route('admin.users.index')
    //         ->with('message', 'User restored successfully');
    // }

    /**
     * Permanently delete the user.
     */
    // public function forceDelete($id)
    // {
    //     // We need to use $id instead of route binding because the user is soft deleted
    //     $user = User::withTrashed()->findOrFail($id);

    //     // Prevent self-force-deletion
    //     if ($user->id === auth()->id()) {
    //         return back()->with('error', 'You cannot delete yourself');
    //     }

    //     $user->forceDelete();

    //     return redirect()->route('admin.users.index')
    //         ->with('message', 'User permanently deleted');
    // }

}
