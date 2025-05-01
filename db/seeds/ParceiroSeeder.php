<?php

use Phinx\Seed\AbstractSeed;

class ParceiroSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run()
    {
        $timestamp = date('Y-m-d H:i:s');
        
        $parceiros = [
            [
                'cnpj' => '12345678000190',
                'logomarca' => null,
                'nome_fantasia' => 'Empresa Teste 1',
                'razao_social' => 'Empresa Teste 1 LTDA',
                'status' => 1,
                'url' => 'https://empresa1.com',
                'created_at' => $timestamp,
                'updated_at' => $timestamp
            ],
            [
                'cnpj' => '98765432000110',
                'logomarca' => null,
                'nome_fantasia' => 'Empresa Teste 2',
                'razao_social' => 'Empresa Teste 2 LTDA',
                'status' => 1,
                'url' => 'https://empresa2.com',
                'created_at' => $timestamp,
                'updated_at' => $timestamp
            ],
            [
                'cnpj' => '45678912000134',
                'logomarca' => null,
                'nome_fantasia' => 'Empresa Teste 3',
                'razao_social' => 'Empresa Teste 3 LTDA',
                'status' => 1,
                'url' => 'https://empresa3.com',
                'created_at' => $timestamp,
                'updated_at' => $timestamp
            ]
        ];
        
        $this->table('parceiros')->insert($parceiros)->saveData();
    }
}
