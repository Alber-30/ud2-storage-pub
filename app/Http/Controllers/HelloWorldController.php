<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;


class HelloWorldController extends Controller
{
    /**
     * Lista todos los ficheros de la carpeta storage/app.
     *
     * @return JsonResponse La respuesta en formato JSON.
     *
     * El JSON devuelto debe tener las siguientes claves:
     * - mensaje: Un mensaje indicando el resultado de la operación.
     * - contenido: Un array con los nombres de los ficheros.
     */
    public function index()
    {
        $files = Storage::disk('local')->files();  // Lista de archivos
        return response()->json([
            'mensaje' => 'Listado de ficheros',
            'contenido' => $files,  // Los archivos que se encuentran
        ]);
    }


     /**
     * Recibe por parámetro el nombre de fichero y el contenido. Devuelve un JSON con el resultado de la operación.
     * Si el fichero ya existe, devuelve un 409.
     *
     * @param filename Parámetro con el nombre del fichero. Devuelve 422 si no hay parámetro.
     * @param content Contenido del fichero. Devuelve 422 si no hay parámetro.
     * @return JsonResponse La respuesta en formato JSON.
     *
     * El JSON devuelto debe tener las siguientes claves:
     * - mensaje: Un mensaje indicando el resultado de la operación.
     */
    public function store(Request $request)
    {
        $request->validate([
            'filename' => 'required|string',
            'content' => 'required|string',
        ]);

        if (Storage::disk('local')->exists($request->filename)) {
            return response()->json(['mensaje' => 'El archivo ya existe'], 409);  // Conflict
        }

        Storage::disk('local')->put($request->filename, $request->content);
        return response()->json(['mensaje' => 'Guardado con éxito'], 200);
    }


     /**
     * Recibe por parámetro el nombre de fichero y devuelve un JSON con su contenido
     *
     * @param name Parámetro con el nombre del fichero.
     * @return JsonResponse La respuesta en formato JSON.
     *
     * El JSON devuelto debe tener las siguientes claves:
     * - mensaje: Un mensaje indicando el resultado de la operación.
     * - contenido: El contenido del fichero si se ha leído con éxito.
     */
    public function show($filename)
    {
        if (!Storage::disk('local')->exists($filename)) {
            return response()->json(['mensaje' => 'Archivo no encontrado'], 404);  // Not Found
        }

        $content = Storage::disk('local')->get($filename);
        return response()->json([
            'mensaje' => 'Archivo leído con éxito',
            'contenido' => $content,
        ]);
    }


    /**
     * Recibe por parámetro el nombre de fichero, el contenido y actualiza el fichero.
     * Devuelve un JSON con el resultado de la operación.
     * Si el fichero no existe devuelve un 404.
     *
     * @param filename Parámetro con el nombre del fichero. Devuelve 422 si no hay parámetro.
     * @param content Contenido del fichero. Devuelve 422 si no hay parámetro.
     * @return JsonResponse La respuesta en formato JSON.
     *
     * El JSON devuelto debe tener las siguientes claves:
     * - mensaje: Un mensaje indicando el resultado de la operación.
     */
    public function update(Request $request, $filename)
    {
        if (!Storage::disk('local')->exists($filename)) {
            return response()->json(['mensaje' => 'El archivo no existe'], 404);  // Not Found
        }

        $request->validate(['content' => 'required|string']);
        Storage::disk('local')->put($filename, $request->content);

        return response()->json(['mensaje' => 'Actualizado con éxito'], 200);
    }


    /**
     * Recibe por parámetro el nombre de ficher y lo elimina.
     * Si el fichero no existe devuelve un 404.
     *
     * @param filename Parámetro con el nombre del fichero. Devuelve 422 si no hay parámetro.
     * @return JsonResponse La respuesta en formato JSON.
     *
     * El JSON devuelto debe tener las siguientes claves:
     * - mensaje: Un mensaje indicando el resultado de la operación.
     */
    public function destroy($filename)
    {
        if (!Storage::disk('local')->exists($filename)) {
            return response()->json(['mensaje' => 'El archivo no existe'], 404);  // Not Found
        }

        Storage::disk('local')->delete($filename);
        return response()->json(['mensaje' => 'Eliminado con éxito'], 200);
    }

}
