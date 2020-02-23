<?php

namespace App\Providers;

use App\Domain\Admin\Config\PermissionEnum;
use App\Domain\Admin\Models\Admin;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        foreach(PermissionEnum::getConstants() as $permissionName) {
            Gate::define($permissionName, function ($user) use ($permissionName) {
                return ($user instanceof Admin) && $user->hasPermission($permissionName);
            });
        }
    }
}
