<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $user = new User();
        $user->name = 'ADMIN2';
        $user->email = 'usuario@example.com';
        $user->cedula = '000000';
        $user->status = '1/3';// se debe cargar con ese valor ya que cuando ingresen por primera ves ese estado los guiara a cambiar la contraseña
        $user->role = '1'; // se necesita por cuestion de los permisos de rol y el rol 1 es administrativo
        $user->password = Hash::make('Prueba@123456'); // el angular esta configurado para que solo reciba con al menos un caracter especial 
        $user->save();
    }
}
