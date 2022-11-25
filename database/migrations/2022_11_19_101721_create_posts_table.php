<?php

use App\Models\Instance;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('public_id');
            $table->foreignIdFor(User::class)->constrained();
            $table->foreignIdFor(Instance::class)->nullable()->constrained();
            $table->unsignedBigInteger('reply_to_post_id')->nullable();
            $table->foreign('reply_to_post_id')->references('id')->on('posts');
            $table->string('body', 500);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts');
    }
}
