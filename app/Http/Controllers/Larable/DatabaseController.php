<?php

namespace App\Http\Controllers\Larable;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Database Management Controller
 *
 * Provides database introspection for the Blade GUI:
 * - Table listing with column details
 * - Foreign key relationships
 * - Relational schema data for ER diagram
 * - Live table data with pagination
 */
class DatabaseController extends Controller
{
    /**
     * List all tables with their column information.
     */
    public function tables(): JsonResponse
    {
        $tables = $this->getTableNames();
        $result = [];

        foreach ($tables as $tableName) {
            $columns = $this->getTableColumns($tableName);
            $foreignKeys = $this->getTableForeignKeys($tableName);
            $indexes = $this->getTableIndexes($tableName);
            $rowCount = DB::table($tableName)->count();

            $result[] = [
                'name' => $tableName,
                'columns' => $columns,
                'foreign_keys' => $foreignKeys,
                'indexes' => $indexes,
                'row_count' => $rowCount,
            ];
        }

        return response()->json($result);
    }

    /**
     * Get paginated data from a specific table.
     */
    public function tableData(Request $request, string $name): JsonResponse
    {
        if (! Schema::hasTable($name)) {
            return response()->json(['error' => "Table '{$name}' does not exist."], 404);
        }

        $perPage = min((int) $request->input('per_page', 10), 100);
        $page = max((int) $request->input('page', 1), 1);
        $orderBy = $request->input('order_by', 'id');
        $orderDir = $request->input('order_dir', 'asc');

        // Validate order_by column exists
        if (! Schema::hasColumn($name, $orderBy)) {
            $columns = Schema::getColumnListing($name);
            $orderBy = $columns[0] ?? 'id';
        }

        $query = DB::table($name)->orderBy($orderBy, $orderDir);
        $total = DB::table($name)->count();
        $data = $query->offset(($page - 1) * $perPage)->limit($perPage)->get();

        $columns = $this->getTableColumns($name);
        $foreignKeys = $this->getTableForeignKeys($name);

        return response()->json([
            'table' => $name,
            'columns' => $columns,
            'foreign_keys' => $foreignKeys,
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int) ceil($total / $perPage),
            ],
        ]);
    }

    /**
     * Get full relational schema for ER diagram.
     */
    public function schema(): JsonResponse
    {
        $tables = $this->getTableNames();
        $nodes = [];
        $edges = [];

        foreach ($tables as $tableName) {
            $columns = $this->getTableColumns($tableName);
            $foreignKeys = $this->getTableForeignKeys($tableName);

            $nodes[] = [
                'id' => $tableName,
                'label' => $tableName,
                'columns' => $columns,
            ];

            foreach ($foreignKeys as $fk) {
                $edges[] = [
                    'from' => $tableName,
                    'from_column' => $fk['column'],
                    'to' => $fk['foreign_table'],
                    'to_column' => $fk['foreign_column'],
                    'constraint_name' => $fk['name'],
                ];
            }
        }

        return response()->json([
            'nodes' => $nodes,
            'edges' => $edges,
        ]);
    }

    /**
     * Get all table names from the database.
     */
    protected function getTableNames(): array
    {
        return collect(Schema::getTables())
            ->pluck('name')
            ->sort()
            ->values()
            ->toArray();
    }

    /**
     * Get column details for a table.
     */
    protected function getTableColumns(string $table): array
    {
        return collect(Schema::getColumns($table))
            ->map(fn ($col) => [
                'name' => $col['name'],
                'type' => $col['type'],
                'type_name' => $col['type_name'],
                'nullable' => $col['nullable'],
                'default' => $col['default'],
                'auto_increment' => $col['auto_increment'] ?? false,
            ])
            ->toArray();
    }

    /**
     * Get foreign key constraints for a table.
     */
    protected function getTableForeignKeys(string $table): array
    {
        return collect(Schema::getForeignKeys($table))
            ->map(fn ($fk) => [
                'name' => $fk['name'],
                'column' => $fk['columns'][0] ?? null,
                'columns' => $fk['columns'],
                'foreign_table' => $fk['foreign_table'],
                'foreign_column' => $fk['foreign_columns'][0] ?? null,
                'foreign_columns' => $fk['foreign_columns'],
                'on_update' => $fk['on_update'] ?? null,
                'on_delete' => $fk['on_delete'] ?? null,
            ])
            ->toArray();
    }

    /**
     * Get indexes for a table.
     */
    protected function getTableIndexes(string $table): array
    {
        return collect(Schema::getIndexes($table))
            ->map(fn ($idx) => [
                'name' => $idx['name'],
                'columns' => $idx['columns'],
                'type' => $idx['type'] ?? null,
                'unique' => $idx['unique'],
                'primary' => $idx['primary'] ?? false,
            ])
            ->toArray();
    }

    /**
     * Execute a raw SQL query.
     */
    public function executeQuery(Request $request): JsonResponse
    {
        $query = trim($request->input('query', ''));

        if (empty($query)) {
            return response()->json(['error' => 'Query cannot be empty.'], 422);
        }

        $startTime = microtime(true);
        $isRead = preg_match('/^\s*(select|with|show|describe|explain|pragma)/i', $query);

        try {
            if ($isRead) {
                $results = DB::select($query);
                $duration = round((microtime(true) - $startTime) * 1000, 2);

                $data = array_map(fn($row) => (array) $row, $results);
                $columns = count($data) > 0 ? array_keys($data[0]) : [];

                return response()->json([
                    'type' => 'select',
                    'columns' => $columns,
                    'data' => $data,
                    'affected_rows' => count($data),
                    'duration_ms' => $duration,
                ]);
            } else {
                $isMutating = preg_match('/^\s*(insert|update|delete)/i', $query);
                if ($isMutating) {
                    $affected = DB::affectingStatement($query);
                } else {
                    DB::statement($query);
                    $affected = 0;
                }
                $duration = round((microtime(true) - $startTime) * 1000, 2);

                return response()->json([
                    'type' => 'statement',
                    'affected_rows' => $affected,
                    'duration_ms' => $duration,
                ]);
            }
        } catch (\Throwable $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            return response()->json([
                'error' => $e->getMessage(),
                'duration_ms' => $duration,
            ], 400);
        }
    }
}
