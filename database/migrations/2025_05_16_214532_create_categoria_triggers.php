<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateCategoriaTriggers extends Migration
{
    public function up()
    {
        // INSERT
        DB::unprepared('
            CREATE TRIGGER tr_categoria_insert AFTER INSERT ON categoria
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_logs (
                    tabla, accion, registro_id, datos_anteriores, datos_nuevos, usuario_id, fecha_cambio
                )
                VALUES (
                    "categoria",
                    "INSERT",
                    NEW.idCategoria,
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
            CREATE TRIGGER tr_categoria_update AFTER UPDATE ON categoria
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_logs (
                    tabla, accion, registro_id, datos_anteriores, datos_nuevos, usuario_id, fecha_cambio
                )
                VALUES (
                    "categoria",
                    "UPDATE",
                    NEW.idCategoria,
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
            CREATE TRIGGER tr_categoria_delete AFTER DELETE ON categoria
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_logs (
                    tabla, accion, registro_id, datos_anteriores, datos_nuevos, usuario_id, fecha_cambio
                )
                VALUES (
                    "categoria",
                    "DELETE",
                    OLD.idCategoria,
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
        DB::unprepared('DROP TRIGGER IF EXISTS tr_categoria_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS tr_categoria_update');
        DB::unprepared('DROP TRIGGER IF EXISTS tr_categoria_delete');
    }
}
