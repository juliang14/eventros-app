<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RsvpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function store(Request $request, $invite_code){
        $guest = \App\Models\Guest::where('invite_code', $invite_code)->firstOrFail();

        $data = $request->validate([
            'status' => 'required|in:yes,no,maybe',
            'companions' => 'nullable|integer|min:0',
        ]);

        $rsvp = \App\Models\Rsvp::updateOrCreate(
            ['guest_id' => $guest->id],
            ['status' => $data['status'], 'companions' => $data['companions'] ?? 0]
        );

        return redirect()->back()->with('success', 'Respuesta registrada. ¡Gracias!');
    }

}
