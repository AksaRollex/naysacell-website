<?php

namespace App\Traits;


use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

trait CodeGenerate
{

    public function getCode()
    {
        return DB::transaction(function () {
            $prx = 'INV-NC-' . date('y') . '-' . date('m') . '-';

            do {
                $newNumber = random_int(100000000, 999999999);
                $newCode = $prx . $newNumber;
                $exists = DB::table('code_generate')->where('code', $newCode)->exists();
            } while ($exists);

            DB::table('code_generate')->insert([
                'code' => $newCode,
                'date_generate' => now()->format('Y-m-d'),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return $newCode;
        });
    }
}
