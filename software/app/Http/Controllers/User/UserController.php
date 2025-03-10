<?php

namespace App\Http\Controllers\User;

use App\Enums\FlashMessageType;
use App\Enums\FlashMessageVariant;
use App\Helpers\FlashMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\EditUserRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Models\User;
use Gate;
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
        Gate::authorize('viewAll', User::class);

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
        Gate::authorize('create', User::class);
        return Inertia::render('admin/CreateUser');
    }

    /**
     * Store a newly created user in database.
     */
    public function store(StoreUserRequest $request)
    {
        Gate::authorize('create', User::class);
        // get the validated request data
        $validated = $request->validated();

        // create a new user
        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $validated['role']
        ]);

        // redirect to show all page with a notification
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
        Gate::authorize('view', User::class);

        return Inertia::render('admin/ShowUser', [
            'user' => $user
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function edit(EditUserRequest $request, User $user)
    {
        Gate::authorize('edit', User::class);

        // valiate user data
        $validated = $request->validated();

        // update user fields
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];

        // only update password if they provide password
        if ($request->filled('password')) {
            $user->password = $validated['password'];
        }
        $user->save();

        return redirect()->route('users.show', $user->id)
            ->with('flash', new FlashMessage(
                'Edited User Successfully',
                FlashMessageVariant::Success,
                FlashMessageType::Normal,
            )->toArray());
    }

    /**
     * Soft delete the specified user.
     */
    public function delete(User $user)
    {
        Gate::authorize('delete', User::class);

        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return back()
                ->with('flash', new FlashMessage(
                    'You cannot delete yourself',
                    FlashMessageVariant::Error,
                    FlashMessageType::Normal,
                )->toArray());
        }

        $deleted = $user->delete();

        if ($deleted) {
            return redirect()->route('users.show-all')
                ->with('flash', new FlashMessage(
                    'User deleted successfully',
                    FlashMessageVariant::Success,
                    FlashMessageType::Normal,
                )->toArray());
        }

        return back()
            ->with('flash', new FlashMessage(
                'Error in deleting the user',
                FlashMessageVariant::Error,
                FlashMessageType::Normal,
            )->toArray());
    }
}
