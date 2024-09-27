<?php

namespace App\Http\Controllers;
use App\Models\Marca;
use Illuminate\Http\Request;

class MarcaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $marcas = Marca::all();
            return response()->json($marcas);
        }catch (\Exception $e){
            return $e ->getMessage();
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view("admin.marcas");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            //validando que no exista un nombre igual en la bd
            $nombre = $request->input("nombre");
            $record = Marca::where("nombre", $nombre)->first();
            if($record){
                return response()->json(["status"=> 'Conflict',
                "data"=> null,"message"=> 'Ya existe un menú con este nombre, digite otro'],409);
            }else{
                $marca = new Marca();
                $marca->nombre = $request->nombre;
                if($marca->save() >0){
                    return response()->json(["status"=> 'Created',
                     "data"=> $marca, "message"=> 'Menú Registrado'],201);
                }else{
                    return response()->json(["status"=> 'Not Acceptable',
                    "data"=> null, "message"=> 'Error al insertar el registro de Marca'],406);
                }
            }
        }catch(\Exception $e){
            return $e->getMessage();
        }  
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //Nos devuelve una entidad por medio de su ID
        try{
            $marca = Marca::findOrFail($id);
            return response()->json($marca);
        }catch(\Exception $e){
            return $e->getMessage();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //Actualizar marcas
        try{
            $nombre = $request->input("nombre");
            $record = Marca::where("nombre", $nombre)->first();
            if($record){
                return response()->json(["status"=> 'Conflict',
                "data"=> null,"message"=> 'Ya existe un     Menú con este nombre, digite otro'],409);
            }else{
                $categoria = Marca::findOrFail($id);
                $categoria->nombre = $request->nombre;
                if($categoria->update() >0){
                    return response()->json(["status"=> 'Updated',
                     "data"=> $categoria, "message"=> 'Menú Actualizado...!'],202);
                }else{
                    return response()->json(["status"=> 'Not Acceptable',
                    "data"=> null, "message"=> 'Error al actualizar el registro'],406);
                }
            }
        }catch(\Exception $e){
            return $e->getMessage();
        }  
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //Eliminar una categoria
        try{
            $marca = Marca::findOrFail($id);
            if($marca->delete() > 0){
                return response()->json(["status"=> 'Deleted',
                 "data"=> null, "message"=> 'Menú Eliminado...!'],205);
            }else{
                return response()->json(["status"=> 'Conflict',
                "data"=> null, "message"=> 'No se puede eliminar este   Menú'],409);
            }
        }catch(\Exception $e){
            return $e->getMessage();
        }
    }
}
