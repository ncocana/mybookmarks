<?php

namespace App\Http\Controllers;

use App\Http\Requests\Series\SeriesRequest;
use App\Http\Requests\Series\SeriesUpdate;
use App\Http\Resources\Series\SeriesResource;
use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SeriesController extends Controller
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
        $series = Series::query()     // Se usan mixins para extender builder y aplicar parámetros en la búsqueda
            ->allowedSorts(['actors', 'num_seasons', 'num_episodes', 'currently_at'])
            ->allowedFilters(['actors', 'num_seasons', 'num_episodes', 'currently_at'])
            ->jsonPaginate();
        
        return SeriesResource::collection($series);
        //Se utiliza un resource para la adhesión a la especificación ApiJson de la respuesta
    }

  
    public function store(SeriesRequest $request) // Se utiliza un form request para la validación
    {
        $series= Series::create([
            'actors' => $request->actors,
            'num_seasons' => $request->num_seasons,
            'num_episodes' => $request->num_episodes,
            'currently_at' => $request->currently_at,
        ]);

        $user = Auth::id();  //Recoge el id del usuario autenticado

        $series->bookmarks()->create([
            'user_id' => $user,
            'title' => $request->title,
            'synopsis' => $request->synopsis,
            'notes' => $request->notes,
        ]);
        //Crea un bookmark relacioando al libro y al usuario autenticado

        SeriesResource::make($series);
    }

 
    public function show(Series $series)
    {
        return SeriesResource::make($series);
    }

   
    public function update(SeriesUpdate $request, Series $series) { 
        //Se utiliza un formRequest especial para la validación que no tenga los campos title y director requeridos
        $series->fill([
            'actors' => $request->input('actors', $series->actors),
            'num_seasons' => $request->input('num_seasons', $series->num_seasons),
            'num_episodes' => $request->input('num_episodes', $series->num_episdodes),
            'currently_at' => $request->input('currently_at', $series->curretly_at),
        ])->save();
        // Con Fill() y save() no hace falta meter todos los atributos en la petición sólo los que modifiquemos
        // Con el segundo parámetro de input() nos aseguramos que si no pasamos un atributo coja los del libro por defecto
        
        $series->bookmarks()->update([
            'title' => $request->title,
            'synopsis' => $request->synopsis,
            'notes' => $request->notes,
        ]);
        
        SeriesResource::make($series);
    }

   
    public function destroy(Series $series)
    {
        $series->delete();
        return response()->json([
            "succes" =>"La serie ".$series->id." ha sido borrada con éxito"
        ]);
    }
}
