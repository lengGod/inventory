<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
                'role_label' => $user?->role?->label(),
                'can' => [
                    'manage_master_data' => $user?->canManageMasterData() ?? false,
                    'manage_transactions' => $user?->canOperateTransactions() ?? false,
                    'print_labels' => $user?->canPrintLabels() ?? false,
                    'manage_users' => $user?->canManageUsers() ?? false,
                ],
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'message' => fn () => $request->session()->get('message'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
