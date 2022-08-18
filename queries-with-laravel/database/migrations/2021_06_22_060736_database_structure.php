<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class DatabaseStructure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared(file_get_contents(database_path('dumps/database_structure.sql')));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach (
            DB::select(
                'select tablename from pg_catalog.pg_tables where schemaname != ? and  schemaname != ? and tablename != ?',
                ['pg_catalog', 'information_schema', 'migrations']
            ) as $table
        ) {
            DB::statement('drop table if exists ' . $table->tablename . ' cascade');
        }
        foreach (DB::select('select relname from pg_class where relkind = ? and relname != ?', ['S', 'migrations_id_seq']) as $sequence) {
            DB::statement('drop sequence if exists ' . $sequence->relname . ' cascade');
        }
    }
}
