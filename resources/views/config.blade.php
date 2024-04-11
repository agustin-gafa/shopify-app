@extends('layouts.app')

@section('content')
    <cuadro-component marca="{{ config('app.name', 'Laravel') }}" username="{{ Auth::user()->name }}">
        <template v-slot:token>    
            @csrf
        </template>

        <template v-slot:contenido>
            <tiendas></tiendas>
        </template>

    </<cuadro-component>
@endsection
