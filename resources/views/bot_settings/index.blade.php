@extends('adminlte::page')

@section('title', 'Pengaturan Bot')

@section('content_header')
    <h1>Pengaturan Bot WhatsApp</h1>
@stop

@section('content')
    <div class="alert alert-warning">
        <b>Catatan:</b> Jangan bagikan token bot ke siapapun.
    </div>

    <div class="card">
        <div class="card-body">
            <form action="#" method="POST">
                <div class="form-group">
                    <label>Token WhatsApp API</label>
                    <input type="text" class="form-control" placeholder="Masukkan token API">
                </div>

                <div class="form-group">
                    <label>URL Webhook</label>
                    <input type="text" class="form-control" placeholder="https://domain.com/webhook">
                </div>

                <button class="btn btn-success mt-2">Simpan</button>
            </form>
        </div>
    </div>
@stop
