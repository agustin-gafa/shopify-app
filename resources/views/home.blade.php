@extends('layouts.app')

@section('content')
    <home-component marca="{{ config('app.name', 'Laravel') }}" username="{{ Auth::user()->name }}">
        <template v-slot:token>    
            @csrf
        </template>

        <template v-slot:contenido>
            <tabla-component></tabla-component>
        </template>

    </<home-component>
@endsection
