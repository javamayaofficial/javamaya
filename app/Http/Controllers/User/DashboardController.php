<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\MemberAccess;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $access = MemberAccess::with('product.classRoom')
            ->where('user_id', $user->id)->whereNull('revoked_at')->latest()->get();

        $classes = $access->pluck('product.classRoom')->filter()->unique('id')->values()
            ->map(fn (ClassRoom $c) => ['class' => $c, 'progress' => $c->progressFor($user)]);

        return view('user.dashboard', [
            'user' => $user,
            'access' => $access,
            'classes' => $classes,
            'orders' => $user->orders()->latest()->limit(5)->get(),
        ]);
    }
}
