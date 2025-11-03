<!DOCTYPE html>
<html lang="en">
<head>
    @php
        use App\Models\GeneralSetting;
        $generalSettings = GeneralSetting::first();
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $generalSettings->app_name ?? 'Edulife' }}</title>
    <link rel="stylesheet" href="{{ asset('assets/user/css/style.css') }}">
    <style>
    @font-face {
    font-family: 'Digital7';
    src: url('/assets/user/fonts/digital-7/digital-7.ttf') format('truetype');
    }
        body {
            background: #121212;
            font-family: 'Digital7';
            margin: 0;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            position: relative;
        }
        #statusContainer {
            color: white;
            text-align: left;
            font-family: 'Digital7';
            white-space: pre-wrap;
            background: transparent;
            font-size: 18px;
            line-height: 1.8;
        }
        canvas {
            position: absolute;
            top: 0;
            left: 0;
            display: block;
            z-index: 0;
        }

        .btn {
            border: none;
            border-radius: 6px;
            padding: 12px 30px;
            font-weight: 600;
            font-size: 14px;
            letter-spacing: 1px;
            color: rgb(0, 0, 0);
            cursor: pointer;
            min-width: 140px;
            text-align: center;
            user-select: none;
            display: inline-block;
            margin: 5px;
            position: relative;
            transition: all 0.1s ease-in-out;
        }

        .btn-agree {
            background: linear-gradient(145deg, #4ca1ff, #0076ff);
            box-shadow: 0 4px #0076ff80, 0 6px 10px #0076ff50;
        }

        .btn-disagree {
            background: linear-gradient(145deg, #ff3300, #cc2900);
            box-shadow: 0 4px #cc290080, 0 6px 10px #cc290050;
        }

        @media (max-width: 576px) {
            .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>

<canvas id="bgCanvas"></canvas>

<div class="agreement-card" id="agreementCard">
    <pre id="statusContainer"
         style="color:white; text-align:left; white-space: pre-wrap; background: transparent; font-size: 15px; line-height: 1.6;"></pre>

    <div class="d-flex flex-column flex-sm-row justify-content-center" id="buttonsContainer" style="text-align:center;">
        <button class="btn btn-agree" id="agreeBtn">Agree</button>
        <button class="btn btn-disagree" id="disagreeBtn">Disagree</button>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {

    const messages = [
        "Server Integration...",
        "Server Connectivity...",
        "Establishing Connection...",
        "Connecting Backend...",
        "Connecting to Cloud Node...",
        "Network Bridge Established...",
        "Server Sync Enabled...",
        "API Connection Ready..."
    ];

    const container = document.getElementById("statusContainer");
    const buttonsContainer = document.getElementById("buttonsContainer");
    const agreeBtn = document.getElementById("agreeBtn");
    const disagreeBtn = document.getElementById("disagreeBtn");

    function typeLine(msg) {
        return new Promise(async (resolve) => {
            const line = document.createElement("div");
            line.style.transition = "color 0.3s ease";
            container.appendChild(line);

            for (let i = 0; i < msg.length; i++) {
                line.textContent += msg[i];
                await new Promise(r => setTimeout(r, 40));
            }

            if (msg.endsWith("...")) {
                for (let round = 0; round < 2; round++) {
                    for (let i = 1; i <= 3; i++) {
                        line.textContent = msg.slice(0, -3) + ".".repeat(i);
                        await new Promise(r => setTimeout(r, 200));
                    }
                }
            }

            line.innerHTML = `<span style="color: #00ff6a;">✔</span> ${msg.replace("...", "")}`;
            line.style.color = "#00ff6a";
            await new Promise(r => setTimeout(r, 400));
            resolve();
        });
    }


    async function agree() {

        buttonsContainer.style.display = "none";

        for (const msg of messages) {
            await typeLine(msg);
        }


        setTimeout(() => {
            window.location.href = "{{ url('/login') }}";
        }, 800);
    }

    function disagree() {
        buttonsContainer.style.display = "none";

        if (window.opener) {
            window.close();
        } else {
            window.location.href = "{{ url('/') }}";
        }
    }


    agreeBtn.addEventListener("click", agree);
    disagreeBtn.addEventListener("click", disagree);


    const canvas = document.getElementById('bgCanvas');
    const ctx = canvas.getContext('2d');
    let particlesArray;

    class Particle {
        constructor() {
            this.x = Math.random() * canvas.width;
            this.y = Math.random() * canvas.height;
            this.size = Math.random() * 3 + 1;
            this.speedX = (Math.random() - 0.5) * 1.5;
            this.speedY = (Math.random() - 0.5) * 1.5;
            this.color = `rgba(76, 161, 255, ${Math.random()})`;
        }
        update() {
            this.x += this.speedX;
            this.y += this.speedY;
            if (this.x > canvas.width) this.x = 0;
            if (this.x < 0) this.x = canvas.width;
            if (this.y > canvas.height) this.y = 0;
            if (this.y < 0) this.y = canvas.height;
        }
        draw() {
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
            ctx.fillStyle = this.color;
            ctx.fill();
        }
    }

    function initCanvas() {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
        particlesArray = [];
        for (let i = 0; i < 100; i++) {
            particlesArray.push(new Particle());
        }
    }

    function animate() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        particlesArray.forEach(p => {
            p.update();
            p.draw();
        });
        requestAnimationFrame(animate);
    }

    initCanvas();
    animate();
    window.addEventListener('resize', initCanvas);
});
</script>

</body>
</html>
