<?php

namespace Database\Seeders;

use App\Models\Institution;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        
        DB::transaction(function(){
            $app_role = Role::create([
                'slug' => 'app-admin',
                'title' => 'App Administrator'
            ]);
            $app_institution = Institution::create([
                'title' => 'Scholastic Cloud'
            ]);
            $app_permission = Permission::create([
                'slug' => 'add-user',
                'title' => 'Can Add User'
            ]);
            $app_user = User::create([
                'first_name' => 'PHILIP LOUIS',
                'middle_name' => 'antepuesto',
                'last_name' => 'Calub',
                'email' => 'philiplouis0717@gmail.com',
                'password' => 'password'
            ]);
            Log::info($app_user->id);
            DB::table('user_institutions')->insert([
                'user_id' => $app_user->id,
                'institution_id' =>  $app_institution->id
            ]);
            DB::table('user_roles')->insert([
                'user_id' => $app_user->id,
                'institution_id' =>  $app_institution->id,
                'role_id' => $app_role->id
            ]);
            DB::table('user_permissions')->insert([
                'user_id' => $app_user->id,
                'institution_id' =>  $app_institution->id,
                'permission_id' => $app_permission->id
            ]);
        });
    }
}
