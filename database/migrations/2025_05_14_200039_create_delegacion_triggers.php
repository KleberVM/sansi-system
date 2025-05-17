<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateDelegacionTriggers extends Migration
{
    public function up()
    {
        // INSERT
        DB::unprepared('
            CREATE TRIGGER tr_delegacion_insert AFTER INSERT ON delegacion
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_logs (
                    tabla, accion, registro_id, datos_anteriores, datos_nuevos, usuario_id, fecha_cambio
                )
                VALUES (
                    "delegacion",
                    "INSERT",
                    NEW.idDelegacion,
                    NULL,
                    JSON_OBJECT(
                        "codigo_sie", NEW.codigo_sie,
                        "nombre", NEW.nombre,
                        "direccion", NEW.direccion,
                        "telefono", NEW.telefono,
                        "created_at", NEW.created_at
                    ),
                    @current_user_id,
                    NOW()
                );
            END
        ');

        // UPDATE
        DB::unprepared('
            CREATE TRIGGER tr_delegacion_update AFTER UPDATE ON delegacion
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_logs (
                    tabla, accion, registro_id, datos_anteriores, datos_nuevos, usuario_id, fecha_cambio
                )
                VALUES (
                    "delegacion",
                    "UPDATE",
                    NEW.idDelegacion,
                    JSON_OBJECT(
                        "codigo_sie", OLD.codigo_sie,
                        "nombre", OLD.nombre,
                        "direccion", OLD.direccion,
                        "telefono", OLD.telefono,
                        "updated_at", OLD.updated_at
                    ),
                    JSON_OBJECT(
                        "codigo_sie", NEW.codigo_sie,
                        "nombre", NEW.nombre,
                        "direccion", NEW.direccion,
                        "telefono", NEW.telefono,
                        "updated_at", NEW.updated_at
                    ),
                    @current_user_id,
                    NOW()
                );
            END
        ');

        // DELETE
        DB::unprepared('
            CREATE TRIGGER tr_delegacion_delete AFTER DELETE ON delegacion
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_logs (
                    tabla, accion, registro_id, datos_anteriores, datos_nuevos, usuario_id, fecha_cambio
                )
                VALUES (
                    "delegacion",
                    "DELETE",
                    OLD.idDelegacion,
                    JSON_OBJECT(
                        "codigo_sie", OLD.codigo_sie,
                        "nombre", OLD.nombre,
                        "direccion", OLD.direccion,
                        "telefono", OLD.telefono,
                        "deleted_at", NOW()
                    ),
                    NULL,
                    @current_user_id,
                    NOW()
                );
            END
        ');
    }

    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS tr_delegacion_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS tr_delegacion_update');
        DB::unprepared('DROP TRIGGER IF EXISTS tr_delegacion_delete');
    }
}
