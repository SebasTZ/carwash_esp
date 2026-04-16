<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::transaction(function (): void {
            $this->call(DocumentoSeeder::class);
            $this->call(ComprobanteSeeder::class);
            $this->call(PermissionSeeder::class);
            $this->call(UserSeeder::class);
            $this->call(FidelizacionSeeder::class);
        });
    }
}
