Halo {{ $pendaftar->nama }},

QR Code untuk {{ $role === 'peserta' ? 'event Anda' : 'event panitia Anda' }} sudah siap. Berikut detailnya:

Event: {{ $pendaftar->event->nama_event ?? 'Umum' }}
{{ $role === 'peserta' ? 'Kode Peserta' : 'Kode Panitia' }}: {{ $pendaftar->kode_panitia ?? $pendaftar->kode_peserta }}

QR Check-in (Datang): Attachment {{ $pendaftar->kode_panitia ?? $pendaftar->kode_peserta }}_datang.png  
QR Check-out (Pulang): Attachment {{ $pendaftar->kode_panitia ?? $pendaftar->kode_peserta }}_pulang.png

Terima kasih.
