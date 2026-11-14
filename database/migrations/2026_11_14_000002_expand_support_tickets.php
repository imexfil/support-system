<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->string('status')->default('open');
            $table->string('department')->default('technical');
            $table->string('priority')->default('normal');
        });
        Schema::table('support_messages', function (Blueprint $table) {
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('author_role')->default('Customer');
            $table->string('author_name')->nullable();
        });
        DB::table('support_tickets')->whereNotNull('closed_at')->update(['status' => 'closed']);
        DB::table('support_messages')->where('from_support', true)->update(['author_role' => 'Support']);
    }

    public function down(): void
    {
        Schema::table('support_messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('author_id');
            $table->dropColumn(['author_role', 'author_name']);
        });
        Schema::table('support_tickets', fn (Blueprint $table) => $table->dropColumn(['status', 'department', 'priority']));
    }
};
