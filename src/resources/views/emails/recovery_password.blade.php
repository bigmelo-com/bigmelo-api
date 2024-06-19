@extends('emails.email_layout')
@section('content')
    <p class="description">
        ¡Haciendo click en el siguiente boton vas a poder recuperar tu contraseña!
    </p>
    <a href={{ $link }} class="button">
        Restablecer Contraseña
    </a>
@endsection
