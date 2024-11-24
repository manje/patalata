<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use phpseclib3\Crypt\RSA;

return new class extends Migration
{


    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('public_key')->nullable();
            $table->text('private_key')->nullable();
        });
        echo "get users\n";
        $users = DB::table('users')->get();
        echo "Hay " . count($users) . " usuarios\n";

        foreach ($users as $user) {
 
$keyPair = RSA::createKey(2048); // Tamaño de clave recomendado: 2048 bits

// Obtiene la clave pública en formato PEM
$publicKey = $keyPair->getPublicKey()->toString('PKCS8');

// Obtiene la clave privada en formato PEM
$privateKey = $keyPair->toString('PKCS8');



            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'public_key' => $publicKey,
                    'private_key' => $privateKey,
                ]);
        }


    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['public_key', 'private_key']);
        });
    }


};
