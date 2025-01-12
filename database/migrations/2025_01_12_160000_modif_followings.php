<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\User;

return new class extends Migration {
    public function up()
    {


        Schema::table('apfollowings', function (Blueprint $table) {
            $table->string('actor', 255)->nullable()->after('actor_id');
            $table->renameColumn('actor_id', 'object');
        });


        $followings = DB::table('apfollowings')
        ->selectRaw('user_id, CONCAT("https://example.com/users/", user_id) as actor')
        ->groupBy('user_id')
        ->get();
        foreach ($followings as $following) {
            $user=User::find($following->user_id);
            $actor=$user->GetActivity()['id'];
            // update apfollowins set actor='https://example.com/users/1' where user_id=1;
            DB::table('apfollowings')
            ->where('user_id', $following->user_id)
            ->update(['actor' => $actor]);

        }

        Schema::table('apfollowings', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('apfollowings', function (Blueprint $table) {
            $table->dropColumn('user_id'); // Eliminar user_id después de migrar los datos
        });
    }

    public function down()
    {
        Schema::table('apfollowings', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('actor_id');
        });

        Schema::table('apfollowings', function (Blueprint $table) {
            $table->dropColumn('actor');
        });
    }
};
