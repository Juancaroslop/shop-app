@extends('adminlte::page')

@section('title', 'Marcas')

@section('content_header')
    <h1>Lista de Menu</h1>
@stop

@section('content')
    <div id="app">
        <marca-component></marca-component>
    </div>
@stop

@section('css')
    <!-- <link rel="stylesheet" href="/css/admin_custom.css"> -->
    @vite('resources/css/app.css')
@stop

@section('js')
    <!-- <script> console.log('Hi!'); </script> -->
    @vite('resources/js/app.js')
@stop