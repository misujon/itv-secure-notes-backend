<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Throwable;

class CreateAdminCommand extends Command
{
    protected $signature = 'app:create-admin
                            {--name= : Admin full name}
                            {--email= : Admin email address}
                            {--password= : Admin password (minimum 8 characters)}';

    protected $description = 'Create the initial admin user account';

    public function handle(UserService $userService): int
    {
        $name = (string) ($this->option('name') ?: $this->ask('Admin name'));
        $email = (string) ($this->option('email') ?: $this->ask('Admin email'));
        $password = (string) ($this->option('password') ?: $this->secret('Admin password (min 8 chars)'));

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        try {
            $admin = $userService->createUser([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role' => User::ROLE_ADMIN,
            ]);
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Admin user created successfully: {$admin->email}");

        return self::SUCCESS;
    }
}
