<?php

namespace PlusInfoLab\Search\FTS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FTSIndexer
{
    /**
     * FTS configuration.
     */
    protected array $config;

    /**
     * Create a new FTS indexer instance.
     */
    public function __construct(array $config = [])
    {
        $this->config = $config ?: config('search.fts', []);
    }

    /**
     * Index a model instance.
     */
    public function index(Model $model): void
    {
        if (! $this->isEnabled() || ! $this->isSQLite()) {
            return;
        }

        $tableName = $this->getFTSTableName($model);
        $fields = $this->getSearchableFields($model);

        if (empty($fields)) {
            return;
        }

        // Create FTS table if not exists
        $this->createFTSTable($tableName, $fields);

        // Prepare data
        $data = ['id' => $model->getKey()];
        foreach ($fields as $field) {
            $data[$field] = $model->$field ?? '';
        }

        // Insert or replace in FTS table
        DB::table($tableName)->updateOrInsert(
            ['id' => $data['id']],
            $data
        );
    }

    /**
     * Remove a model from index.
     */
    public function remove(Model $model): void
    {
        if (! $this->isEnabled() || ! $this->isSQLite()) {
            return;
        }

        $tableName = $this->getFTSTableName($model);

        DB::table($tableName)->where('id', $model->getKey())->delete();
    }

    /**
     * Create FTS table.
     */
    public function createFTSTable(string $tableName, array $fields): void
    {
        $version = $this->config['version'] ?? 'fts5';
        $tokenizer = $this->config['tokenizer'] ?? 'unicode61';

        // Check if table exists
        $exists = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name=?", [$tableName]);

        if (! empty($exists)) {
            return;
        }

        // Create FTS virtual table
        $fieldList = implode(', ', array_merge(['id'], $fields));
        $sql = "CREATE VIRTUAL TABLE {$tableName} USING {$version}({$fieldList}, tokenize='{$tokenizer}')";

        DB::statement($sql);
    }

    /**
     * Search using FTS.
     */
    public function search(Model $model, string $query): array
    {
        if (! $this->isEnabled() || ! $this->isSQLite()) {
            return [];
        }

        $tableName = $this->getFTSTableName($model);

        // Check if FTS table exists
        $exists = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name=?", [$tableName]);

        if (empty($exists)) {
            return [];
        }

        // Perform FTS search
        $results = DB::select(
            "SELECT id, rank FROM {$tableName} WHERE {$tableName} MATCH ? ORDER BY rank",
            [$query]
        );

        return array_column($results, 'id');
    }

    /**
     * Optimize FTS index.
     */
    public function optimize(string $tableName): void
    {
        if (! $this->isEnabled() || ! $this->isSQLite()) {
            return;
        }

        $version = $this->config['version'] ?? 'fts5';

        if ($version === 'fts5') {
            DB::statement("INSERT INTO {$tableName}({$tableName}) VALUES('optimize')");
        }
    }

    /**
     * Get FTS table name for model.
     */
    protected function getFTSTableName(Model $model): string
    {
        $prefix = $this->config['table_prefix'] ?? 'fts_';

        return $prefix . $model->getTable();
    }

    /**
     * Get searchable fields from model.
     */
    protected function getSearchableFields(Model $model): array
    {
        if (method_exists($model, 'getSearchableFields')) {
            return $model->getSearchableFields();
        }

        return [];
    }

    /**
     * Check if FTS is enabled.
     */
    protected function isEnabled(): bool
    {
        return $this->config['enabled'] ?? false;
    }

    /**
     * Check if using SQLite.
     */
    protected function isSQLite(): bool
    {
        return DB::connection()->getDriverName() === 'sqlite';
    }
}
