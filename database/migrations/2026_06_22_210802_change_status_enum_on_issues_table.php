<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // widen status to the kanban pipeline stages, remapping existing rows
    public function up(): void
    {
        DB::statement("ALTER TABLE issues MODIFY status ENUM('open','in_progress','closed','todo','blocked','qa_staging','qa_done','prod') NOT NULL DEFAULT 'todo'");

        DB::table('issues')->where('status', 'open')->update(['status' => 'todo']);
        DB::table('issues')->where('status', 'closed')->update(['status' => 'prod']);

        DB::statement("ALTER TABLE issues MODIFY status ENUM('todo','in_progress','blocked','qa_staging','qa_done','prod') NOT NULL DEFAULT 'todo'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE issues MODIFY status ENUM('open','in_progress','closed','todo','blocked','qa_staging','qa_done','prod') NOT NULL DEFAULT 'open'");

        DB::table('issues')->where('status', 'todo')->update(['status' => 'open']);
        DB::table('issues')->whereIn('status', ['blocked', 'qa_staging', 'qa_done', 'prod'])->update(['status' => 'closed']);

        DB::statement("ALTER TABLE issues MODIFY status ENUM('open','in_progress','closed') NOT NULL DEFAULT 'open'");
    }
};
