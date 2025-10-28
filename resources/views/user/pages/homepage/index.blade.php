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
        body {
            background: #121212;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            position: relative;
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
         style="color:white; text-align:left; font-family: monospace; white-space: pre-wrap; background: transparent; font-size: 15px; line-height: 1.6;"></pre>

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

    // টাইপিং ইফেক্ট
    function typeLine(msg) {
        return new Promise(async (resolve) => {
            const line = document.createElement("div");
            line.style.transition = "color 0.3s ease";
            container.appendChild(line);

            // টাইপিং অ্যানিমেশন
            for (let i = 0; i < msg.length; i++) {
                line.textContent += msg[i];
                await new Promise(r => setTimeout(r, 40));
            }

            // যদি শেষে "..." থাকে, তাহলে ২ বার লোডিং অ্যানিমেশন করবে
            if (msg.endsWith("...")) {
                for (let round = 0; round < 2; round++) {
                    for (let i = 1; i <= 3; i++) {
                        line.textContent = msg.slice(0, -3) + ".".repeat(i);
                        await new Promise(r => setTimeout(r, 200));
                    }
                }
            }

            // ✅ রঙ পরিবর্তন + চেক আইকন
            line.innerHTML = `<span style="color: #00ff6a;">✔</span> ${msg.replace("...", "")}`;
            line.style.color = "#00ff6a";
            await new Promise(r => setTimeout(r, 400));
            resolve();
        });
    }

    // ✅ Agree ফাংশন
    async function agree() {
        // 💥 ক্লিকের সাথেসাথেই বাটন লুকাও
        buttonsContainer.style.display = "none";

        // টাইপিং শুরু
        for (const msg of messages) {
            await typeLine(msg);
        }

        // সবশেষে রিডাইরেক্ট
        setTimeout(() => {
            window.location.href = "{{ url('/login') }}";
        }, 800);
    }

    // ✅ Disagree ফাংশন
    function disagree() {
        buttonsContainer.style.display = "none"; // 💥 ক্লিকের সাথেসাথেই লুকাবে

        if (window.opener) {
            window.close();
        } else {
            window.location.href = "{{ url('/') }}";
        }
    }

    // ইভেন্ট লিসেনার অ্যাড করো
    agreeBtn.addEventListener("click", agree);
    disagreeBtn.addEventListener("click", disagree);

    // 🎇 ব্যাকগ্রাউন্ড পার্টিকলস
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
