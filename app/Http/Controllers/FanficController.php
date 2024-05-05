<?php

namespace App\Http\Controllers;

use App\Http\Requests\fanfic\FanficRequest;
use App\Http\Requests\fanfic\FanficUpdate;
use App\Http\Resources\fanfic\FanficResource;
use App\Models\Fanfic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FanficController extends Controller
{

    public function __construct()  //Aplica el Sanctum a los métodos store, update y delete
    {
        $this->middleware('auth:sanctum')
        ->only([
            'store',
            'update',
            'destroy'
        ]);
        
    }
 
    public function index()
    {
        $fanfics = Fanfic::query()     // Se usan mixins para extender builder y aplicar parámetros en la búsqueda
        ->allowedSorts(['title', 'author'])
        ->jsonPaginate();
        
        return FanficResource::collection($fanfics);
        //Se utiliza un resource para la adhesión a la especificación ApiJson de la respuesta
    }


    public function store(FanficRequest $request) // Se utiliza un form request para la validación
    {
        $fanfic= Fanfic::create([
            'title' => $request->title,
            'author' => $request->author,
            'language' => $request->language,
            'fandom' => $request->fandom,
            'relationships' => $request->relationships,
            'words' => $request->words,
            'read_chapters' => $request->read_chapters,
            'total_chapters' => $request->total_chapters,
            'synopsis' => $request->synopsis,
            'notes' => $request->notes,
        ]);

        $user = Auth::id();  //Recoge el id del usuario autenticado

        $fanfic->Bookmarks()->create(['user_id' => $user]);
        //Crea un Fanficmark relacioando al libro y al usuario autenticado

        return FanficResource::make($fanfic);
    }

  
    public function show(Fanfic $fanfic)
    {
       return FanficResource::make($fanfic);
    }


    public function update(FanficUpdate $request, Fanfic $fanfic) { 
        //Se utiliza un formRequest especial para la validación que no tenga los campos title y author requeridos
            $fanfic->fill([
                'title' => $request->input('title', $fanfic->title),
                'author' => $request->input('author', $fanfic->author),
                'language' => $request->input('language', $fanfic->language),
                'fandom' => $request->input('fandom', $fanfic->fandom),
                'relationships' => $request->input('relationships', $fanfic->relationships),
                'words' => $request->input('words', $fanfic->words),
                'read_chapters' => $request->input('read_chapters', $fanfic->read_chapters),
                'total_chapters' => $request->input('total_chapters', $fanfic->total_chapters),
                'synopsis' => $request->input('synopsis', $fanfic->synopsis),
                'notes' => $request->input('notes', $fanfic->notes),
            ])->save();
        // Con Fill() y save() no hace falta meter todos los atributos en la petición sólo los que modifiquemos
        // Con el segundo parámetro de input() nos aseguramos que si no pasamos un atributo coja los del libro por defecto
            FanficResource::make($fanfic);
        }

  
        public function destroy(Fanfic $fanfic)
        {
            $fanfic->delete();
            return response()->json([
                "Succes"=> "El fanfic ".$fanfic->id." ha sido eliminado con éxito"
            ]);
        }
}
