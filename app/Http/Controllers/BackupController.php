<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;


class BackupController extends Controller
{
    public function backup()
    {
        $databaseName = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');

        $fileName = 'madusis_backup_' . date('Y-m-d_H-i-s') . '.sql';
        
        // Tentukan folder penyimpanan di storage/app/backup/
        $backupDir = storage_path('app/backup');
        
        // Buat folder 'backup' secara otomatis jika belum ada
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true, true);
        }

        $backupPath = $backupDir . '/' . $fileName;

        try {
            $tables = DB::select('SHOW TABLES');
            $tablesArray = array_map('current', $tables);
            
            $sqlContent = "-- Madusis POS Database Backup\n-- Date: " . date('Y-m-d H:i:s') . "\n\n";
            $sqlContent .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tablesArray as $table) {
                $createTable = DB::select("SHOW CREATE TABLE `{$table}`")[0];
                $sqlContent .= "DROP TABLE IF EXISTS `{$table}`;\n";
                $sqlContent .= $createTable->{'Create Table'} . ";\n\n";

                $rows = DB::table($table)->get();
                foreach ($rows as $row) {
                    $rowArray = (array) $row;
                    $columns = array_keys($rowArray);
                    $values = array_values($rowArray);

                    $escapedValues = array_map(function ($value) {
                        if (is_null($value)) return 'NULL';
                        return "'" . addslashes($value) . "'";
                    }, $values);

                    $sqlContent .= "INSERT INTO `{$table}` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $escapedValues) . ");\n";
                }
                $sqlContent .= "\n";
            }
            $sqlContent .= "SET FOREIGN_KEY_CHECKS=1;\n";

            File::put($backupPath, $sqlContent);

            // Mengunduh file ke browser, lalu otomatis menghapusnya dari server setelah terkirim agar tidak menumpuk
            return response()->download($backupPath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal melakukan backup database: ' . $e->getMessage());
        }
    }

    // 2. Fitur Restore Database (Upload file .sql)
    public function restore(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'backup_file' => 'required|file|mimes:sql,txt|max:10240', // Maksimal 10MB
        ]);

        try {
            $file = $request->file('backup_file');
            $sqlContent = File::get($file->getRealPath());

            // Jalankan SQL queries secara aman
            DB::unprepared('SET FOREIGN_KEY_CHECKS=0;');
            
            // Pisahkan per baris perintah SQL atau eksekusi sekaligus
            DB::unprepared($sqlContent);
            
            DB::unprepared('SET FOREIGN_KEY_CHECKS=1;');

            return redirect()->back()->with('success', 'Database berhasil direstore!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal merestore database: ' . $e->getMessage());
        }
    }
}
