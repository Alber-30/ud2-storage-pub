<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class CsvController extends Controller
{
    /**
     * Lista todos los ficheros CSV de la carpeta storage/app.
     *
     * @return JsonResponse La respuesta en formato JSON.
     */
    public function index()
    {
        $files = Storage::files();
        $csvFiles = array_filter($files, function ($file) {
            return pathinfo($file, PATHINFO_EXTENSION) === 'csv';
        });

        return response()->json([
            'mensaje' => 'Listado de ficheros',
            'contenido' => array_map(fn($file) => basename($file), $csvFiles),
        ]);
    }

    /**
     * Recibe por parámetro el nombre de fichero y el contenido CSV y crea un nuevo fichero con ese nombre y contenido en storage/app.
     * Devuelve un JSON con el resultado de la operación.
     *
     * @param Request $request
     * @return JsonResponse La respuesta en formato JSON.
     */
    public function store(Request $request)
    {
        $request->validate([
            'filename' => 'required|string',
            'content' => 'required|string',
        ]);

        if (Storage::exists($request->filename)) {
            return response()->json([
                'mensaje' => 'El fichero ya existe',
            ], 409);
        }

        Storage::put($request->filename, $request->content);

        return response()->json([
            'mensaje' => 'Guardado con éxito',
        ]);
    }

    /**
     * Recibe por parámetro el nombre de un fichero CSV y devuelve un JSON con su contenido.
     *
     * @param string $id
     * @return JsonResponse La respuesta en formato JSON.
     */
    public function show(string $id)
    {
        // Verifica si el archivo existe en el almacenamiento
        if (!Storage::exists($id)) {
            return response()->json([
                'mensaje' => 'Fichero no encontrado',
            ], 404);
        }

        // Obtiene el contenido del archivo CSV
        $content = Storage::get($id);

        // Convierte el contenido en un array de líneas CSV
        $csvData = array_map('str_getcsv', explode("\n", $content));

        // Verifica si hay al menos una fila de datos
        if (count($csvData) < 2) {
            return response()->json([
                'mensaje' => 'El archivo CSV no contiene datos',
                'contenido' => [],
            ], 400);
        }

        // Extrae la primera fila como encabezados
        $headers = array_shift($csvData);

        // Convierte las filas restantes en un array asociativo usando los encabezados
        $content = array_map(function ($row) use ($headers) {
            return array_combine($headers, $row);
        }, $csvData);

        // Devuelve la respuesta JSON con los datos correctamente formateados
        return response()->json([
            'mensaje' => 'Fichero leído con éxito',
            'contenido' => $content,
        ]);
    }


    /**
     * Recibe por parámetro el nombre de fichero, el contenido CSV y actualiza el fichero CSV.
     *
     * @param Request $request
     * @param string $id
     * @return JsonResponse La respuesta en formato JSON.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        if (!Storage::exists($id)) {
            return response()->json([
                'mensaje' => 'El fichero no existe',
            ], 404);
        }

        // Validamos si el contenido es un CSV válido
        $csvData = @str_getcsv($request->content);
        if ($csvData === false) {
            return response()->json([
                'mensaje' => 'Formato de CSV no válido',
            ], 415);
        }

        Storage::put($id, $request->content);

        return response()->json([
            'mensaje' => 'Fichero actualizado exitosamente',
        ]);
    }

    /**
     * Recibe por parámetro el nombre de fichero y lo elimina.
     *
     * @param string $id
     * @return JsonResponse La respuesta en formato JSON.
     */
    public function destroy(string $id)
    {
        if (!Storage::exists($id)) {
            return response()->json([
                'mensaje' => 'El fichero no existe',
            ], 404);
        }

        Storage::delete($id);

        return response()->json([
            'mensaje' => 'Fichero eliminado exitosamente',
        ]);
    }
}
