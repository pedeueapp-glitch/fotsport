<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class PromoteAdminCommand extends Command
{
    protected $signature = 'app:promote-admin {email}';
    protected $description = 'Promove um usuário para o status de SuperAdmin';

    public function handle()
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error('Usuário não encontrado.');
            return;
        }

        $user->update([
            'is_superadmin' => true,
            'is_active' => true
        ]);

        $this->info("Usuário {$user->name} promovido a SuperAdmin com sucesso!");
    }
}
