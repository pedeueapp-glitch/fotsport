<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class VerifyUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:verify {email} {--remove : Remover o selo de verificado}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atribui ou remove o selo de verificado de um usuário pelo email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $remove = $this->option('remove');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("Usuário com o email {$email} não encontrado.");
            return Command::FAILURE;
        }

        $status = $remove ? false : true;
        $user->is_verified = $status;
        $user->save();

        $action = $status ? 'verificado' : 'não verificado';
        $this->info("O usuário {$user->name} ({$email}) agora está {$action}.");

        return Command::SUCCESS;
    }
}
