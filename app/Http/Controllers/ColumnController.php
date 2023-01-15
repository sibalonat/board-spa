<?php

namespace App\Http\Controllers;

use App\Models\Column;
use Illuminate\Http\Request;
// Spatie/db-dumper

class ColumnController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Column::with('cards')->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $column = Column::create($request->all());
        return response()->json($column);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Column $column)
    {
        $column->delete();
        $column->fresh();
        return response()->json('done');
    }

}
