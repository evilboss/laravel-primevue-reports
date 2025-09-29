<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Market;
use Inertia\Inertia;
use Inertia\Response;

class ReportsController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();
        
        // Get markets user has access to
        $markets = Market::active()
            ->when(!$user->isAdmin(), function ($query) use ($user) {
                $query->whereIn('id', $user->getAccessibleMarketIds());
            })
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return Inertia::render('Reports/Index', [
            'markets' => $markets,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->value,
                'is_admin' => $user->isAdmin(),
            ],
        ]);
    }
}
