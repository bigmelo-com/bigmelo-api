@extends('emails.email_layout')
@section('content')
  <h2>¡Gracias por tu reporte!</h2>
  <p>¡Hola! Desde Bigmelo queremos agradecer tu reporte.</p>
  <p>Nos contactaremos contigo a través del correo suministrado en el formulario y/o el correo vinculado a tu cuenta.</p>
  <br />
  <h4>Tu mensaje para Bigmelo:</h4>
  <p>{{ $support_message }}</p>
@endsection
