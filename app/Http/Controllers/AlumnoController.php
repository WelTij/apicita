<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AlumnoController extends Controller
{
    protected $allowedExtensions = ['png', 'jpg', 'jpeg', 'gif'];

    protected function allowedFile($filename)
    {
        return in_array(strtolower(pathinfo($filename, PATHINFO_EXTENSION)), $this->allowedExtensions);
    }

    public function uploadImage(Request $request)
    {
        if (!$request->hasFile('file')) {
            return response()->json(['message' => 'No se encontró ninguna imagen en la solicitud.'], 400);
        }

        $file = $request->file('file');

        if (!$file->isValid()) {
            return response()->json(['message' => 'Archivo no válido.'], 400);
        }

        if (!$this->allowedFile($file->getClientOriginalName())) {
            return response()->json(['message' => 'Formato de archivo no permitido.'], 400);
        }

        $filename = $file->getClientOriginalName();
        $file->storeAs('assets/uploads', $filename, 'public');


        $data = $request->only(['matricula', 'nombre', 'apellidos', 'curp', 'sexo', 'edad']);
        $data['image_filename'] = $filename;
        DB::table('alumnos')->insert($data);

        return response()->json(['message' => 'Datos almacenados exitosamente.'], 200);
    }

    public function getAlumnos()
    {
        $alumnos = DB::table('alumnos')->get();
        $base_url = 'http://192.168.1.65:8000/ApiTV/public/api/alumnos';

        $alumnosWithUrls = $alumnos->map(function ($alumno) use ($base_url) {
            $alumno->image_url = $base_url . '/public/assets/uploads/' . $alumno->image_filename;
            return $alumno;
        });

        return response()->json($alumnosWithUrls);
    }
}
