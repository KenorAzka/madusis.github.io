@extends('layouts.app')

@section('content')
<div style="display: flex; justify-content: center; align-items: center; height: 80vh;">
    <div style="background: #fff; padding: 30px; border-radius: 12px; border: 1px solid var(--border-color); max-width: 400px; width: 100%; text-align: center;">
        <h3 style="margin-bottom: 15px; color: #1e293b;">Verifikasi Alamat Email Anda</h3>

        @if (session('message'))
            <div style="background: #dcfce7; color: #16a34a; padding: 10px; border-radius: 6px; font-size: 12px; margin-bottom: 15px;">
                Tautan verifikasi baru telah dikirim ke alamat email Anda.
            </div>
        @endif

        <p style="font-size: 13px; color: #64748b; margin-bottom: 20px;">
            Sebelum melanjutkan, mohon periksa email Anda untuk tautan verifikasi. Jika Anda tidak menerima email tersebut, silakan klik tombol di bawah untuk meminta ulang.
        </p>

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" style="width: 100%; padding: 10px; background: #1c2a3a; color: #fff; border: none; border-radius: 8px; font-weight: 700; cursor: pointer;">
                Kirim Ulang Email Verifikasi
            </button>
        </form>
    </div>
</div>
@endsection