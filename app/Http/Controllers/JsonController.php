<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class JsonController extends Controller
{
    // Función que valida si el contenido es un JSON válido.
    private function isValidJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * Lista todos los ficheros JSON de la carpeta storage/app.
     * Se debe comprobar fichero a fichero si su contenido es un JSON válido.
     *
     * @return JsonResponse La respuesta en formato JSON.
     */
    public function index()
    {
        $files = Storage::files('json'); // Aseguramos que los archivos estén en una carpeta específica.
        $jsonFiles = [];

        // Iteramos para validar los archivos JSON.
        foreach ($files as $file) {
            if ($this->isValidJson(Storage::get($file))) {
                $jsonFiles[] = basename($file); // Guardamos solo los archivos JSON válidos.
            }
        }

        return response()->json([
            'mensaje' => 'Operación exitosa',
            'contenido' => $jsonFiles,
        ]);
    }

    /**
     * Recibe un nombre de fichero y su contenido, y lo guarda como un nuevo archivo JSON.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $filename = $request->input('filename');
        $content = $request->input('content');

        if (!$filename || !$content) {
            return response()->json([
                'mensaje' => 'Faltan parámetros',
            ], 422);
        }

        if (Storage::exists('json/' . $filename)) {
            return response()->json([
                'mensaje' => 'El fichero ya existe',
            ], 409);
        }

        if (!$this->isValidJson($content)) {
            return response()->json([
                'mensaje' => 'Contenido no es un JSON válido',
            ], 415);
        }

        Storage::put('json/' . $filename, $content); // Guardamos el archivo en la carpeta 'json'

        return response()->json([
            'mensaje' => 'Fichero guardado exitosamente',
        ]);
    }

    /**
     * Muestra el contenido de un fichero específico.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id)
    {
        if (!Storage::exists('json/' . $id)) {
            return response()->json([
                'mensaje' => 'El fichero no existe',
            ], 404);
        }

        $content = Storage::get('json/' . $id);

        return response()->json([
            'mensaje' => 'Operación exitosa',
            'contenido' => json_decode($content, true),
        ]);
    }

    /**
     * Actualiza un fichero existente con un nuevo contenido.
     *
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(Request $request, string $id)
    {
        if (!Storage::exists('json/' . $id)) {
            return response()->json([
                'mensaje' => 'El fichero no existe',
            ], 404);
        }

        $content = $request->input('content');

        if (!$this->isValidJson($content)) {
            return response()->json([
                'mensaje' => 'Contenido no es un JSON válido',
            ], 415);
        }

        Storage::put('json/' . $id, $content);

        return response()->json([
            'mensaje' => 'Fichero actualizado exitosamente',
        ]);
    }

    /**
     * Elimina un fichero específico.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id)
    {
        if (!Storage::exists('json/' . $id)) {
            return response()->json([
                'mensaje' => 'El fichero no existe',
            ], 404);
        }

        Storage::delete('json/' . $id);

        return response()->json([
            'mensaje' => 'Fichero eliminado exitosamente',
        ]);
    }
}
