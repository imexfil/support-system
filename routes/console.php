<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

Artisan::command('desk:admin', function () {
    $name = $this->ask('Name');
    $email = $this->ask('Email');
    $password = $this->secret('Password (at least 12 characters)');
    $confirmation = $this->secret('Confirm password');
    $input = validator(compact('name', 'email', 'password'), [
        'name' => 'required|string|max:100',
        'email' => 'required|email|max:255|unique:users',
        'password' => 'required|string|min:12',
    ]);
    if ($input->fails() || $password !== $confirmation) {
        $this->error($input->errors()->first() ?: 'Passwords do not match.');

        return 1;
    }

    $user = new User(['name' => $name, 'email' => $email, 'password' => Hash::make($password)]);
    $user->role = 'admin';
    $user->save();
    $this->info('Administrator created.');
});
