<?php
// Option 1: Create a new Laravel migration for indexes
// Run: php artisan make:migration add_sync_performance_indexes

// database/migrations/add_sync_performance_indexes.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for better sync performance
     */
    public function up()
    {
        // Users table indexes
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                // Only add if not exists
                if (!$this->indexExists('users', 'users_school_updated_idx')) {
                    $table->index(['school_id', 'updated_at'], 'users_school_updated_idx');
                }
                if (!$this->indexExists('users', 'users_role_status_idx')) {
                    $table->index(['role', 'status'], 'users_role_status_idx');
                }
                if (!$this->indexExists('users', 'users_email_school_idx')) {
                    $table->index(['email', 'school_id'], 'users_email_school_idx');
                }
            });
        }

        // Courses table indexes
        if (Schema::hasTable('courses')) {
            Schema::table('courses', function (Blueprint $table) {
                if (!$this->indexExists('courses', 'courses_school_updated_idx')) {
                    $table->index(['school_id', 'updated_at'], 'courses_school_updated_idx');
                }
                if (!$this->indexExists('courses', 'courses_status_school_idx')) {
                    $table->index(['status', 'school_id'], 'courses_status_school_idx');
                }
            });
        }

        // Fleet table indexes
        if (Schema::hasTable('fleet')) {
            Schema::table('fleet', function (Blueprint $table) {
                if (!$this->indexExists('fleet', 'fleet_school_updated_idx')) {
                    $table->index(['school_id', 'updated_at'], 'fleet_school_updated_idx');
                }
                if (!$this->indexExists('fleet', 'fleet_status_school_idx')) {
                    $table->index(['status', 'school_id'], 'fleet_status_school_idx');
                }
                if (!$this->indexExists('fleet', 'fleet_instructor_idx')) {
                    $table->index('instructor', 'fleet_instructor_idx');
                }
            });
        }

        // Schedules table indexes
        if (Schema::hasTable('schedules')) {
            Schema::table('schedules', function (Blueprint $table) {
                if (!$this->indexExists('schedules', 'schedules_school_updated_idx')) {
                    $table->index(['school_id', 'updated_at'], 'schedules_school_updated_idx');
                }
                if (!$this->indexExists('schedules', 'schedules_student_date_idx')) {
                    $table->index(['student', 'start'], 'schedules_student_date_idx');
                }
                if (!$this->indexExists('schedules', 'schedules_instructor_date_idx')) {
                    $table->index(['instructor', 'start'], 'schedules_instructor_date_idx');
                }
                if (!$this->indexExists('schedules', 'schedules_status_school_idx')) {
                    $table->index(['status', 'school_id'], 'schedules_status_school_idx');
                }
                if (!$this->indexExists('schedules', 'schedules_date_range_idx')) {
                    $table->index(['start', 'end'], 'schedules_date_range_idx');
                }
            });
        }

        // Invoices table indexes
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                if (!$this->indexExists('invoices', 'invoices_school_updated_idx')) {
                    $table->index(['school_id', 'updated_at'], 'invoices_school_updated_idx');
                }
                if (!$this->indexExists('invoices', 'invoices_student_status_idx')) {
                    $table->index(['student', 'status'], 'invoices_student_status_idx');
                }
                if (!$this->indexExists('invoices', 'invoices_due_date_idx')) {
                    $table->index('due_date', 'invoices_due_date_idx');
                }
            });
        }

        // Payments table indexes
        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                if (!$this->indexExists('payments', 'payments_user_updated_idx')) {
                    $table->index(['userId', 'updated_at'], 'payments_user_updated_idx');
                }
                if (!$this->indexExists('payments', 'payments_invoice_idx')) {
                    $table->index('invoiceId', 'payments_invoice_idx');
                }
                if (!$this->indexExists('payments', 'payments_date_status_idx')) {
                    $table->index(['paymentDate', 'status'], 'payments_date_status_idx');
                }
            });
        }

        // Schools table indexes (if needed)
        if (Schema::hasTable('schools')) {
            Schema::table('schools', function (Blueprint $table) {
                if (!$this->indexExists('schools', 'schools_status_idx')) {
                    $table->index('status', 'schools_status_idx');
                }
                if (!$this->indexExists('schools', 'schools_invitation_code_idx')) {
                    $table->index('invitation_code', 'schools_invitation_code_idx');
                }
            });
        }

        echo "✅ Sync performance indexes added successfully!\n";
    }

    /**
     * Reverse the migrations
     */
    public function down()
    {
        $indexes = [
            'users' => [
                'users_school_updated_idx',
                'users_role_status_idx', 
                'users_email_school_idx'
            ],
            'courses' => [
                'courses_school_updated_idx',
                'courses_status_school_idx'
            ],
            'fleet' => [
                'fleet_school_updated_idx',
                'fleet_status_school_idx',
                'fleet_instructor_idx'
            ],
            'schedules' => [
                'schedules_school_updated_idx',
                'schedules_student_date_idx',
                'schedules_instructor_date_idx',
                'schedules_status_school_idx',
                'schedules_date_range_idx'
            ],
            'invoices' => [
                'invoices_school_updated_idx',
                'invoices_student_status_idx',
                'invoices_due_date_idx'
            ],
            'payments' => [
                'payments_user_updated_idx',
                'payments_invoice_idx',
                'payments_date_status_idx'
            ],
            'schools' => [
                'schools_status_idx',
                'schools_invitation_code_idx'
            ]
        ];

        foreach ($indexes as $table => $tableIndexes) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) use ($tableIndexes) {
                    foreach ($tableIndexes as $index) {
                        if ($this->indexExists($table->getTable(), $index)) {
                            $table->dropIndex($index);
                        }
                    }
                });
            }
        }

        echo "✅ Sync performance indexes removed successfully!\n";
    }

    /**
     * Check if an index exists
     */
    private function indexExists($table, $index)
    {
        try {
            $indexes = Schema::getConnection()
                ->getDoctrineSchemaManager()
                ->listTableIndexes($table);
            
            return array_key_exists($index, $indexes);
        } catch (\Exception $e) {
            return false;
        }
    }
};
