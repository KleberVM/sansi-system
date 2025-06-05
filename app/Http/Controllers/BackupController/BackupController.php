<?php

namespace App\Http\Controllers\BackupController;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
class BackupController extends Controller
{
    public function index()
    {
        $logs = DB::table('audit_logs')
            ->leftJoin('users', 'audit_logs.usuario_id', '=', 'users.id')
            ->select(
                'audit_logs.*',
                'users.name as usuario_nombre'  // Traemos el nombre del usuario
            )->orderBy('fecha_cambio', 'desc')
            ->get();

        return view('backupAdmin.backup', compact('logs'));
    }

    public function createBackup() {}
}
