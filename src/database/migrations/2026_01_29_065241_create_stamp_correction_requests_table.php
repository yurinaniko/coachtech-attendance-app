<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStampCorrectionRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('stamp_correction_requests', function (Blueprint $table) {
        $table->id();
        $table->foreignId('attendance_id')->constrained()->cascadeOnDelete();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->dateTime('requested_clock_in_at')->nullable();
        $table->dateTime('requested_clock_out_at')->nullable();
        $table->text('requested_note');
        $table->string('status')->default('pending');
        $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stamp_correction_requests');
    }
}
