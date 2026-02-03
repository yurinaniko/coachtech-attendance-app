<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApprovalLogsTable extends Migration
{
    public function up()
    {
        Schema::create('approval_logs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('stamp_correction_request_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->foreignId('admin_user_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->timestamp('approved_at');
        $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('approval_logs');
    }
}
