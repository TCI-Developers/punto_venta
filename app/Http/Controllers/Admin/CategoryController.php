<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    // vista principal
    public function index()
    {   
        $categories = Category::where('status', 1)->get();
        return view('admin.category.index', ['categories' => $categories, 'status' => 1]);
    }
    
    //funcion para listar categorias inhabilidatas
    public function indexDead()
    {
        $categories = Category::where('status', 0)->get();
        return view('admin.category.index', ['categories' => $categories, 'status' => 0]);
    }

    // funcion para guardar    
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'description' => 'required',
        ]);

        $category = new Category();
        $category->name = $request->name;
        $category->description = $request->description;
        $category->save();

        return redirect()->back()->with('success', 'Categoria agregada con exito.');
    }


    // funcion para actualizar
    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'id' => 'required',
            'name' => 'required',
            'description' => 'required',
        ]);

        $category = Category::find($request->id);
        if(is_object($category)){
            $category->name = $request->name;
            $category->description = $request->description;
            $category->save();
        }

        return redirect()->back()->with('success', 'Categoria actualizada con exito.');
    }

    // funcion para inhabilitar categorias
    public function destroy($id)
    {
        $category = Category::find($id);
        if(is_object($category)){
            $category->status = 0;
            $category->save();
        }

        return redirect()->back()->with('success', 'Categoria eliminada con exito.');
    }

    // funcion para habilitar categorias eliminadas
    public function enable($id)
    {
        $category = Category::find($id);
        if(is_object($category)){
            $category->status = 1;
            $category->save();
        }

        return redirect()->back()->with('success', 'Categoria habilitada con exito.');
    }
}
