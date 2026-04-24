<?php

namespace App\Core\Services;

use Illuminate\Support\Facades\DB;
use Throwable;

class TransactionHandler
{
    public function execute(callable $callback)
    {
        try {
            return DB::transaction($callback);
        } catch (Throwable $e) {
            report($e);
            throw $e;
        }
    }

    public function executeSilent(callable $callback)
    {
        try {
            return DB::transaction($callback);
        } catch (Throwable $e) {
            report($e);
            return null;
        }
    }

    public function rollback(callable $callback): void
    {
        DB::beginTransaction();

        try {
            $callback();
            DB::rollBack();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
