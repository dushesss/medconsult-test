<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_actions', function (Blueprint $table): void {
            $table->string('level', 16)->default('audit')->after('action');
            $table->index(['level', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('user_actions', function (Blueprint $table): void {
            $table->dropIndex(['level', 'created_at']);
            $table->dropColumn('level');
        });
    }
};
