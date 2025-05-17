<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateConvocatoriaTriggers extends Migration
{
    public function up()
    {
        // INSERT
        DB::unprepared('
            CREATE TRIGGER tr_convocatoria_insert AFTER INSERT ON convocatoria
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_logs (
                    tabla, accion, registro_id, datos_anteriores, datos_nuevos, usuario_id, fecha_cambio
                )
                VALUES (
                    "convocatoria",
                    "INSERT",
                    NEW.idConvocatoria,
                    NULL,
                    JSON_OBJECT(
                        "nombre", NEW.nombre,
                        "descripcion", NEW.descripcion,
                        "fechaInicio", NEW.fechaInicio,
                        "fechaFin", NEW.fechaFin,
                        "contacto", NEW.contacto,
                        "requisitos", NEW.requisitos,
                        "metodoPago", NEW.metodoPago,
                        "estado", NEW.estado,
                        "created_at", NEW.created_at,
                        "updated_at", NEW.updated_at
                    ),
                    @current_user_id,
                    NOW()
                );
            END
        ');

        // UPDATE
        DB::unprepared('
            CREATE TRIGGER tr_convocatoria_update AFTER UPDATE ON convocatoria
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_logs (
                    tabla, accion, registro_id, datos_anteriores, datos_nuevos, usuario_id, fecha_cambio
                )
                VALUES (
                    "convocatoria",
                    "UPDATE",
                    NEW.idConvocatoria,
                    JSON_OBJECT(
                        "nombre", OLD.nombre,
                        "descripcion", OLD.descripcion,
                        "fechaInicio", OLD.fechaInicio,
                        "fechaFin", OLD.fechaFin,
                        "contacto", OLD.contacto,
                        "requisitos", OLD.requisitos,
                        "metodoPago", OLD.metodoPago,
                        "estado", OLD.estado,
                        "created_at", OLD.created_at,
                        "updated_at", OLD.updated_at
                    ),
                    JSON_OBJECT(
                        "nombre", NEW.nombre,
                        "descripcion", NEW.descripcion,
                        "fechaInicio", NEW.fechaInicio,
                        "fechaFin", NEW.fechaFin,
                        "contacto", NEW.contacto,
                        "requisitos", NEW.requisitos,
                        "metodoPago", NEW.metodoPago,
                        "estado", NEW.estado,
                        "created_at", NEW.created_at,
                        "updated_at", NEW.updated_at
                    ),
                    @current_user_id,
                    NOW()
                );
            END
        ');

        // DELETE
        DB::unprepared('
            CREATE TRIGGER tr_convocatoria_delete AFTER DELETE ON convocatoria
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_logs (
                    tabla, accion, registro_id, datos_anteriores, datos_nuevos, usuario_id, fecha_cambio
                )
                VALUES (
                    "convocatoria",
                    "DELETE",
                    OLD.idConvocatoria,
                    JSON_OBJECT(
                        "nombre", OLD.nombre,
                        "descripcion", OLD.descripcion,
                        "fechaInicio", OLD.fechaInicio,
                        "fechaFin", OLD.fechaFin,
                        "contacto", OLD.contacto,
                        "requisitos", OLD.requisitos,
                        "metodoPago", OLD.metodoPago,
                        "estado", OLD.estado,
                        "created_at", OLD.created_at,
                        "updated_at", OLD.updated_at,
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
        DB::unprepared('DROP TRIGGER IF EXISTS tr_convocatoria_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS tr_convocatoria_update');
        DB::unprepared('DROP TRIGGER IF EXISTS tr_convocatoria_delete');
    }
}
