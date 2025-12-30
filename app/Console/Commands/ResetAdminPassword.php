<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetAdminPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:reset-password {email?} {--password=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset password for an admin user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email') ?? $this->ask('Enter user email');
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User with email '{$email}' not found.");
            return 1;
        }
        
        $password = $this->option('password') ?? $this->secret('Enter new password');
        
        if (empty($password)) {
            $this->error('Password cannot be empty.');
            return 1;
        }
        
        // Since the model has 'password' => 'hashed' in casts, we can set it directly
        // Laravel will automatically hash it
        $user->password = $password;
        $user->save();
        
        $this->info("Password reset successfully for user: {$user->email}");
        $this->info("You can now login with this password.");
        
        return 0;
    }
}
