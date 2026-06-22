<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // create issue_tag pivot table
    public function up(): void
    {
        Schema::create('issue_tag', function (Blueprint $table) {
            $table->foreignId('issue_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['issue_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issue_tag');
    }
};
