<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QrCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pendaftar;   
    public $qrDatang;
    public $qrPulang;
    public $role;         

    public function __construct($pendaftar, $qrDatang, $qrPulang, $role = 'peserta')
    {
        $this->pendaftar = $pendaftar;
        $this->qrDatang = $qrDatang;
        $this->qrPulang = $qrPulang;
        $this->role = $role;
    }

    public function build(): self
    {
        // Tentukan kode untuk file attachment berdasarkan role
        $kode = $this->role === 'panitia' ? $this->pendaftar->kode_panitia : $this->pendaftar->kode_peserta;

        return $this->subject('QR Code Event Anda')
                    ->text('emails.qrcode_plain_dual')
                    ->attach($this->qrDatang, [
                        'as' => $kode . '_datang.png',
                        'mime' => 'image/png',
                    ])
                    ->attach($this->qrPulang, [
                        'as' => $kode . '_pulang.png',
                        'mime' => 'image/png',
                    ]);
    }
}
