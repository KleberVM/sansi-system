<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateAreaTriggers extends Migration
{
    public function up()
    {
        // INSERT
        DB::unprepared('
            CREATE TRIGGER tr_area_insert AFTER INSERT ON area
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_logs (
                    tabla, accion, registro_id, datos_anteriores, datos_nuevos, usuario_id, fecha_cambio
                )
                VALUES (
                    "area",
                    "INSERT",
                    NEW.idArea,
                    NULL,
                    JSON_OBJECT(
                        "nombre", NEW.nombre
                    ),
                    @current_user_id,
                    NOW()
                );
            END
        ');

        // UPDATE
        DB::unprepared('
            CREATE TRIGGER tr_area_update AFTER UPDATE ON area
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_logs (
                    tabla, accion, registro_id, datos_anteriores, datos_nuevos, usuario_id, fecha_cambio
                )
                VALUES (
                    "area",
                    "UPDATE",
                    NEW.idArea,
                    JSON_OBJECT(
                        "nombre", OLD.nombre
                    ),
                    JSON_OBJECT(
                        "nombre", NEW.nombre
                    ),
                    @current_user_id,
                    NOW()
                );
            END
        ');

        // DELETE
        DB::unprepared('
            CREATE TRIGGER tr_area_delete AFTER DELETE ON area
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_logs (
                    tabla, accion, registro_id, datos_anteriores, datos_nuevos, usuario_id, fecha_cambio
                )
                VALUES (
                    "area",
                    "DELETE",
                    OLD.idArea,
                    JSON_OBJECT(
                        "nombre", OLD.nombre,
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
        DB::unprepared('DROP TRIGGER IF EXISTS tr_area_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS tr_area_update');
        DB::unprepared('DROP TRIGGER IF EXISTS tr_area_delete');
    }
}
