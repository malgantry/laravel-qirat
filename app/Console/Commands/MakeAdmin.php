<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class MakeAdmin extends Command
{
    /** @var string */
    protected $signature = 'user:make-admin {email : Email of the user} {--name= : Name to use when creating a new user} {--password= : Password to set when creating a new user}';

    /** @var string */
    protected $description = 'Promote an existing user to admin (or create one if missing).';

    public function handle(): int
    {
        $email = trim((string) $this->argument('email'));
        if ($email === '') {
            $this->error('Email is required.');
            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $name = $this->option('name') ?: 'Admin User';
            $plainPassword = (string) ($this->option('password') ?: 'Admin@12345');
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($plainPassword),
                'is_admin' => true,
                'is_active' => true,
            ]);
            $this->info('Created new admin user: ' . $email);
            $this->line('Generated password: ' . $plainPassword);
        } else {
            $user->is_admin = true;
            $user->is_active = true;
            $user->save();
            $this->info('Promoted user to admin: ' . $email);
        }

        return self::SUCCESS;
    }
}
