<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Database\Factories\BookFactory;
use Illuminate\Http\Request;

class BookController extends Controller
{
   
    public function index()
    {
        $books = Book::query()     // Se usan mixins para extender builder y aplicar parámetros en la búsqueda
        ->allowedSorts(['title', 'author'])
        ->jsonPaginate();
        
        return BookResource::collection($books);
        //Se utiliza un resource para la adhesión a la especificación ApiJson de la respuesta
    }


    public function store(BookRequest $request) // Se utiliza un form request para la validación
    {
        $book= Book::create([
            'title' => $request->input('data.attributes.title'),
            'author' => $request->input('data.attributes.author'),
            'language' => $request->input('data.attributes.language'),
            'read_pages' => $request->input('data.attributes.read_pages'),
            'total_pages' => $request->input('data.attributes.total_pages'),
            'synopsis' => $request->input('data.attributes.synopsis'),
            'notes' => $request->input('data.attributes.notes'),
        ]);

        BookResource::make($book);
    }


  
    public function show(Book $book)
    {
       return BookResource::make($book);
    }



    public function update(Request $request, Book $book)
    {
        $book->update([
            'title' => $request->input('data.attributes.title'),
            'author' => $request->input('data.attributes.author'),
            'language' => $request->input('data.attributes.language'),
            'read_pages' => $request->input('data.attributes.read_pages'),
            'total_pages' => $request->input('data.attributes.total_pages'),
            'synopsis' => $request->input('data.attributes.synopsis'),
            'notes' => $request->input('data.attributes.notes'),
        ]);

        BookResource::make($book);
    }


  
    public function destroy(Book $book)
    {
        $book->delete();
        return response()->json([
            "Succes"=> "Libro ".$book->id." eliminado"
        ]);
    }
}