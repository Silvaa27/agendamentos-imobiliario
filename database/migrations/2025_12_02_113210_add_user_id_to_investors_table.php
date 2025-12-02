<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Investor;

return new class extends Migration {
    public function up(): void
    {
        // 1. Adicionar coluna user_id
        Schema::table('investors', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->onDelete('cascade')
                ->index('idx_inv_user');
        });

        // 2. Para cada investor existente, criar um User
        $investors = Investor::all();

        foreach ($investors as $investor) {
            // Verificar se já existe um user com este email
            $user = User::where('email', $investor->email)->first();

            if (!$user) {
                // Criar novo usuário
                $user = User::create([
                    'name' => $investor->name,
                    'email' => $investor->email,
                    'password' => bcrypt('password123'), // Senha temporária
                ]);
            }

            // Atribuir role de investidor
            $investorRole = \Spatie\Permission\Models\Role::where('name', 'investidor')->first();
            if (!$investorRole) {
                $investorRole = \Spatie\Permission\Models\Role::create([
                    'name' => 'investidor',
                    'guard_name' => 'web'
                ]);
            }

            $user->assignRole($investorRole);

            // Atualizar investor com user_id
            $investor->user_id = $user->id;
            $investor->save();
        }
    }

    public function down(): void
    {
        Schema::table('investors', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};