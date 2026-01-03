@extends('layouts.app')

@section('title', 'DEV MODE: WHITE GACOR')

@section('styles')
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* --- TRIK RAHASIA BIAR FULL SCREEN --- */
        /* Kita paksa elemen ini keluar dari layout bawaan */
        .fullscreen-overlay {
            position: fixed; /* Kunci posisi ke layar browser */
            top: 0;
            left: 0;
            width: 100vw; /* Lebar 100% viewport */
            height: 100vh; /* Tinggi 100% viewport */
            z-index: 99999; /* Pastikan di atas segalanya (Sidebar, Navbar, dll) */
            background-color: #f8fafc;
            overflow-y: auto; /* Biar bisa discroll kalau konten panjang */
        }

        /* --- BACKGROUND & DEKORASI --- */
        .white-gacor-scene {
            background-image: 
                linear-gradient(rgba(59, 130, 246, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(59, 130, 246, 0.05) 1px, transparent 1px);
            background-size: 40px 40px;
            font-family: 'Rajdhani', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .white-gacor-scene::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(circle at center, rgba(59, 130, 246, 0.05) 0%, rgba(248, 250, 252, 0.9) 80%);
            pointer-events: none;
        }

        /* --- CARD STYLE --- */
        .dev-card-wrapper {
            perspective: 1000px;
            z-index: 10;
            max-width: 900px;
            width: 90%;
            position: relative;
        }

        .dev-card {
            position: relative;
            background: rgba(255, 255, 255, 0.85);
            border-radius: 24px;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(59, 130, 246, 0.2);
            box-shadow: 
                0 25px 50px -12px rgba(59, 130, 246, 0.15),
                0 0 0 1px rgba(255, 255, 255, 0.5) inset;
            overflow: hidden;
            transform-style: preserve-3d;
            animation: cardFloat 6s ease-in-out infinite alternate;
        }

        @keyframes cardFloat {
            0% { transform: translateY(0); }
            100% { transform: translateY(-15px); }
        }

        /* --- SCANNER LINE --- */
        .dev-card::after {
            content: '';
            position: absolute;
            top: -50%; left: -50%; width: 200%; height: 200%;
            background: linear-gradient(to bottom, transparent, rgba(59, 130, 246, 0.1) 48%, rgba(59, 130, 246, 0.4) 50%, rgba(59, 130, 246, 0.1) 52%, transparent);
            transform: rotate(45deg);
            animation: scanline 4s linear infinite;
            pointer-events: none;
            opacity: 0.5;
        }

        @keyframes scanline {
            0% { transform: translateY(-100%) rotate(45deg); }
            100% { transform: translateY(100%) rotate(45deg); }
        }

        /* --- TYPOGRAPHY --- */
        .dev-title {
            font-weight: 800;
            font-size: 4.5rem;
            text-transform: uppercase;
            letter-spacing: 4px;
            color: #1e293b;
            line-height: 1;
            margin-bottom: 10px;
            text-shadow: 3px 3px 0px rgba(59, 130, 246, 0.2);
            position: relative;
            display: inline-block;
        }

        /* Efek Glitch pada Judul */
        .dev-title:hover {
            animation: glitchText 0.3s cubic-bezier(.25, .46, .45, .94) both infinite;
            color: #3b82f6;
        }

        @keyframes glitchText {
            0% { transform: translate(0); }
            20% { transform: translate(-2px, 2px); }
            40% { transform: translate(-2px, -2px); }
            60% { transform: translate(2px, 2px); }
            80% { transform: translate(2px, -2px); }
            100% { transform: translate(0); }
        }

        .highlight-role {
            color: #3b82f6;
            background: rgba(59, 130, 246, 0.1);
            padding: 2px 8px;
            border-radius: 4px;
        }

        /* --- TOMBOL --- */
        .cyber-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: #fff;
            background: #1e293b;
            padding: 14px 30px;
            margin: 10px;
            border-radius: 12px;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 1px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px -5px rgba(30, 41, 59, 0.3);
        }

        .cyber-link:hover {
            transform: translateY(-3px);
            background: #3b82f6;
            box-shadow: 0 15px 30px -5px rgba(59, 130, 246, 0.4);
            color: white;
        }

        .cyber-link.danger {
            background: white;
            color: #ef4444;
            border: 1px solid #fee2e2;
            box-shadow: 0 5px 15px -5px rgba(239, 68, 68, 0.2);
        }
        .cyber-link.danger:hover {
            background: #ef4444;
            color: white;
            border-color: #ef4444;
            box-shadow: 0 15px 30px -5px rgba(239, 68, 68, 0.3);
        }

        /* Tombol Balik ke Dashboard (Pojok Kiri Atas) */
        .back-btn {
            position: absolute;
            top: 30px;
            left: 30px;
            z-index: 20;
            background: white;
            padding: 10px 20px;
            border-radius: 50px;
            text-decoration: none;
            color: #64748b;
            font-weight: 700;
            font-size: 0.8rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
        }
        .back-btn:hover {
            background: #f1f5f9;
            color: #334155;
            transform: translateX(-3px);
        }

    </style>
@endsection

@section('content')

<div class="fullscreen-overlay">
    
    <div class="white-gacor-scene">
        
        <a href="{{ route('dashboard.admin') }}" class="back-btn">
            ← DASHBOARD UTAMA
        </a>

        <div class="dev-card-wrapper">
            <div class="dev-card">
                
                <div class="p-10 md:p-16 text-center relative z-10">
                    
                    <div style="font-size: 5rem; margin-bottom: -20px; animation: bounce 3s infinite;">
                        👨‍💻🔥
                    </div>
                    
                    <h1 class="dev-title">
                        GASSS DEVELOPER<br>GANTENG
                    </h1>
                    
                    <p style="font-size: 1.25rem; color: #64748b; font-weight: 600; margin-top: 10px;">
                        System Status: <span style="color:#ef4444;">UNSTABLE</span>. 
                        Mode: <span class="highlight-role">GOD MODE</span>
                    </p>
                    
                    <div style="width: 100px; height: 4px; background: #e2e8f0; margin: 30px auto; border-radius: 2px;"></div>

                    <p style="color: #94a3b8; font-size: 1rem; margin-bottom: 40px; max-width: 600px; margin-left: auto; margin-right: auto;">
                        "Area khusus untuk eksperimen tanpa batas. Jangan lupa balikin role sebelum dimarahin klien."
                    </p>

                    <div style="display: flex; justify-content: center; flex-wrap: wrap;">
                        <a href="{{ route('developer.status') }}" class="cyber-link">
                            🔍 Cek Status Impersonate
                        </a>
                        
                        <a href="{{ route('developer.impersonate.clear') }}" class="cyber-link danger">
                            💥 Hapus Impersonate (Normal)
                        </a>
                    </div>

                </div>

                <div style="background: #f8fafc; padding: 15px; text-align: center; border-top: 1px solid #f1f5f9;">
                    <span style="font-size: 0.75rem; color: #cbd5e1; font-weight: 700; letter-spacing: 2px;">SECURE CONNECTION // ENCRYPTED</span>
                </div>

            </div>
        </div>
    </div>

</div>

<style>
    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
</style>

@endsection