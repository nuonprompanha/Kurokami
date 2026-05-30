<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('subscriber')->after('password');
        });

        if (Schema::hasColumn('users', 'is_admin')) {
            DB::table('users')->where('is_admin', true)->update(['role' => 'administrator']);
            DB::table('users')->where('is_admin', false)->update(['role' => 'subscriber']);

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_admin');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('password');
        });

        DB::table('users')->whereIn('role', ['administrator', 'editor'])->update(['is_admin' => true]);
        DB::table('users')->where('role', 'subscriber')->update(['is_admin' => false]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
