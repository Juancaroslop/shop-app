<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categoria;

class CategoriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $categorias = Categoria::all();
            return response()->json($categorias);
        }catch(\Exception $e){
            return $e->getMessage();
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view("admin.categorias");
        //devolver una vista
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            $nombre = $request->input("nombre");
            $record = Categoria::where("nombre", $nombre)->first();
            if($record){
                return response()->json(["status"=> 'Conflict',
                "data"=> null,"message"=> 'Ya existe una categoria con este nombre'],409);
            }else{
                $categoria = new Categoria();
                $categoria->nombre = $request->nombre;
                if($categoria->save() >0){
                    return response()->json(["status"=> 'Created',
                     "data"=> $categoria, "message"=> 'Categoria Registrada'],201);
                }else{
                    return response()->json(["status"=> 'fail',
                    "data"=> null, "message"=> 'Error al intentar guardar la categoria'],409);
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
            $categoria = Categoria::findOrFail($id);
            return response()->json($categoria);
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
        //Actualizar Categorias
        try{
            $nombre = $request->input("nombre");
            $record = Categoria::where("nombre", $nombre)->first();
            if($record){
                return response()->json(["status"=> 'Conflict',
                "data"=> null,"message"=> 'Ya existe una categoria con este nombre'],409);
            }else{
                $categoria = Categoria::findOrFail($id);
                $categoria->nombre = $request->nombre;
                if($categoria->update() >0){
                    return response()->json(["status"=> 'Updated',
                     "data"=> $categoria, "message"=> 'Categoria Actualizada...!'],202);
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
            $categoria = Categoria::findOrFail($id);
            if($categoria->delete() >0){
                return response()->json(["status"=> 'Deleted',
                 "data"=> null, "message"=> 'Categoria Eliminada...!'],205);
            }else{
                return response()->json(["status"=> 'Conflict',
                "data"=> null, "message"=> 'No se puede eliminar esta categoria'],409);
            }
        }catch(\Exception $e){
            return $e->getMessage();
        }
    }
}
