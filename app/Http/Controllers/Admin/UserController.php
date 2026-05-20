<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserByAdminRequest;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');

        $users = User::query()
            ->withCount('listings')
            ->when($q, function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('name', 'like', "%{$q}%")
                       ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'q'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(UpdateUserByAdminRequest $request, User $user)
    {
        $user->update($request->validated());

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Usuario actualizado.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('status', 'No puedes eliminarte a ti mismo.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Usuario eliminado.');
    }
}
