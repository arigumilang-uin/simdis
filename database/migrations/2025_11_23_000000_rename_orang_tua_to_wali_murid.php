<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::transaction(function () {
            $roleOrtu = DB::table('roles')->where('nama_role', 'Orang Tua')->first();
            $roleWali = DB::table('roles')->where('nama_role', 'Wali Murid')->first();

            if (!$roleOrtu) {
                // Nothing to do
                return;
            }

            if (!$roleWali) {
                // Safe rename: update the existing row
                DB::table('roles')->where('id', $roleOrtu->id)->update(['nama_role' => 'Wali Murid']);
            } else {
                // A 'Wali Murid' role already exists: transfer users and delete old role
                DB::table('users')->where('role_id', $roleOrtu->id)->update(['role_id' => $roleWali->id]);
                DB::table('roles')->where('id', $roleOrtu->id)->delete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::transaction(function () {
            $roleWali = DB::table('roles')->where('nama_role', 'Wali Murid')->first();
            $roleOrtu = DB::table('roles')->where('nama_role', 'Orang Tua')->first();

            if (!$roleWali) {
                return;
            }

            // If both exist, we assume previous migration created Wali Murid while Orang Tua also exists
            if ($roleOrtu) {
                // Prefer to move users that currently point to Wali Murid (created earlier) back to Orang Tua
                DB::table('users')->where('role_id', $roleWali->id)->update(['role_id' => $roleOrtu->id]);
                // Optionally remove 'Wali Murid' role if it was created by this migration
                // But to be safe, do not delete it automatically. We'll only rename back when unique.
            } else {
                // Rename Wali Murid back to Orang Tua
                DB::table('roles')->where('id', $roleWali->id)->update(['nama_role' => 'Orang Tua']);
            }
        });
    }
};
