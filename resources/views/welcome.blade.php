<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width,initial-scale=1" />
        <meta name="theme-color" content="#071522" />
        <meta
            name="description"
            content="JM Academy, kelas coding dan robotik kreatif untuk anak dan remaja."
        />
        <title>JM Academy — Coding & Robotics</title>
        <link rel="preconnect" href="https://fonts.bunny.net" />
        <link
            href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap"
            rel="stylesheet"
        />
        <style>
            :root {
                --dark: #071522;
                --ink: #10283c;
                --muted: #687989;
                --blue: #1687ff;
                --cyan: #18d7ce;
                --lime: #bbed54;
                --soft: #f3f8fc;
                --line: #dce8f0;
            }
            * {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }
            html {
                scroll-behavior: smooth;
            }
            body {
                font-family: "Plus Jakarta Sans", sans-serif;
                color: var(--ink);
                overflow-x: hidden;
            }
            a {
                text-decoration: none;
                color: inherit;
            }
            button {
                font: inherit;
            }
            .container {
                width: min(1160px, calc(100% - 40px));
                margin: auto;
            }
            .nav {
                position: absolute;
                z-index: 20;
                width: 100%;
                height: 88px;
                color: #fff;
            }
            .nav-in {
                height: 100%;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }
            .logo {
                display: flex;
                align-items: center;
                gap: 10px;
                font-weight: 800;
                font-size: 19px;
                letter-spacing: -0.04em;
            }
            .mark {
                width: 40px;
                height: 40px;
                border-radius: 13px;
                background: linear-gradient(135deg, var(--blue), var(--cyan));
                display: grid;
                place-items: center;
                box-shadow: 0 10px 30px #1687ff55;
            }
            .mark svg {
                width: 25px;
            }
            .logo small {
                display: block;
                font-size: 7px;
                letter-spacing: 0.2em;
                color: #8ba5b9;
                margin-top: 2px;
            }
            .links {
                display: flex;
                align-items: center;
                gap: 24px;
                color: #b9cad7;
                font-size: 13px;
                font-weight: 600;
            }
            .links a:hover {
                color: #fff;
            }
            .auth-links {
                display: flex;
                align-items: center;
                gap: 9px;
            }
            .login-link {
                padding: 11px 14px;
                color: #fff !important;
            }
            .register-link {
                padding: 12px 18px;
                border-radius: 12px;
                background: linear-gradient(135deg, var(--blue), #1ab6e6);
                color: #fff !important;
                box-shadow: 0 9px 24px #1687ff42;
            }
            .nav-cta {
                padding: 12px 18px;
                border: 1px solid #ffffff2b;
                background: #ffffff0d;
                border-radius: 12px;
                color: #fff !important;
            }
            .menu {
                display: none;
                color: white;
                background: none;
                border: 0;
                font-size: 25px;
            }
            .hero {
                min-height: 760px;
                padding: 175px 0 90px;
                position: relative;
                overflow: hidden;
                color: #fff;
                background:
                    radial-gradient(
                        circle at 77% 43%,
                        #1687ff30,
                        transparent 25%
                    ),
                    radial-gradient(
                        circle at 20% 20%,
                        #18d7ce13,
                        transparent 25%
                    ),
                    var(--dark);
            }
            .hero:before {
                content: "";
                position: absolute;
                inset: 0;
                background-image:
                    linear-gradient(#7fbae00b 1px, transparent 1px),
                    linear-gradient(90deg, #7fbae00b 1px, transparent 1px);
                background-size: 54px 54px;
                mask-image: linear-gradient(#000, transparent);
            }
            .hero-grid {
                position: relative;
                z-index: 2;
                display: grid;
                grid-template-columns: 1.05fr 0.95fr;
                gap: 45px;
                align-items: center;
            }
            .eyebrow {
                display: inline-flex;
                align-items: center;
                gap: 9px;
                padding: 8px 13px;
                border-radius: 99px;
                border: 1px solid #1687ff30;
                background: #eff8ff;
                color: #0874da;
                font-size: 11px;
                font-weight: 800;
                letter-spacing: 0.1em;
                text-transform: uppercase;
            }
            .eyebrow i {
                width: 7px;
                height: 7px;
                border-radius: 50%;
                background: var(--cyan);
                box-shadow: 0 0 0 5px #18d7ce1c;
            }
            .hero .eyebrow {
                background: #18d7ce12;
                border-color: #18d7ce30;
                color: #6fe6e1;
            }
            h1 {
                font-size: clamp(48px, 5.6vw, 78px);
                line-height: 1.04;
                letter-spacing: -0.06em;
                margin: 23px 0;
            }
            .gradient {
                background: linear-gradient(
                    100deg,
                    #57b7ff,
                    #45e0d8 50%,
                    #c6f66c
                );
                -webkit-background-clip: text;
                color: transparent;
            }
            .hero-copy > p {
                max-width: 590px;
                color: #a7bac9;
                line-height: 1.8;
                font-size: 17px;
            }
            .actions {
                display: flex;
                gap: 15px;
                margin-top: 33px;
            }
            .btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 16px 22px;
                border-radius: 13px;
                font-size: 14px;
                font-weight: 800;
                transition: 0.25s;
            }
            .primary {
                background: linear-gradient(135deg, var(--blue), #1ab6e6);
                box-shadow: 0 14px 30px #1687ff45;
            }
            .primary:hover {
                transform: translateY(-3px);
            }
            .ghost {
                border: 1px solid #ffffff29;
                color: #d8e4ed;
            }
            .stats {
                display: flex;
                gap: 26px;
                margin-top: 43px;
            }
            .stats strong {
                display: block;
                font-size: 21px;
            }
            .stats span {
                font-size: 11px;
                color: #7790a4;
            }
            .divider {
                width: 1px;
                background: #263b4d;
            }
            .visual {
                height: 500px;
                position: relative;
            }
            .glow {
                position: absolute;
                width: 330px;
                height: 330px;
                left: 50%;
                top: 50%;
                transform: translate(-50%, -50%);
                border-radius: 50%;
                background: #1687ff20;
                box-shadow: 0 0 100px #18d7ce20;
            }
            .orbit {
                position: absolute;
                inset: 45px;
                border: 1px dashed #66b8f32e;
                border-radius: 50%;
                animation: spin 24s linear infinite;
            }
            .orbit:after,
            .orbit:before {
                content: "";
                position: absolute;
                width: 10px;
                height: 10px;
                border-radius: 50%;
                background: var(--cyan);
                box-shadow: 0 0 18px var(--cyan);
            }
            .orbit:before {
                top: 17%;
                left: 5%;
            }
            .orbit:after {
                right: 17%;
                bottom: 7%;
                background: var(--lime);
            }
            .bot {
                position: absolute;
                width: 245px;
                height: 315px;
                left: 50%;
                top: 49%;
                transform: translate(-50%, -50%);
                animation: float 4s ease-in-out infinite;
            }
            .antenna {
                position: absolute;
                left: 120px;
                top: 0;
                width: 5px;
                height: 45px;
                border-radius: 5px;
                background: #5f7d91;
            }
            .antenna:after {
                content: "";
                position: absolute;
                left: -5px;
                top: -5px;
                width: 15px;
                height: 15px;
                border-radius: 50%;
                background: var(--cyan);
                box-shadow: 0 0 18px var(--cyan);
            }
            .head {
                position: absolute;
                left: 30px;
                top: 39px;
                width: 184px;
                height: 132px;
                border: 3px solid #40718f;
                border-radius: 46px;
                background: linear-gradient(145deg, #e8faff, #86b3c8);
                box-shadow:
                    inset -10px -12px 20px #133e5433,
                    0 20px 50px #0007;
            }
            .face {
                position: absolute;
                inset: 21px 17px;
                border-radius: 28px;
                background: #081b29;
            }
            .eye {
                position: absolute;
                top: 28px;
                width: 25px;
                height: 16px;
                border-radius: 50%;
                background: var(--cyan);
                box-shadow: 0 0 17px var(--cyan);
                animation: blink 4s infinite;
            }
            .eye.l {
                left: 29px;
            }
            .eye.r {
                right: 29px;
            }
            .mouth {
                position: absolute;
                left: 50%;
                bottom: 19px;
                width: 46px;
                height: 17px;
                transform: translateX(-50%);
                border-bottom: 4px solid var(--cyan);
                border-radius: 50%;
            }
            .ear {
                position: absolute;
                top: 82px;
                width: 24px;
                height: 44px;
                background: #4c7d96;
                border: 3px solid #78a9bd;
            }
            .ear.l {
                left: 9px;
                border-radius: 12px 0 0 12px;
            }
            .ear.r {
                right: 8px;
                border-radius: 0 12px 12px 0;
            }
            .body {
                position: absolute;
                left: 53px;
                top: 179px;
                width: 139px;
                height: 105px;
                border: 3px solid #3e718d;
                border-radius: 32px 32px 45px 45px;
                background: linear-gradient(145deg, #d9f5ff, #75a5bd);
                box-shadow:
                    inset -8px -10px 18px #133e5438,
                    0 20px 35px #0005;
            }
            .chest {
                position: absolute;
                inset: 22px 31px;
                border-radius: 15px;
                background: #0b2434;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 7px;
            }
            .chest i {
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background: var(--cyan);
                animation: pulse 1.5s infinite;
            }
            .chest i:nth-child(2) {
                background: var(--lime);
                animation-delay: 0.3s;
            }
            .chest i:nth-child(3) {
                background: var(--blue);
                animation-delay: 0.6s;
            }
            .arm {
                position: absolute;
                top: 189px;
                width: 24px;
                height: 92px;
                border: 3px solid #3e718d;
                border-radius: 15px;
                background: #79a9bf;
                transform-origin: top;
            }
            .arm.l {
                left: 27px;
                animation: wave 3s ease-in-out infinite;
            }
            .arm.r {
                right: 25px;
                transform: rotate(-16deg);
            }
            .bot-shadow {
                position: absolute;
                width: 185px;
                height: 25px;
                left: 50%;
                bottom: 54px;
                transform: translateX(-50%);
                border-radius: 50%;
                filter: blur(12px);
                background: #0007;
                animation: shade 4s ease-in-out infinite;
            }
            .code {
                position: absolute;
                z-index: 5;
                width: 225px;
                padding: 13px;
                border: 1px solid #4f9ac747;
                border-radius: 15px;
                background: #071420dc;
                box-shadow: 0 22px 40px #0005;
                backdrop-filter: blur(12px);
                font: 10px/1.8 monospace;
                color: #89a4b8;
            }
            .code.one {
                right: -15px;
                top: 60px;
                animation: smallfloat 5s infinite;
            }
            .code.two {
                left: -5px;
                bottom: 64px;
                animation: smallfloat 5s 1s infinite;
            }
            .dots {
                display: flex;
                gap: 5px;
                margin-bottom: 8px;
            }
            .dots i {
                width: 6px;
                height: 6px;
                border-radius: 50%;
                background: #ff6b75;
            }
            .dots i:nth-child(2) {
                background: #ffd45c;
            }
            .dots i:nth-child(3) {
                background: #57e48c;
            }
            .pink {
                color: #f27bc4;
            }
            .blue {
                color: #60b8ff;
            }
            .green {
                color: #76e6a7;
            }
            .yellow {
                color: #f0d468;
            }
            .cursor {
                display: inline-block;
                width: 6px;
                height: 12px;
                background: var(--cyan);
                animation: cursor 0.8s infinite;
            }
            section {
                padding: 100px 0;
            }
            .section-head {
                text-align: center;
                max-width: 700px;
                margin: 0 auto 52px;
            }
            .section-head h2,
            .why h2 {
                font-size: clamp(34px, 4vw, 50px);
                line-height: 1.15;
                letter-spacing: -0.05em;
                margin: 17px 0;
            }
            .section-head p,
            .why > p {
                color: var(--muted);
                line-height: 1.75;
            }
            .programs {
                background: var(--soft);
            }
            .cards {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 22px;
            }
            .card {
                --color: var(--blue);
                position: relative;
                overflow: hidden;
                padding: 31px;
                border: 1px solid var(--line);
                border-radius: 24px;
                background: #fff;
                transition: 0.3s;
                box-shadow: 0 8px 30px #143c5a0b;
            }
            .card:hover {
                transform: translateY(-8px);
                box-shadow: 0 24px 60px #07213a1e;
                border-color: transparent;
            }
            .card:after {
                content: "";
                position: absolute;
                width: 120px;
                height: 120px;
                right: -55px;
                top: -55px;
                border-radius: 50%;
                background: var(--color);
                opacity: 0.1;
            }
            .icon {
                width: 54px;
                height: 54px;
                border-radius: 16px;
                background: color-mix(in srgb, var(--color) 12%, white);
                color: var(--color);
                display: grid;
                place-items: center;
                margin-bottom: 23px;
            }
            .icon svg {
                width: 28px;
            }
            .card h3 {
                font-size: 19px;
                margin-bottom: 11px;
            }
            .card p {
                font-size: 13px;
                color: var(--muted);
                line-height: 1.75;
                min-height: 70px;
            }
            .meta {
                display: flex;
                justify-content: space-between;
                margin-top: 23px;
                padding-top: 19px;
                border-top: 1px solid #edf2f6;
                font-size: 11px;
                font-weight: 700;
            }
            .meta span {
                color: var(--muted);
            }
            .meta a {
                color: var(--color);
            }
            .why-grid {
                display: grid;
                grid-template-columns: 0.9fr 1.1fr;
                gap: 85px;
                align-items: center;
            }
            .lab {
                position: relative;
                min-height: 470px;
                border-radius: 30px;
                overflow: hidden;
                background:
                    radial-gradient(
                        circle at 75% 35%,
                        #18d7ce32,
                        transparent 30%
                    ),
                    #0a1d2e;
                box-shadow: 0 24px 70px #07213a25;
            }
            .laptop {
                position: absolute;
                left: 11%;
                right: 11%;
                top: 18%;
                height: 245px;
                padding: 26px 23px;
                border: 8px solid #587384;
                border-radius: 18px;
                background: #07131e;
                color: #8da9bb;
                box-shadow: 0 30px 60px #0006;
                font: 12px/2 monospace;
                transform: perspective(800px) rotateY(-5deg);
            }
            .laptop:after {
                content: "";
                position: absolute;
                width: 116%;
                height: 18px;
                left: -8%;
                bottom: -23px;
                border-radius: 3px 3px 14px 14px;
                background: linear-gradient(#7892a1, #3f5868);
            }
            .done {
                position: absolute;
                right: 4%;
                bottom: 9%;
                padding: 16px;
                border-radius: 15px;
                background: #fff;
                box-shadow: 0 20px 50px #0004;
                font-size: 11px;
                font-weight: 800;
                animation: smallfloat 4s infinite;
            }
            .done span {
                color: #0fb9aa;
            }
            .features {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 23px;
                margin-top: 33px;
            }
            .feature {
                display: flex;
                gap: 12px;
            }
            .check {
                flex: 0 0 27px;
                height: 27px;
                border-radius: 9px;
                background: #e7fbf8;
                color: #0fb9aa;
                display: grid;
                place-items: center;
                font-weight: 900;
            }
            .feature h4 {
                font-size: 13px;
                margin-bottom: 5px;
            }
            .feature p {
                font-size: 11px;
                color: var(--muted);
                line-height: 1.6;
            }
            .steps {
                background: var(--dark);
                color: #fff;
            }
            .steps .section-head p {
                color: #8aa1b4;
            }
            .step-row {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 20px;
                position: relative;
            }
            .step-row:before {
                content: "";
                position: absolute;
                height: 1px;
                left: 12%;
                right: 12%;
                top: 34px;
                background: linear-gradient(
                    90deg,
                    var(--blue),
                    var(--cyan),
                    var(--lime)
                );
            }
            .step {
                text-align: center;
                position: relative;
            }
            .num {
                position: relative;
                z-index: 2;
                width: 68px;
                height: 68px;
                margin: 0 auto 22px;
                border: 1px solid #24465e;
                border-radius: 21px;
                background: #0d273a;
                box-shadow: 0 0 0 8px var(--dark);
                display: grid;
                place-items: center;
                color: var(--cyan);
                font-weight: 800;
            }
            .step h3 {
                font-size: 15px;
                margin-bottom: 8px;
            }
            .step p {
                padding: 0 10px;
                color: #8298a9;
                font-size: 11px;
                line-height: 1.7;
            }
            .cta-sec {
                padding: 80px 0;
            }
            .cta {
                padding: 64px 68px;
                border-radius: 30px;
                color: #fff;
                overflow: hidden;
                position: relative;
                background: linear-gradient(120deg, #0975e7, #0db4c5);
            }
            .cta:after {
                content: "";
                position: absolute;
                width: 370px;
                height: 370px;
                right: -70px;
                top: -190px;
                border: 1px solid #ffffff2c;
                border-radius: 50%;
            }
            .cta-in {
                position: relative;
                z-index: 2;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 30px;
            }
            .cta h2 {
                font-size: clamp(30px, 4vw, 45px);
                letter-spacing: -0.05em;
            }
            .cta p {
                color: #d8f3fa;
                margin-top: 10px;
            }
            .cta .btn {
                background: white;
                color: #0874da;
                white-space: nowrap;
            }
            footer {
                padding: 33px 0;
                border-top: 1px solid var(--line);
            }
            .footer {
                display: flex;
                align-items: center;
                justify-content: space-between;
                color: #788795;
                font-size: 11px;
            }
            .footer .logo {
                color: var(--ink);
            }
            .footlinks {
                display: flex;
                gap: 23px;
            }
            .reveal {
                opacity: 0;
                transform: translateY(24px);
                transition: 0.7s ease;
            }
            .reveal.visible {
                opacity: 1;
                transform: none;
            }
            @keyframes float {
                50% {
                    transform: translate(-50%, -55%);
                }
            }
            @keyframes shade {
                50% {
                    transform: translateX(-50%) scale(0.8);
                    opacity: 0.4;
                }
            }
            @keyframes smallfloat {
                50% {
                    transform: translateY(-13px);
                }
            }
            @keyframes spin {
                to {
                    transform: rotate(360deg);
                }
            }
            @keyframes blink {
                0%,
                44%,
                48%,
                100% {
                    transform: scaleY(1);
                }
                46% {
                    transform: scaleY(0.08);
                }
            }
            @keyframes pulse {
                50% {
                    opacity: 0.3;
                    transform: scale(0.75);
                }
            }
            @keyframes wave {
                0%,
                100% {
                    transform: rotate(16deg);
                }
                50% {
                    transform: rotate(42deg);
                }
            }
            @keyframes cursor {
                50% {
                    opacity: 0;
                }
            }
            @media (max-width: 900px) {
                .links {
                    display: none;
                    position: absolute;
                    top: 76px;
                    left: 20px;
                    right: 20px;
                    padding: 22px;
                    flex-direction: column;
                    align-items: stretch;
                    background: #102637;
                    border: 1px solid #28475d;
                    border-radius: 16px;
                }
                .links.open {
                    display: flex;
                }
                .menu {
                    display: block;
                }
                .hero {
                    padding-top: 145px;
                }
                .hero-grid {
                    grid-template-columns: 1fr;
                }
                .hero-copy {
                    text-align: center;
                }
                .hero-copy > p {
                    margin: auto;
                }
                .actions,
                .stats {
                    justify-content: center;
                }
                .visual {
                    height: 470px;
                }
                .cards {
                    grid-template-columns: 1fr 1fr;
                }
                .card:last-child {
                    grid-column: 1/-1;
                }
                .why-grid {
                    grid-template-columns: 1fr;
                    gap: 50px;
                }
                .lab {
                    order: 2;
                }
                .step-row {
                    grid-template-columns: 1fr 1fr;
                    gap: 45px;
                }
                .step-row:before {
                    display: none;
                }
                .cta-in {
                    display: block;
                }
                .cta .btn {
                    margin-top: 27px;
                }
            }
            @media (max-width: 580px) {
                .container {
                    width: calc(100% - 28px);
                }
                .hero {
                    padding: 130px 0 60px;
                }
                h1 {
                    font-size: 43px;
                }
                .actions {
                    flex-direction: column;
                }
                .stats {
                    gap: 14px;
                }
                .stats strong {
                    font-size: 17px;
                }
                .visual {
                    height: 415px;
                }
                .bot {
                    transform: translate(-50%, -50%) scale(0.82);
                }
                .orbit {
                    inset: 45px 5px;
                }
                .code {
                    width: 180px;
                    font-size: 8px;
                }
                .code.one {
                    right: -9px;
                }
                .code.two {
                    left: -9px;
                }
                .cards,
                .features,
                .step-row {
                    grid-template-columns: 1fr;
                }
                .card:last-child {
                    grid-column: auto;
                }
                section {
                    padding: 74px 0;
                }
                .lab {
                    min-height: 390px;
                }
                .laptop {
                    left: 6%;
                    right: 6%;
                    font-size: 9px;
                    padding: 20px 13px;
                }
                .cta {
                    padding: 43px 27px;
                }
                .footer {
                    flex-direction: column;
                    text-align: center;
                    gap: 21px;
                }
            }
            @media (prefers-reduced-motion: reduce) {
                html {
                    scroll-behavior: auto;
                }
                *,
                *:before,
                *:after {
                    animation: none !important;
                    transition: none !important;
                }
                .reveal {
                    opacity: 1;
                    transform: none;
                }
            }
        </style>
    </head>
    <body>
        <nav class="nav">
            <div class="container nav-in">
                <a class="logo" href="#home"
                    ><span class="mark"
                        ><svg viewBox="0 0 24 24" fill="none">
                            <path
                                d="M5 8h14v10H5z"
                                stroke="white"
                                stroke-width="1.8"
                            />
                            <circle cx="9" cy="12" r="1.3" fill="white" />
                            <circle cx="15" cy="12" r="1.3" fill="white" />
                            <path
                                d="M9 15h6M12 8V5m-2 0h4"
                                stroke="white"
                                stroke-width="1.8"
                                stroke-linecap="round"
                            /></svg></span
                    ><span
                        >JM Academy<small>CODE • CREATE • INNOVATE</small></span
                    ></a
                ><button
                    class="menu"
                    aria-label="Buka menu"
                    aria-expanded="false"
                >
                    ☰
                </button>
                <div class="links">
                    <a href="#program">Program</a
                    ><a href="#keunggulan">Keunggulan</a
                    ><a href="#cara">Cara Belajar</a>
                    <div class="auth-links">
                        <a class="login-link" href="{{ route('login') }}"
                            >Login</a
                        ><a class="register-link" href="{{ route('register') }}"
                            >Register</a
                        >
                    </div>
                </div>
            </div>
        </nav>
        <main>
            <section class="hero" id="home">
                <div class="container hero-grid">
                    <div class="hero-copy">
                        <span class="eyebrow"
                            ><i></i>Future skills start here</span
                        >
                        <h1>
                            Build the future,<br /><span class="gradient"
                                >one line at a time.</span
                            >
                        </h1>
                        <p>
                            Tempat anak dan remaja mengubah rasa ingin tahu
                            menjadi karya nyata melalui coding, robotik, dan
                            teknologi kreatif.
                        </p>
                        <div class="actions">
                            <a class="btn primary" href="#program"
                                >Jelajahi Program &nbsp;→</a
                            ><a class="btn ghost" href="#cara"
                                >▶ &nbsp; Lihat Cara Belajar</a
                            >
                        </div>
                        <div class="stats">
                            <div>
                                <strong>500+</strong><span>Siswa aktif</span>
                            </div>
                            <i class="divider"></i>
                            <div>
                                <strong>25+</strong><span>Mentor ahli</span>
                            </div>
                            <i class="divider"></i>
                            <div>
                                <strong>4.9/5</strong
                                ><span>Rating orang tua</span>
                            </div>
                        </div>
                    </div>
                    <div
                        class="visual"
                        aria-label="Robot animasi sedang belajar coding"
                    >
                        <div class="glow"></div>
                        <div class="orbit"></div>
                        <div class="bot-shadow"></div>
                        <div class="bot">
                            <div class="antenna"></div>
                            <div class="ear l"></div>
                            <div class="ear r"></div>
                            <div class="head">
                                <div class="face">
                                    <i class="eye l"></i><i class="eye r"></i
                                    ><i class="mouth"></i>
                                </div>
                            </div>
                            <div class="arm l"></div>
                            <div class="arm r"></div>
                            <div class="body">
                                <div class="chest"><i></i><i></i><i></i></div>
                            </div>
                        </div>
                        <div class="code one">
                            <div class="dots"><i></i><i></i><i></i></div>
                            <span class="pink">function</span>
                            <span class="blue">buildFuture</span>() {<br />&nbsp;
                            <span class="pink">return</span>
                            <span class="green">"awesome!"</span>;<br />}
                            <span class="cursor"></span>
                        </div>
                        <div class="code two">
                            <div class="dots"><i></i><i></i><i></i></div>
                            <span class="blue">robot</span>.<span class="yellow"
                                >move</span
                            >(<span class="green">100</span>);<br /><span
                                class="blue"
                                >robot</span
                            >.<span class="yellow">say</span>(<span
                                class="green"
                                >"Hello!"</span
                            >);
                        </div>
                    </div>
                </div>
            </section>
            <section class="programs" id="program">
                <div class="container">
                    <div class="section-head reveal">
                        <span class="eyebrow"><i></i>Program belajar</span>
                        <h2>Pilih petualangan<br />teknologimu</h2>
                        <p>
                            Kurikulum berbasis proyek untuk mengasah logika,
                            kreativitas, dan kemampuan memecahkan masalah.
                        </p>
                    </div>
                    <div class="cards">
                        <article class="card reveal">
                            <div class="icon">
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path
                                        d="m8 9-4 3 4 3m8-6 4 3-4 3m-3-9-2 12"
                                    />
                                </svg>
                            </div>
                            <h3>Creative Coding</h3>
                            <p>
                                Belajar logika dan membuat game, animasi, serta
                                aplikasi seru dari nol.
                            </p>
                            <div class="meta">
                                <span>Usia 7–16 tahun</span
                                ><a href="#daftar">Pelajari →</a>
                            </div>
                        </article>
                        <article class="card reveal" style="--color: #10bda9">
                            <div class="icon">
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <rect
                                        x="5"
                                        y="7"
                                        width="14"
                                        height="11"
                                        rx="3"
                                    />
                                    <path
                                        d="M12 7V4m-2 0h4M8 12h.01M16 12h.01M9 15h6"
                                    />
                                </svg>
                            </div>
                            <h3>Robotics Lab</h3>
                            <p>
                                Merakit, memprogram, dan menghidupkan robot
                                sambil memahami dunia engineering.
                            </p>
                            <div class="meta">
                                <span>Usia 8–17 tahun</span
                                ><a href="#daftar">Pelajari →</a>
                            </div>
                        </article>
                        <article class="card reveal" style="--color: #8b5cf6">
                            <div class="icon">
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path
                                        d="M8 4h8l3 5-7 11L5 9l3-5Zm-3 5 7 3 7-3M8 4l4 8 4-8"
                                    />
                                </svg>
                            </div>
                            <h3>AI & Digital Creator</h3>
                            <p>
                                Eksplorasi kecerdasan buatan dan ciptakan karya
                                digital yang relevan dengan masa depan.
                            </p>
                            <div class="meta">
                                <span>Usia 12–18 tahun</span
                                ><a href="#daftar">Pelajari →</a>
                            </div>
                        </article>
                    </div>
                </div>
            </section>
            <section id="keunggulan">
                <div class="container why-grid">
                    <div class="lab reveal">
                        <div class="laptop">
                            <span class="pink">const</span> future = {<br />&nbsp;skills:
                            [<span class="green">'coding'</span>,
                            <span class="green">'robotics'</span
                            >],<br />&nbsp;mindset:
                            <span class="green">'creator'</span
                            >,<br />&nbsp;potential:
                            <span class="blue">Infinity</span
                            ><br />};<br /><span class="blue">create</span
                            >(future); <span class="cursor"></span>
                        </div>
                        <div class="done">
                            <span>✓</span> Project completed!
                        </div>
                    </div>
                    <div class="why reveal">
                        <span class="eyebrow"><i></i>Kenapa JM Academy?</span>
                        <h2>
                            Bukan cuma belajar.<br />Anak akan
                            <span class="gradient">mencipta.</span>
                        </h2>
                        <p>
                            Teknologi paling baik dipelajari dengan praktik
                            langsung. Setiap sesi adalah ruang aman untuk
                            bereksperimen, mencoba lagi, dan bangga pada karya
                            sendiri.
                        </p>
                        <div class="features">
                            <div class="feature">
                                <span class="check">✓</span>
                                <div>
                                    <h4>Berbasis proyek</h4>
                                    <p>
                                        Setiap level menghasilkan karya nyata.
                                    </p>
                                </div>
                            </div>
                            <div class="feature">
                                <span class="check">✓</span>
                                <div>
                                    <h4>Mentor berpengalaman</h4>
                                    <p>Pendampingan hangat di kelas kecil.</p>
                                </div>
                            </div>
                            <div class="feature">
                                <span class="check">✓</span>
                                <div>
                                    <h4>Sesuai level</h4>
                                    <p>Materi bertahap sesuai kemampuan.</p>
                                </div>
                            </div>
                            <div class="feature">
                                <span class="check">✓</span>
                                <div>
                                    <h4>Komunitas kreator</h4>
                                    <p>Lingkungan positif untuk bertumbuh.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <section class="steps" id="cara">
                <div class="container">
                    <div class="section-head reveal">
                        <span class="eyebrow"><i></i>Cara belajar</span>
                        <h2>Dari penasaran<br />menjadi pencipta</h2>
                        <p>
                            Perjalanan belajar yang seru, terarah, dan penuh
                            pencapaian.
                        </p>
                    </div>
                    <div class="step-row">
                        <div class="step reveal">
                            <div class="num">01</div>
                            <h3>Temukan minat</h3>
                            <p>
                                Konsultasi dan trial class untuk menemukan
                                program terbaik.
                            </p>
                        </div>
                        <div class="step reveal">
                            <div class="num">02</div>
                            <h3>Belajar konsep</h3>
                            <p>
                                Materi interaktif yang dekat dengan dunia anak.
                            </p>
                        </div>
                        <div class="step reveal">
                            <div class="num">03</div>
                            <h3>Buat proyek</h3>
                            <p>Praktik membuat game, aplikasi, atau robot.</p>
                        </div>
                        <div class="step reveal">
                            <div class="num">04</div>
                            <h3>Showcase karya</h3>
                            <p>
                                Presentasikan hasil dan rayakan setiap kemajuan.
                            </p>
                        </div>
                    </div>
                </div>
            </section>
            <section class="cta-sec" id="daftar">
                <div class="container">
                    <div class="cta reveal">
                        <div class="cta-in">
                            <div>
                                <h2>Siap menciptakan masa depan?</h2>
                                <p>
                                    Ikuti kelas percobaan gratis dan temukan
                                    potensi terbaik anak.
                                </p>
                            </div>
                            <a
                                class="btn"
                                href="https://wa.me/6281234567890?text=Halo%20JM%20Academy%2C%20saya%20ingin%20mendaftar%20trial%20class"
                                >Daftar Trial Gratis →</a
                            >
                        </div>
                    </div>
                </div>
            </section>
        </main>
        <footer id="kontak">
            <div class="container footer">
                <a class="logo" href="#home"
                    ><span class="mark"
                        ><svg viewBox="0 0 24 24" fill="none">
                            <path
                                d="M5 8h14v10H5z"
                                stroke="white"
                                stroke-width="1.8"
                            />
                            <circle cx="9" cy="12" r="1.3" fill="white" />
                            <circle
                                cx="15"
                                cy="12"
                                r="1.3"
                                fill="white"
                            /></svg></span
                    ><span
                        >JM Academy<small>CODE • CREATE • INNOVATE</small></span
                    ></a
                ><span
                    >© {{ date('Y') }} JM Academy. All rights reserved.</span
                >
                <div class="footlinks">
                    <a href="#program">Program</a
                    ><a href="#keunggulan">Tentang</a
                    ><a href="#daftar">Kontak</a>
                </div>
            </div>
        </footer>
        <script>
            const m = document.querySelector(".menu"),
                l = document.querySelector(".links");
            m.addEventListener("click", () => {
                const o = l.classList.toggle("open");
                m.setAttribute("aria-expanded", o);
                m.textContent = o ? "×" : "☰";
            });
            l.querySelectorAll("a").forEach((a) =>
                a.addEventListener("click", () => {
                    l.classList.remove("open");
                    m.textContent = "☰";
                }),
            );
            const ob = new IntersectionObserver(
                (es) =>
                    es.forEach((e) => {
                        if (e.isIntersecting) {
                            e.target.classList.add("visible");
                            ob.unobserve(e.target);
                        }
                    }),
                { threshold: 0.12 },
            );
            document.querySelectorAll(".reveal").forEach((e) => ob.observe(e));
        </script>
    </body>
</html>
