<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DatabaseBackupController extends Controller
{
    /**
     * The backup directory path
     */
    protected string $backupPath = 'backups';

    /**
     * Display a listing of database backups.
     */
    public function index(): View
    {
        $backups = $this->getBackups();
        $dbConnection = config('database.default');
        $dbName = $this->getDatabaseName();
        
        return view('admin.database-backup.index', compact('backups', 'dbConnection', 'dbName'));
    }

    /**
     * Create a new database backup.
     */
    public function create(): RedirectResponse
    {
        try {
            $filename = $this->generateBackup();
            
            return redirect()->route('admin.database-backup.index')
                ->with('success', "Database backup created successfully: {$filename}");
        } catch (\Exception $e) {
            return redirect()->route('admin.database-backup.index')
                ->with('error', 'Failed to create backup: ' . $e->getMessage());
        }
    }

    /**
     * Download a database backup file.
     */
    public function download(string $filename): BinaryFileResponse|RedirectResponse
    {
        $filePath = storage_path("app/{$this->backupPath}/{$filename}");
        
        if (!file_exists($filePath)) {
            return redirect()->route('admin.database-backup.index')
                ->with('error', 'Backup file not found.');
        }

        return response()->download($filePath, $filename);
    }

    /**
     * Delete a database backup file.
     */
    public function destroy(string $filename): RedirectResponse
    {
        $filePath = storage_path("app/{$this->backupPath}/{$filename}");
        
        if (!file_exists($filePath)) {
            return redirect()->route('admin.database-backup.index')
                ->with('error', 'Backup file not found.');
        }

        unlink($filePath);

        return redirect()->route('admin.database-backup.index')
            ->with('success', 'Backup deleted successfully.');
    }

    /**
     * Generate a database backup based on the database driver.
     */
    protected function generateBackup(): string
    {
        $connection = config('database.default');
        $timestamp = now()->format('Y-m-d_H-i-s');
        
        // Ensure backup directory exists
        $backupDir = storage_path("app/{$this->backupPath}");
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        switch ($connection) {
            case 'sqlite':
                return $this->backupSqlite($timestamp);
            case 'mysql':
            case 'mariadb':
            case 'pgsql':
                return $this->backupDatabaseViaPHP($timestamp, $connection);
            default:
                throw new \Exception("Unsupported database driver: {$connection}");
        }
    }

    /**
     * Create a backup for SQLite database.
     */
    protected function backupSqlite(string $timestamp): string
    {
        $dbPath = config('database.connections.sqlite.database');
        
        if (!file_exists($dbPath)) {
            throw new \Exception('SQLite database file not found.');
        }

        $filename = "backup_{$timestamp}.sqlite";
        $backupPath = storage_path("app/{$this->backupPath}/{$filename}");
        
        // Copy the SQLite database file
        copy($dbPath, $backupPath);
        
        return $filename;
    }

    /**
     * Create a backup using PHP (works for MySQL, MariaDB, PostgreSQL).
     * This method doesn't require external tools like mysqldump or pg_dump.
     */
    protected function backupDatabaseViaPHP(string $timestamp, string $driver): string
    {
        $filename = "backup_{$timestamp}.sql";
        $backupPath = storage_path("app/{$this->backupPath}/{$filename}");
        
        $tables = $this->getAllTables($driver);
        
        if (empty($tables)) {
            throw new \Exception('No tables found in the database.');
        }
        
        $sqlContent = $this->generateSqlDump($tables, $driver);
        
        // Write to file
        file_put_contents($backupPath, $sqlContent);
        
        // Compress the SQL file
        $gzFilename = $filename . '.gz';
        $gzPath = storage_path("app/{$this->backupPath}/{$gzFilename}");
        
        $fp = gzopen($gzPath, 'w9');
        gzwrite($fp, file_get_contents($backupPath));
        gzclose($fp);
        
        // Remove the uncompressed SQL file
        unlink($backupPath);
        
        return $gzFilename;
    }

    /**
     * Get all tables from the database.
     */
    protected function getAllTables(string $driver): array
    {
        $tables = [];
        
        switch ($driver) {
            case 'mysql':
            case 'mariadb':
                $result = DB::select('SHOW TABLES');
                foreach ($result as $row) {
                    $tables[] = array_values((array) $row)[0];
                }
                break;
            case 'pgsql':
                $result = DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
                foreach ($result as $row) {
                    $tables[] = $row->tablename;
                }
                break;
        }
        
        return $tables;
    }

    /**
     * Generate SQL dump content for all tables.
     */
    protected function generateSqlDump(array $tables, string $driver): string
    {
        $dbName = $this->getDatabaseName();
        $sql = "-- Database Backup\n";
        $sql .= "-- Generated: " . now()->toDateTimeString() . "\n";
        $sql .= "-- Database: {$dbName}\n";
        $sql .= "-- Driver: {$driver}\n\n";
        
        if ($driver === 'mysql' || $driver === 'mariadb') {
            $sql .= "SET FOREIGN_KEY_CHECKS=0;\n";
            $sql .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
            $sql .= "SET AUTOCOMMIT = 0;\n";
            $sql .= "START TRANSACTION;\n\n";
        }
        
        foreach ($tables as $table) {
            $sql .= $this->getTableDump($table, $driver);
        }
        
        if ($driver === 'mysql' || $driver === 'mariadb') {
            $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
            $sql .= "COMMIT;\n";
        }
        
        return $sql;
    }

    /**
     * Get the SQL dump for a single table (structure + data).
     */
    protected function getTableDump(string $table, string $driver): string
    {
        $sql = "-- --------------------------------------------------------\n";
        $sql .= "-- Table structure for table `{$table}`\n";
        $sql .= "-- --------------------------------------------------------\n\n";
        
        // Drop table if exists
        $sql .= "DROP TABLE IF EXISTS \"{$table}\";\n\n";
        
        // Get create table statement
        $sql .= $this->getCreateTableStatement($table, $driver);
        $sql .= "\n\n";
        
        // Get table data
        $sql .= "-- Dumping data for table `{$table}`\n\n";
        $sql .= $this->getTableData($table, $driver);
        $sql .= "\n";
        
        return $sql;
    }

    /**
     * Get CREATE TABLE statement for a table.
     */
    protected function getCreateTableStatement(string $table, string $driver): string
    {
        switch ($driver) {
            case 'mysql':
            case 'mariadb':
                $result = DB::select("SHOW CREATE TABLE `{$table}`");
                if (!empty($result)) {
                    return $result[0]->{'Create Table'} . ";\n";
                }
                break;
            case 'pgsql':
                // For PostgreSQL, we need to build the CREATE TABLE statement
                return $this->getPostgresCreateTable($table);
        }
        
        return '';
    }

    /**
     * Build CREATE TABLE statement for PostgreSQL.
     */
    protected function getPostgresCreateTable(string $table): string
    {
        $columns = DB::select("
            SELECT column_name, data_type, character_maximum_length, 
                   column_default, is_nullable, udt_name
            FROM information_schema.columns 
            WHERE table_name = ? AND table_schema = 'public'
            ORDER BY ordinal_position
        ", [$table]);
        
        if (empty($columns)) {
            return '';
        }
        
        $sql = "CREATE TABLE \"{$table}\" (\n";
        $columnDefs = [];
        
        foreach ($columns as $column) {
            $def = "    \"{$column->column_name}\" ";
            
            // Handle data type
            if ($column->character_maximum_length) {
                $def .= strtoupper($column->data_type) . "({$column->character_maximum_length})";
            } else {
                $def .= strtoupper($column->udt_name);
            }
            
            // Handle nullable
            if ($column->is_nullable === 'NO') {
                $def .= " NOT NULL";
            }
            
            // Handle default
            if ($column->column_default !== null) {
                $def .= " DEFAULT {$column->column_default}";
            }
            
            $columnDefs[] = $def;
        }
        
        // Get primary key
        $primaryKey = DB::select("
            SELECT a.attname
            FROM pg_index i
            JOIN pg_attribute a ON a.attrelid = i.indrelid AND a.attnum = ANY(i.indkey)
            WHERE i.indrelid = ?::regclass AND i.indisprimary
        ", [$table]);
        
        if (!empty($primaryKey)) {
            $pkColumns = array_map(fn($pk) => "\"{$pk->attname}\"", $primaryKey);
            $columnDefs[] = "    PRIMARY KEY (" . implode(", ", $pkColumns) . ")";
        }
        
        $sql .= implode(",\n", $columnDefs);
        $sql .= "\n);\n";
        
        return $sql;
    }

    /**
     * Get INSERT statements for table data.
     */
    protected function getTableData(string $table, string $driver): string
    {
        $rows = DB::table($table)->get();
        
        if ($rows->isEmpty()) {
            return "-- Table `{$table}` is empty\n";
        }
        
        $sql = '';
        $columns = array_keys((array) $rows->first());
        
        // Quote column names
        $quotedColumns = array_map(function($col) use ($driver) {
            return $driver === 'pgsql' ? "\"{$col}\"" : "`{$col}`";
        }, $columns);
        
        $columnList = implode(', ', $quotedColumns);
        $tableQuote = $driver === 'pgsql' ? "\"{$table}\"" : "`{$table}`";
        
        foreach ($rows as $row) {
            $values = [];
            foreach ($columns as $column) {
                $value = $row->$column;
                
                if ($value === null) {
                    $values[] = 'NULL';
                } elseif (is_numeric($value)) {
                    $values[] = $value;
                } elseif (is_bool($value)) {
                    $values[] = $value ? 'TRUE' : 'FALSE';
                } else {
                    // Escape string value
                    $escaped = str_replace("'", "''", $value);
                    $escaped = str_replace("\\", "\\\\", $escaped);
                    $values[] = "'" . $escaped . "'";
                }
            }
            
            $sql .= "INSERT INTO {$tableQuote} ({$columnList}) VALUES (" . implode(', ', $values) . ");\n";
        }
        
        return $sql;
    }

    /**
     * Get list of existing backups.
     */
    protected function getBackups(): array
    {
        $backupDir = storage_path("app/{$this->backupPath}");
        
        if (!is_dir($backupDir)) {
            return [];
        }

        $files = glob($backupDir . '/*');
        $backups = [];

        foreach ($files as $filePath) {
            // Skip directories
            if (is_dir($filePath)) {
                continue;
            }
            
            $filename = basename($filePath);
            
            // Skip hidden files
            if (str_starts_with($filename, '.')) {
                continue;
            }
            
            // Parse the timestamp from filename (format: backup_YYYY-MM-DD_HH-MM-SS.ext)
            preg_match('/backup_(\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2})/', $filename, $matches);
            
            $createdAt = null;
            if (!empty($matches[1])) {
                $createdAt = \Carbon\Carbon::createFromFormat('Y-m-d_H-i-s', $matches[1]);
            }
            
            $backups[] = [
                'filename' => $filename,
                'size' => $this->formatFileSize(filesize($filePath)),
                'size_bytes' => filesize($filePath),
                'created_at' => $createdAt,
                'type' => $this->getBackupType($filename),
            ];
        }

        // Sort by created_at descending (newest first)
        usort($backups, function ($a, $b) {
            if ($a['created_at'] === null) return 1;
            if ($b['created_at'] === null) return -1;
            return $b['created_at']->timestamp - $a['created_at']->timestamp;
        });

        return $backups;
    }

    /**
     * Get the database name for display.
     */
    protected function getDatabaseName(): string
    {
        $connection = config('database.default');
        
        if ($connection === 'sqlite') {
            return basename(config('database.connections.sqlite.database'));
        }
        
        return config("database.connections.{$connection}.database", 'Unknown');
    }

    /**
     * Get backup type based on file extension.
     */
    protected function getBackupType(string $filename): string
    {
        if (str_ends_with($filename, '.sqlite')) {
            return 'SQLite Database';
        } elseif (str_ends_with($filename, '.sql.gz')) {
            return 'Compressed SQL';
        } elseif (str_ends_with($filename, '.sql')) {
            return 'SQL Dump';
        }
        
        return 'Unknown';
    }

    /**
     * Format file size to human readable format.
     */
    protected function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}

