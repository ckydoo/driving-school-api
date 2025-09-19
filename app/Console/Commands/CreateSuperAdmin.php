<?php
// app/Console/Commands/CreateSuperAdmin.php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'admin:create-super
                            {--email= : Email address for the super admin}
                            {--password= : Password for the super admin}
                            {--fname= : First name}
                            {--lname= : Last name}';

    /**
     * The console command description.
     */
    protected $description = 'Create a new super administrator user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating Super Administrator...');

        // Get inputs
        $email = $this->option('email') ?: $this->ask('Email address');
        $password = $this->option('password') ?: $this->secret('Password');
        $fname = $this->option('fname') ?: $this->ask('First name');
        $lname = $this->option('lname') ?: $this->ask('Last name');

        // Validate inputs
        $validator = Validator::make([
            'email' => $email,
            'password' => $password,
            'fname' => $fname,
            'lname' => $lname,
        ], [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return 1;
        }

        // Check if super admin already exists
        $existingSuperAdmin = User::superAdmins()->first();
        if ($existingSuperAdmin) {
            $this->warn('A super administrator already exists: ' . $existingSuperAdmin->email);
            if (!$this->confirm('Do you want to create another super admin?')) {
                return 0;
            }
        }

        // Create super admin
        try {
            $user = User::create([
                'fname' => $fname,
                'lname' => $lname,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'super_admin',
                'is_super_admin' => true,
                'status' => 'active',
                'date_of_birth' => now()->subYears(30)->format('Y-m-d'), // Default
                'gender' => 'other', // Default
                'school_id' => null, // Super admins don't belong to a specific school
            ]);

            $this->info('✅ Super Administrator created successfully!');
            $this->table(['Field', 'Value'], [
                ['ID', $user->id],
                ['Name', $user->full_name],
                ['Email', $user->email],
                ['Role', $user->role_display],
                ['Status', $user->status],
                ['Created', $user->created_at->format('Y-m-d H:i:s')],
            ]);

            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to create super administrator: ' . $e->getMessage());
            return 1;
        }
    }
}

// app/Console/Commands/PromoteToSuperAdmin.php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class PromoteToSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'admin:promote-super {email : Email of user to promote}';

    /**
     * The console command description.
     */
    protected $description = 'Promote an existing user to super administrator';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        // Find user
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email '{$email}' not found.");
            return 1;
        }

        // Check if already super admin
        if ($user->isSuperAdmin()) {
            $this->warn("User '{$email}' is already a super administrator.");
            return 0;
        }

        // Show user details and confirm
        $this->info("User to promote:");
        $this->table(['Field', 'Value'], [
            ['ID', $user->id],
            ['Name', $user->full_name],
            ['Email', $user->email],
            ['Current Role', $user->role_display],
            ['School', $user->school?->name ?? 'None'],
            ['Status', $user->status],
        ]);

        if (!$this->confirm("Promote this user to Super Administrator?")) {
            $this->info('Operation cancelled.');
            return 0;
        }

        // Promote user
        try {
            $user->update([
                'role' => 'super_admin',
                'is_super_admin' => true,
                'school_id' => null, // Remove school association
            ]);

            $this->info('✅ User promoted to Super Administrator successfully!');
            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to promote user: ' . $e->getMessage());
            return 1;
        }
    }
}
