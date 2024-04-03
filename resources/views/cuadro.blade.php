@extends('layouts.app')

@section('content')
    <cuadro-component marca="{{ config('app.name', 'Laravel') }}" username="{{ Auth::user()->name }}">
        <template v-slot:token>    
            @csrf
        </template>

        <template v-slot:contenido>
            <producto token="{{ csrf_token() }}"></producto>
        </template>

    </<cuadro-component>
@endsection
