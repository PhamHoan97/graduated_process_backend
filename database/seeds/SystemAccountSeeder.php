<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SystemAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('systems')->insert(
            [
                ['username'=>'Tran Viet Huy', 'email'=>'huytran161297@gmail.com', 'password'=>Hash::make('jessepinkman1'), 'role'=>1],
                ['username'=>'Pham Hoan', 'email'=>'nhatvan023@gmail.com', 'password'=>Hash::make('phamhoan123'), 'role'=>1]
            ]
        );
    }
}
