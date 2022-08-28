<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Notebook;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        /**
         * Check if user is authorized to edit / delete given Notebook entry.
         * Only Notebook creator is authorized to do so.
         *
         * @return bool
         */
        Gate::define('modify-notebook', function (User $user, Notebook $notebook) {
            return $user->id === $notebook->creator_uuid;
        });
    }
}
