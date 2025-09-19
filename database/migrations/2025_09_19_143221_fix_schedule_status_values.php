<?php
// Create a new migration file: php artisan make:migration fix_schedule_status_values

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update any null or empty status values to 'scheduled'
        DB::table('schedules')
            ->whereNull('status')
            ->orWhere('status', '')
            ->update(['status' => 'scheduled']);

        // Update status based on logic:
        // If attended = 1, set status to 'completed'
        DB::table('schedules')
            ->where('attended', 1)
            ->where('status', '!=', 'completed')
            ->update(['status' => 'completed']);

        // Set missed status for past lessons that weren't attended
        DB::table('schedules')
            ->where('end', '<', now())
            ->where('attended', 0)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->update(['status' => 'missed']);

        // Ensure we have proper status values
        DB::statement("
            UPDATE schedules
            SET status = 'scheduled'
            WHERE status NOT IN ('scheduled', 'completed', 'cancelled', 'missed', 'in_progress')
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optional: revert changes if needed
        // DB::table('schedules')->update(['status' => null]);
    }
};
