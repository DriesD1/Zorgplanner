<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        Schema::table('houses', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->after('house_id')->constrained()->nullOnDelete();
        });

        Schema::table('visits', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->after('client_id')->constrained()->nullOnDelete();
        });

        Schema::table('fiche_definitions', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
        });

        Schema::table('communication_entries', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->after('house_id')->constrained()->nullOnDelete();
        });

        // Backfill: one user = one organization
        $now = now();
        $users = DB::table('users')->get(['id', 'name', 'email']);

        foreach ($users as $user) {
            $orgId = DB::table('organizations')->insertGetId([
                'name' => $user->name ?? $user->email ?? 'Organisatie '.$user->id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('users')->where('id', $user->id)->update(['organization_id' => $orgId]);

            DB::table('houses')->where('user_id', $user->id)->update(['organization_id' => $orgId]);
            DB::table('fiche_definitions')->where('user_id', $user->id)->update(['organization_id' => $orgId]);
        }

        // Sync org to clients via house
        DB::table('clients as c')
            ->join('houses as h', 'c.house_id', '=', 'h.id')
            ->update(['c.organization_id' => DB::raw('h.organization_id')]);

        // Sync org to visits via client
        DB::table('visits as v')
            ->join('clients as c', 'v.client_id', '=', 'c.id')
            ->update(['v.organization_id' => DB::raw('c.organization_id')]);

        // Sync org to communication entries via house
        DB::table('communication_entries as e')
            ->join('houses as h', 'e.house_id', '=', 'h.id')
            ->update(['e.organization_id' => DB::raw('h.organization_id')]);
    }

    public function down(): void
    {
        Schema::table('communication_entries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('organization_id');
        });

        Schema::table('visits', function (Blueprint $table) {
            $table->dropConstrainedForeignId('organization_id');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropConstrainedForeignId('organization_id');
        });

        Schema::table('houses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('organization_id');
        });

        Schema::table('fiche_definitions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('organization_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('organization_id');
        });

        Schema::dropIfExists('organizations');
    }
};
