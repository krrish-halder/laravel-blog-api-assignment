<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->string('password')->nullable()->change();
        });

        Schema::table('blogs', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
            $table->string('title')->nullable()->change();
            $table->text('content')->nullable()->change();
        });

        Schema::table('likes', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->unsignedBigInteger('likeable_id')->nullable()->change();
            $table->string('likeable_type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::table('users_blogs_likes_tables', function (Blueprint $table) {
        //     //
        // });
    }
};
