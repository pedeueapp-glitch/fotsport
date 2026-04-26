<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('pix_key_type')->nullable()->after('pix_key');
        });

        Schema::table('withdrawals', function (Blueprint $table) {
            $table->string('pix_key')->nullable()->after('net_amount');
            $table->string('pix_key_type')->nullable()->after('pix_key');
            $table->string('efi_payout_id')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('pix_key_type');
        });

        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropColumn(['pix_key', 'pix_key_type', 'efi_payout_id']);
        });
    }
};
