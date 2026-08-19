<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class CreateUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:user 
                            {email? : The email address for the new user} 
                            {name? : The name for the new user} 
                            {--password= : The password for the new user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new user account directly via CLI';

    /**
     * The command aliases.
     *
     * @var array<int, string>
     */
    protected $aliases = ['user:create'];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->components->info('--- Creating Aiffiliate User ---');

        // 1. Get or prompt for Name
        $name = $this->argument('name');
        if (! $name) {
            $name = text(
                label: 'Name',
                placeholder: 'Admin User',
                default: 'Admin',
                required: true,
            );
        }

        // 2. Get or prompt for Email
        $email = $this->argument('email');
        if (! $email) {
            $email = text(
                label: 'Email address',
                placeholder: 'admin@example.com',
                required: true,
                validate: function (string $value) {
                    $validator = Validator::make(['email' => $value], [
                        'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
                    ]);

                    return $validator->fails() ? $validator->errors()->first('email') : null;
                }
            );
        } else {
            $validator = Validator::make(['email' => $email], [
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            ]);

            if ($validator->fails()) {
                $this->components->error($validator->errors()->first('email'));
                return self::FAILURE;
            }
        }

        // 3. Get or prompt for Password
        $passwordInput = $this->option('password');
        if (! $passwordInput) {
            $passwordInput = password(
                label: 'Password',
                placeholder: 'Minimum 8 characters',
                required: true,
                validate: function (string $value) {
                    return strlen($value) < 8 ? 'The password must be at least 8 characters.' : null;
                }
            );
        } elseif (strlen($passwordInput) < 8) {
            $this->components->error('The password must be at least 8 characters.');
            return self::FAILURE;
        }

        // 4. Create User
        $user = new User();
        $user->name = $name;
        $user->email = $email;
        $user->password = Hash::make($passwordInput);
        $user->email_verified_at = now();
        $user->save();

        $this->components->info("✓ User [{$user->email}] created successfully!");

        $this->table(
            ['ID', 'Name', 'Email', 'Verified At'],
            [
                [$user->id, $user->name, $user->email, $user->email_verified_at?->toDateTimeString() ?? 'N/A'],
            ]
        );

        return self::SUCCESS;
    }
}
