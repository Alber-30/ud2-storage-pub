<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;

class CsvTest extends TestCase
{
    public function testIndex()
    {
        Storage::fake('local');

        // Crear ficheros CSV falsos
        Storage::put('file1.csv', "header1,header2\nvalue1,value2");
        Storage::put('file2.csv', "header1,header2\nvalue1,value2");

        $response = $this->getJson('/api/csv');

        $response->assertStatus(200)
                 ->assertJson([
                     'mensaje' => 'Listado de ficheros',
                     'contenido' => ['file1.csv', 'file2.csv'],
                 ]);
    }

    public function testShow()
    {
        Storage::fake('local');

        // Crear un archivo CSV
        Storage::put('existingfile.csv', "header1,header2\nvalue1,value2");

        $response = $this->getJson('/api/csv/existingfile.csv');

        $response->assertStatus(200)
                 ->assertJson([
                     'mensaje' => 'Fichero leído con éxito',
                     'contenido' => [
                         ['header1' => 'value1', 'header2' => 'value2']
                     ]
                 ]);
    }


    public function testStore()
    {
        Storage::fake('local');

        $data = [
            'filename' => 'newfile.csv',
            'content' => "header1,header2\nvalue1,value2",
        ];

        $response = $this->postJson('/api/csv', $data);

        $response->assertStatus(200)
                 ->assertJson([
                     'mensaje' => 'Guardado con éxito',
                 ]);

        Storage::disk('local')->assertExists('newfile.csv');
    }

    public function testUpdate()
    {
        Storage::fake('local');

        // Crear un archivo CSV
        Storage::put('existingfile.csv', "header1,header2\nvalue1,value2");

        $data = [
            'content' => "header1,header2\nvalue3,value4",
        ];

        $response = $this->putJson('/api/csv/existingfile.csv', $data);

        $response->assertStatus(200)
                 ->assertJson([
                     'mensaje' => 'Fichero actualizado exitosamente',
                 ]);

        // Verifica que el contenido del archivo se haya actualizado correctamente
        $content = Storage::get('existingfile.csv');
        $this->assertStringContainsString('value3,value4', $content);
    }

    public function testDestroy()
    {
        Storage::fake('local');

        // Crear un archivo CSV
        Storage::put('existingfile.csv', "header1,header2\nvalue1,value2");

        $response = $this->deleteJson('/api/csv/existingfile.csv');

        $response->assertStatus(200)
                 ->assertJson([
                     'mensaje' => 'Fichero eliminado exitosamente',
                 ]);

        // Verifica que el archivo haya sido eliminado
        Storage::disk('local')->assertMissing('existingfile.csv');
    }
}
