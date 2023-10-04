<?php

namespace Database\Seeders;

use App\Models\Lead;
use App\Models\Client;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class LeadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Creamos 10 leads aleatorios para pruebas
        Lead::factory(10)->create()->each(function ($lead) {
            // Para cada lead, creamos un client con el mismo email y asociamos el ID del lead.
            Client::create([
                'email' => $lead->email,
                'lead_id' => $lead->id
            ]);
        });
    }
}
