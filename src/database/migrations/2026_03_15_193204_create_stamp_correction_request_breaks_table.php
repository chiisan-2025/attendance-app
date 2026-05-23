<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStampCorrectionRequestBreaksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stamp_correction_request_breaks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stamp_correction_request_id');
            $table->time('requested_break_start_at');
            $table->time('requested_break_end_at')->nullable();
            $table->timestamps();

            $table->foreign('stamp_correction_request_id', 'scr_breaks_request_id_fk')
                ->references('id')
                ->on('stamp_correction_requests')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stamp_correction_request_breaks');
    }
}
