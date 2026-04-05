<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Parent event reference (for auto-generated visit from flight pair)
            $table->foreignId('parent_event_id')->nullable()->after('id')
                ->constrained('events')->cascadeOnDelete();

            // Return flight date (only for type=visit with flight logic)
            $table->dateTime('return_time')->nullable()->after('end_time');

            // Recurring event fields
            $table->string('recurrence_rule')->nullable()->after('tracking_start');
            $table->string('recurrence_day')->nullable()->after('recurrence_rule');
            $table->time('recurrence_time')->nullable()->after('recurrence_day');
            $table->date('recurrence_until')->nullable()->after('recurrence_time');
        });

        // Migrate type enum -> string with new categories
        // SQLite workaround: add new column, copy data, drop old, rename
        Schema::table('events', function (Blueprint $table) {
            $table->string('type_new')->default('visit')->after('type');
        });

        DB::table('events')->where('type', 'flight')->update(['type_new' => 'visit']);
        DB::table('events')->where('type', 'visit')->update(['type_new' => 'visit']);
        DB::table('events')->where('type', 'date')->update(['type_new' => 'live_date']);

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->renameColumn('type_new', 'type');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('type_old')->default('visit')->after('type');
        });

        DB::table('events')->where('type', 'visit')->update(['type_old' => 'visit']);
        DB::table('events')->where('type', 'virtual_date')->update(['type_old' => 'date']);
        DB::table('events')->where('type', 'live_date')->update(['type_old' => 'date']);
        DB::table('events')->where('type', 'anniversary')->update(['type_old' => 'date']);

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->renameColumn('type_old', 'type');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'parent_event_id',
                'return_time',
                'recurrence_rule',
                'recurrence_day',
                'recurrence_time',
                'recurrence_until',
            ]);
        });
    }
};
