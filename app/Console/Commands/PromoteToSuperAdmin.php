<?php

// app/Console/Commands/ListSuperAdmins.php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ListSuperAdmins extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'admin:list-super';

    /**
     * The console command description.
     */
    protected $description = 'List all super administrators';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $superAdmins = User::superAdmins()->get();

        if ($superAdmins->isEmpty()) {
            $this->warn('No super administrators found.');
            $this->info('Create one using: php artisan admin:create-super');
            return 0;
        }

        $this->info('Super Administrators:');
        $this->table(
            ['ID', 'Name', 'Email', 'Status', 'Created'],
            $superAdmins->map(function ($user) {
                return [
                    $user->id,
                    $user->full_name,
                    $user->email,
                    $user->status,
                    $user->created_at->format('Y-m-d H:i:s'),
                ];
            })->toArray()
        );

        return 0;
    }
}
