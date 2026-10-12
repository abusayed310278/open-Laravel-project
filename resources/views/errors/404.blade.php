@extends('layouts.app')

@section('title', '404 - Look like you\'re lost')

@section('content')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Arvo:wght@400;700&display=swap" rel="stylesheet">

    <style>
        .page_404 {
            padding: 50px 0 70px 0;
            background: #fff;
            font-family: 'Arvo', serif;
        }
        .page_404 img {
            width: 100%;
        }
        .four_zero_four_bg {
            background-image: url('{{ asset('images/404-anim.gif') }}');
            height: 400px;
            background-position: center;
            background-repeat: no-repeat;
        }
        .four_zero_four_bg h1 {
            font-size: 80px;
            font-weight: 700;
            color: #111827;
            text-align: center;
            line-height: 1;
            margin: 0;
        }
        .contant_box_404 {
            margin-top: -50px;
            text-align: center;
        }
        .contant_box_404 h3 {
            font-size: 32px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
        }
        .contant_box_404 p {
            font-size: 15px;
            color: #4b5563;
            margin-bottom: 20px;
        }
        .link_404 {
            color: #fff !important;
            padding: 12px 30px;
            background-color: var(--brand-primary, #f59e0b);
            margin: 15px 0 0 0;
            display: inline-block;
            border-radius: 8px;
            font-weight: 700;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.2s ease-in-out;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .link_404:hover {
            opacity: 0.92;
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
        }
        @media (max-width: 640px) {
            .four_zero_four_bg {
                height: 300px;
                background-size: contain;
            }
            .four_zero_four_bg h1 {
                font-size: 60px;
            }
            .contant_box_404 {
                margin-top: -20px;
            }
            .contant_box_404 h3 {
                font-size: 24px;
            }
        }
    </style>

    <section class="page_404 min-h-[70vh] flex items-center justify-center">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 w-full">
            <div class="text-center">
                <div class="four_zero_four_bg flex items-start justify-center pt-2">
                    <h1 class="text-center">404</h1>
                </div>

                <div class="contant_box_404">
                    <h3>Look like you're lost</h3>
                    <p>the page you are looking for not avaible!</p>
                    <a href="{{ route('home') }}" class="link_404">Go to Home</a>
                </div>
            </div>
        </div>
    </section>
@endsection
