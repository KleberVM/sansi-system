<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade as PDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Auth;
class DelegacionController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('delegacion');
        
        // Search functionality
        if ($request->has('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('nombre', 'like', $searchTerm)
                  ->orWhere('codigo_sie', 'like', $searchTerm);
            });
        }
        
        // Apply filters
        if ($request->filled('dependencia')) {
            $query->where('dependencia', $request->dependencia);
        }

        if ($request->filled('departamento')) {
            $query->where('departamento', $request->departamento);
        }

        if ($request->filled('provincia')) {
            $query->where('provincia', $request->provincia);
        }

        if ($request->filled('municipio')) {
            $query->where('municipio', $request->municipio);
        }

        // Get paginated results with all current query parameters
        $delegaciones = $query->orderBy('nombre')
                            ->paginate(10)
                            ->appends($request->all());

        return view('delegaciones.delegaciones', compact('delegaciones'));
    }

    public function create()
    {
        return view('delegaciones.agregarDelegacion');
    }

    public function store(Request $request)
    {
        // Validar los datos del formulario
        $validator = Validator::make($request->all(), [
            'codigo_sie' => 'required|string|max:20|unique:delegacion',
            'nombre' => 'required|string|max:100|unique:delegacion',
            'dependencia' => 'required|in:Fiscal,Convenio,Privado,Comunitaria',
            'departamento' => 'required|string',
            'provincia' => 'required|string|max:20',
            'municipio' => 'required|string|max:20',
            'zona' => 'nullable|string|max:30',
            'direccion' => 'required|string|max:40',
            'telefono' => 'nullable|numeric',
            'nombre_responsable' => 'required|string|max:40',
            'correo_responsable' => 'required|email|unique:delegacion,responsable_email',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Insertar en la base de datos
        DB::statement('SET @current_user_id = ' . Auth::id());
        DB::table('delegacion')->insert([
            'codigo_sie' => $request->codigo_sie,
            'nombre' => $request->nombre,
            'dependencia' => $request->dependencia,
            'departamento' => $request->departamento,
            'provincia' => $request->provincia,
            'municipio' => $request->municipio,
            'zona' => $request->zona,
            'direccion' => $request->direccion,
            'telefono' => $request->telefono,
            'responsable_nombre' => $request->nombre_responsable,
            'responsable_email' => $request->correo_responsable,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('delegaciones')
            ->with('success', 'Colegio agregado correctamente');
    }

    public function show($codigo_sie)
    {
        $delegacion = DB::table('delegacion')->where('codigo_sie', $codigo_sie)->first();
        
        if (!$delegacion) {
            return redirect()->route('delegaciones')->with('error', 'Colegio no encontrado');
        }
        
        return view('delegaciones.ver', compact('delegacion'));
    }

    public function edit($codigo_sie)
    {
        $delegacion = DB::table('delegacion')->where('codigo_sie', $codigo_sie)->first();
        
        if (!$delegacion) {
            return redirect()->route('delegaciones')->with('error', 'Colegio no encontrado');
        }
        
        return view('delegaciones.editar', compact('delegacion'));
    }

    public function update(Request $request, $codigo_sie)
    {
        // Validar los datos del formulario
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100|unique:delegacion,nombre,' . $codigo_sie . ',codigo_sie',
            'dependencia' => 'required|in:Fiscal,Convenio,Privado,Comunitaria',
            'departamento' => 'required|string',
            'provincia' => 'required|string|max:20',
            'municipio' => 'required|string|max:20',
            'zona' => 'required|string|max:30',
            'direccion' => 'required|string|max:40',
            'telefono' => 'required|numeric',
            'nombre_responsable' => 'required|string|max:40',
            'correo_responsable' => 'required|email|unique:delegacion,responsable_email,' . $codigo_sie . ',codigo_sie',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Actualizar en la base de datos
        DB::statement('SET @current_user_id = ' . Auth::id());
        DB::table('delegacion')
            ->where('codigo_sie', $codigo_sie)
            ->update([
                'nombre' => $request->nombre,
                'dependencia' => $request->dependencia,
                'departamento' => $request->departamento,
                'provincia' => $request->provincia,
                'municipio' => $request->municipio,
                'zona' => $request->zona,
                'direccion' => $request->direccion,
                'telefono' => $request->telefono,
                'responsable_nombre' => $request->nombre_responsable,
                'responsable_email' => $request->correo_responsable,
                'updated_at' => now(),
            ]);

        return redirect()->route('delegaciones.ver', $codigo_sie)
            ->with('success', 'Colegio actualizado correctamente');
    }

    public function destroy($codigo_sie)
    {
        try {
            DB::statement('SET @current_user_id = ' . Auth::id());
            $deleted = DB::table('delegacion')->where('codigo_sie', $codigo_sie)->delete();
            
            if ($deleted) {
                return response()->json(['success' => true]);
            } else {
                return response()->json(['success' => false, 'message' => 'No se encontró el colegio'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar el colegio'], 500);
        }
    }

    public function exportPdf()
    {
        $delegaciones = DB::table('delegacion')->get();
        
        $pdf = PDF::loadView('delegaciones.pdf', compact('delegaciones'));
        
        return $pdf->download('delegaciones.pdf');
    }

    public function exportExcel()
    {
        $delegaciones = DB::table('delegacion')->get();
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Headers
        $sheet->setCellValue('A1', 'Código SIE');
        $sheet->setCellValue('B1', 'Nombre');
        $sheet->setCellValue('C1', 'Departamento');
        $sheet->setCellValue('D1', 'Provincia');
        $sheet->setCellValue('E1', 'Municipio');
        $sheet->setCellValue('F1', 'Dependencia');
        
        // Data
        $row = 2;
        foreach ($delegaciones as $delegacion) {
            $sheet->setCellValue('A' . $row, $delegacion->codigo_sie);
            $sheet->setCellValue('B' . $row, $delegacion->nombre);
            $sheet->setCellValue('C' . $row, $delegacion->departamento);
            $sheet->setCellValue('D' . $row, $delegacion->provincia);
            $sheet->setCellValue('E' . $row, $delegacion->municipio);
            $sheet->setCellValue('F' . $row, $delegacion->dependencia);
            $row++;
        }
        
        $writer = new Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="delegaciones.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
    
    // Helper method to get filter information for the title
    private function getFilterInfo(Request $request)
    {
        $filterInfo = [
            'title' => 'Registro de Colegios',
            'filters' => []
        ];
        
        if ($request->has('departamento') && !empty($request->departamento)) {
            $filterInfo['filters'][] = 'Departamento: ' . $request->departamento;
        }
        
        if ($request->has('provincia') && !empty($request->provincia)) {
            $filterInfo['filters'][] = 'Provincia: ' . $request->provincia;
        }
        
        if ($request->has('municipio') && !empty($request->municipio)) {
            $filterInfo['filters'][] = 'Municipio: ' . $request->municipio;
        }
        
        if ($request->has('dependencia') && !empty($request->dependencia)) {
            $filterInfo['filters'][] = 'Dependencia: ' . $request->dependencia;
        }
        
        if ($request->has('search') && !empty($request->search)) {
            $filterInfo['filters'][] = 'Búsqueda: ' . $request->search;
        }
        
        return $filterInfo;
    }
}