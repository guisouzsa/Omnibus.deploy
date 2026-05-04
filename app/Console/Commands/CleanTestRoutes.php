<?php

namespace App\Console\Commands;

use App\Models\Route;
use Illuminate\Console\Command;

class CleanTestRoutes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-test-routes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove rotas de teste, deixando apenas as rotas reais';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $testRoutes = [
            'Centro → Zona Norte',
            'Aeroporto → Rodoviária',
            'Hospital → Zona Sul',
            'Estação → Bairro Central',
            'Terminal → Zona Leste',
        ];

        foreach ($testRoutes as $name) {
            $deleted = Route::where('name', $name)->delete();
            if ($deleted > 0) {
                $this->info("✓ Deletada rota: $name");
            }
        }

        $remaining = Route::where('user_id', 1)->count();
        $this->info("\n✓ Limpeza concluída! Rotas restantes: {$remaining}");
    }
}

