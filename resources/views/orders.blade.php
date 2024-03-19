@extends('layouts.app')

@section('title', 'Ordenes de cliente')

@section('content_header')
    <h1>Gestión de ordenes</h1>
@stop

@section('content')
    <div id="app">
        <ordenes-cliente :user="{{Auth::user() != null ? Auth::user() : json_encode($user = array())}}"></ordenes-cliente>
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