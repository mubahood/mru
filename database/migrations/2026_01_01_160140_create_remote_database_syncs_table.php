<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemoteDatabaseSyncsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('remote_database_syncs', function (Blueprint $table) {
            $table->id();
            $table->string('table_name')->index();
            $table->timestamp('last_synced_at')->nullable();
            $table->unsignedBigInteger('start_id')->default(0);
            $table->unsignedInteger('range_limit')->default(1000)->comment('Number of records to sync per batch');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'paused'])->default('pending');
            $table->text('message')->nullable()->comment('Status message or error details');
            $table->longText('remote_data')->nullable()->comment('Sample remote data for reference');
            $table->unsignedInteger('number_of_records_synced')->default(0);
            $table->unsignedInteger('total_records')->nullable()->comment('Total records in remote table');
            $table->unsignedInteger('records_inserted')->default(0);
            $table->unsignedInteger('records_updated')->default(0);
            $table->unsignedInteger('records_skipped')->default(0);
            $table->unsignedInteger('records_failed')->default(0);
            $table->timestamp('sync_started_at')->nullable();
            $table->timestamp('sync_completed_at')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable()->comment('Sync duration in seconds');
            $table->string('triggered_by')->nullable()->comment('User who triggered the sync');
            $table->text('sync_config')->nullable()->comment('JSON config for sync transformation logic');
            $table->timestamps();
            
            // Indexes
            $table->index(['table_name', 'status']);
            $table->index('last_synced_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('remote_database_syncs');
    }
}
