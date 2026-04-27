<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Profil')
                    ->description('Data dasar identitas pengguna aplikasi.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Gunakan nama asli untuk keperluan identifikasi sistem.'),

                        TextInput::make('email')
                            ->label('Alamat Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText('Email akan digunakan sebagai kredensial login utama.'),

                        DateTimePicker::make('email_verified_at')
                            ->label('Email Terverifikasi Pada')
                            ->placeholder('Belum Terverifikasi')
                            ->helperText('Biarkan kosong jika ingin memaksa pengguna melakukan verifikasi email manual.'),

                        TextInput::make('password')
                            ->label('Kata Sandi')
                            ->password()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                            ->helperText('Minimal 8 karakter. Kosongkan jika tidak ingin mengubah kata sandi saat ini.'),
                    ]),

                Section::make('Keamanan & Perizinan')
                    ->description('Pengaturan hak akses dan otentikasi dua faktor (2FA).')
                    ->columns(2)
                    ->schema([
                        Select::make('roles')
                            ->label('Peran (Roles)')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->helperText('Tentukan satu atau lebih hak akses untuk pengguna ini.'),

                        DateTimePicker::make('two_factor_confirmed_at')
                            ->label('2FA Dikonfirmasi Pada')
                            ->disabled()
                            ->helperText('Tanggal saat pengguna berhasil mengaktifkan Two Factor Authentication.'),

                        Textarea::make('two_factor_secret')
                            ->label('2FA Secret Key')
                            ->disabled()
                            ->columnSpanFull()
                            ->helperText('Kunci rahasia enkripsi untuk aplikasi otentikator (Google/Microsoft Authenticator).'),

                        Textarea::make('two_factor_recovery_codes')
                            ->label('Kode Pemulihan 2FA')
                            ->disabled()
                            ->columnSpanFull()
                            ->helperText('Kumpulan kode cadangan untuk masuk jika perangkat utama hilang.'),
                    ]),
            ]);
    }
}
