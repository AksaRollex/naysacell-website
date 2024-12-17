<?php

namespace Database\Seeders;

use App\Models\CodeOperator;
use Illuminate\Database\Seeder;

class CodeOperatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $operators = [
            // Telkomsel
            ['name_operator' => 'TELKOMSEL', 'codes' => ['0852', '0853', '0811', '0812', '0813', '0821', '0822']],

            // Indosat
            ['name_operator' => 'INDOSAT', 'codes' => ['0857', '0856']],

            // XL
            ['name_operator' => 'XL', 'codes' => ['0817', '0818', '0819', '0877', '0878']],

            // Axis
            ['name_operator' => 'AXIS', 'codes' => [ '0832', '0833', '0838']],

            // Three (3)
            ['name_operator' => 'TRI', 'codes' => ['0896', '0897', '0898', '0899']],

            // Smartfren
            ['name_operator' => 'SMARTFREN', 'codes' => ['0881', '0882', '0883', '0884', '0885', '0886', '0887', '0888', '0889']]
        ];

        foreach ($operators as $operator) {
            foreach ($operator['codes'] as $code) {
                CodeOperator::create([
                    'name_operator' => $operator['name_operator'],
                    'code' => $code
                ]);
            }
        }
    }
}
