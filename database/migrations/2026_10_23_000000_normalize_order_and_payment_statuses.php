<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // SQLite must be able to suspend foreign keys while rebuilding enum columns.
    public $withinTransaction = false;

    public function up(): void
    {
        $this->convertStatuses('orders', ['paied' => 'paid', 'unpaied' => 'unpaid']);
        $this->convertStatuses('payments', ['success' => 'paid', 'failed' => 'unpaid']);
    }

    public function down(): void
    {
        $this->convertStatuses('payments', ['paid' => 'success', 'unpaid' => 'failed']);
        $this->convertStatuses('orders', ['paid' => 'paied', 'unpaid' => 'unpaied']);
    }

    private function convertStatuses(string $tableName, array $mapping): void
    {
        // Accept both vocabularies before converting rows, then restrict the enum.
        Schema::table($tableName, function (Blueprint $table) use ($mapping) {
            $table->enum('status', array_merge(array_keys($mapping), array_values($mapping)))->change();
        });

        foreach ($mapping as $previous => $current) {
            DB::table($tableName)->where('status', $previous)->update(['status' => $current]);
        }

        Schema::table($tableName, function (Blueprint $table) use ($mapping) {
            $table->enum('status', array_values($mapping))->change();
        });
    }
};
