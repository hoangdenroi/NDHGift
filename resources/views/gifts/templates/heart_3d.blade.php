@extends('layouts.gift-player')

@section('head')
    <!-- Three.js CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <!-- OrbitControls.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
@endsection

@section('effect-content')
    <script>
        // --- ENGINE THREE.JS ---
        let scene, camera, renderer, controls;
        let heartPoints, starfield, textCylinder, dustPoints;
        let heartGeometry, starGeometry;
        const container = document.getElementById('canvas-container');
        const clock = new THREE.Clock();

        // Khởi chạy Three.js ngay lập tức để render scene tĩnh phía sau
        initThree(window.giftData);
        animate();

        function initThree(giftData) {
            // Scene
            scene = new THREE.Scene();
            scene.fog = new THREE.FogExp2(0x000000, 0.005); // Trùng màu nền đen #000000

            // Camera
            camera = new THREE.PerspectiveCamera(60, window.innerWidth / window.innerHeight, 1, 1000);
            camera.position.set(0, 5, 30);

            // Renderer
            renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
            renderer.setPixelRatio(window.devicePixelRatio);
            renderer.setSize(window.innerWidth, window.innerHeight);
            container.appendChild(renderer.domElement);

            // Controls
            controls = new THREE.OrbitControls(camera, renderer.domElement);
            controls.enableDamping = true;
            controls.dampingFactor = 0.05;
            controls.maxDistance = 80;
            controls.minDistance = 10;
            controls.autoRotate = true;
            controls.autoRotateSpeed = 0.5;

            // Lights
            const ambientLight = new THREE.AmbientLight(0xffffff, 0.8);
            scene.add(ambientLight);

            // Build objects
            createStarfield();
            createHeart();
            createTextCylinder(giftData);

            // Event Listeners
            window.addEventListener('resize', onWindowResize);
        }

        // Tạo dải sao nền lấp lánh
        function createStarfield() {
            const starsCount = 1200;
            starGeometry = new THREE.BufferGeometry();
            const positions = new Float32Array(starsCount * 3);
            const colors = new Float32Array(starsCount * 3);

            for (let i = 0; i < starsCount; i++) {
                positions[i * 3] = (Math.random() - 0.5) * 200;
                positions[i * 3 + 1] = (Math.random() - 0.5) * 200;
                positions[i * 3 + 2] = (Math.random() - 0.5) * 200;

                const r = 0.85 + Math.random() * 0.15;
                const g = 0.85 + Math.random() * 0.15;
                const b = 0.95 + Math.random() * 0.05;
                colors[i * 3] = r;
                colors[i * 3 + 1] = g;
                colors[i * 3 + 2] = b;
            }

            starGeometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
            starGeometry.setAttribute('color', new THREE.BufferAttribute(colors, 3));

            const starTexture = createCircleTexture(16, '#ffffff');

            const starMaterial = new THREE.PointsMaterial({
                size: 0.7,
                map: starTexture,
                transparent: true,
                vertexColors: true,
                blending: THREE.AdditiveBlending,
                depthWrite: false
            });

            starfield = new THREE.Points(starGeometry, starMaterial);
            scene.add(starfield);
        }

        // Tạo hình trái tim 3D bằng hạt
        function createHeart() {
            const heartParticlesCount = 5000;
            heartGeometry = new THREE.BufferGeometry();
            const positions = new Float32Array(heartParticlesCount * 3);

            // Công thức tham số vỏ trái tim 3D
            for (let i = 0; i < heartParticlesCount; i++) {
                const u = Math.random() * Math.PI;
                const v = (Math.random() - 0.5) * Math.PI * 2;

                const sinU = Math.sin(u);
                const cosU = Math.cos(u);
                const sinV = Math.sin(v);
                const cosV = Math.cos(v);

                let x = 16 * Math.pow(sinU, 3) * sinV;
                let y = 13 * cosU - 5 * Math.cos(2*u) - 2 * Math.cos(3*u) - Math.cos(4*u);
                let z = 16 * Math.pow(sinU, 3) * cosV;

                const scale = 0.5 + Math.random() * 0.5;
                positions[i * 3] = x * scale * 0.6;
                positions[i * 3 + 1] = y * scale * 0.6 + 3.0; // Dịch tâm lên
                positions[i * 3 + 2] = z * scale * 0.6;
            }

            heartGeometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
            
            const heartTexture = createCircleTexture(32, '#fb7185', true);

            const heartMaterial = new THREE.PointsMaterial({
                size: 0.45,
                map: heartTexture,
                transparent: true,
                blending: THREE.AdditiveBlending,
                depthWrite: false,
                color: 0xff4d79 // Màu hồng đỏ lãng mạn
            });

            heartPoints = new THREE.Points(heartGeometry, heartMaterial);
            scene.add(heartPoints);
        }

        // Tạo vòng chữ chạy xoay tròn ở dưới chân trái tim
        function createTextCylinder(giftData) {
            const textCanvas = document.createElement('canvas');
            textCanvas.width = 2048;
            textCanvas.height = 256;
            const ctx = textCanvas.getContext('2d');

            ctx.fillStyle = 'rgba(0, 0, 0, 0)';
            ctx.fillRect(0, 0, textCanvas.width, textCanvas.height);

            // Cấu hình chữ lấp lánh
            ctx.font = 'bold 38px "Outfit", sans-serif';
            ctx.fillStyle = '#fda4af';
            ctx.shadowColor = '#f43f5e';
            ctx.shadowBlur = 18;
            ctx.textAlign = 'left';
            ctx.textBaseline = 'middle';

            // Đọc spiral_texts từ giftData settings
            let spiralTexts = ['Mãi yêu em! 💖', 'Trọn đời bên nhau 💕', 'Yêu em nhiều hơn mỗi ngày!'];
            if (giftData && giftData.settings) {
                if (typeof giftData.settings.spiral_texts === 'string') {
                    // Nếu là chuỗi phân tách bởi dấu phẩy
                    spiralTexts = giftData.settings.spiral_texts.split(',').map(t => t.trim());
                } else if (Array.isArray(giftData.settings.spiral_texts)) {
                    spiralTexts = giftData.settings.spiral_texts;
                }
            }

            const phrase = spiralTexts.join("   💖   ") + "   💖   ";
            let repeatedText = phrase;
            while(ctx.measureText(repeatedText).width < textCanvas.width) {
                repeatedText += phrase;
            }

            ctx.fillText(repeatedText, 10, textCanvas.height / 2);

            const texture = new THREE.CanvasTexture(textCanvas);
            texture.wrapS = THREE.RepeatWrapping;
            texture.wrapT = THREE.ClampToEdgeWrapping;

            const geometry = new THREE.CylinderGeometry(11, 9, 2.2, 64, 1, true);
            const material = new THREE.MeshBasicMaterial({
                map: texture,
                transparent: true,
                side: THREE.DoubleSide,
                depthWrite: false,
                blending: THREE.AdditiveBlending
            });

            textCylinder = new THREE.Mesh(geometry, material);
            textCylinder.position.set(0, -6, 0);
            scene.add(textCylinder);

            createSpiralDust();
        }

        // Bụi hạt galaxy xoay xung quanh vòng chữ
        function createSpiralDust() {
            const count = 350;
            const geom = new THREE.BufferGeometry();
            const positions = new Float32Array(count * 3);

            for(let i=0; i<count; i++) {
                const angle = (i / count) * Math.PI * 6;
                const radius = 9.5 + (Math.random() - 0.5) * 1.5;
                positions[i*3] = Math.cos(angle) * radius;
                positions[i*3+1] = -6 + (Math.random() - 0.5) * 1.2;
                positions[i*3+2] = Math.sin(angle) * radius;
            }

            geom.setAttribute('position', new THREE.BufferAttribute(positions, 3));
            const dustTexture = createCircleTexture(16, '#f43f5e');
            const mat = new THREE.PointsMaterial({
                size: 0.28,
                map: dustTexture,
                transparent: true,
                blending: THREE.AdditiveBlending,
                depthWrite: false,
                color: 0xffc0cb
            });

            dustPoints = new THREE.Points(geom, mat);
            scene.add(dustPoints);
        }

        // Helper tạo CanvasTexture hình tròn phát sáng
        function createCircleTexture(size, colorHex, glow = false) {
            const canvas = document.createElement('canvas');
            canvas.width = size;
            canvas.height = size;
            const ctx = canvas.getContext('2d');
            const center = size / 2;

            if (glow) {
                const grad = ctx.createRadialGradient(center, center, 0, center, center, center);
                grad.addColorStop(0, colorHex);
                grad.addColorStop(0.3, 'rgba(244, 63, 94, 0.8)');
                grad.addColorStop(0.7, 'rgba(244, 63, 94, 0.15)');
                grad.addColorStop(1, 'rgba(0,0,0,0)');
                ctx.fillStyle = grad;
            } else {
                ctx.fillStyle = colorHex;
            }

            ctx.beginPath();
            ctx.arc(center, center, center, 0, Math.PI * 2);
            ctx.fill();

            return new THREE.CanvasTexture(canvas);
        }

        // Thời điểm bắt đầu chạy hiệu ứng (reset khi mở quà)
        let activeStartTime = null;

        // Animation Loop
        function animate() {
            requestAnimationFrame(animate);

            const active = window.NDHGift.isOpened;
            
            // Khi vừa chuyển sang active, ghi nhận thời điểm bắt đầu
            if (active && activeStartTime === null) {
                activeStartTime = performance.now();
            }

            // Tính thời gian trôi qua kể từ khi mở quà (giây)
            const elapsedTime = active ? (performance.now() - activeStartTime) / 1000 : 0;

            // Nhịp đập trái tim
            const pulse = active ? (1.0 + 0.08 * Math.pow(Math.sin(elapsedTime * 2.8), 4)) : 1.0;
            const doublePulse = active ? (pulse + 0.03 * Math.pow(Math.sin(elapsedTime * 5.6), 2)) : 1.0;
            if (heartPoints) {
                heartPoints.scale.set(doublePulse, doublePulse, doublePulse);
                if (active) {
                    heartPoints.rotation.y = elapsedTime * 0.12;
                }
            }

            // Chữ chạy xoay tròn
            if (textCylinder) {
                if (active) {
                    textCylinder.rotation.y = -elapsedTime * 0.22;
                    textCylinder.material.map.offset.x = elapsedTime * 0.04;
                }
            }

            // Bụi galaxy xoay
            if (dustPoints && active) {
                dustPoints.rotation.y = elapsedTime * 0.32;
            }

            // Sao nền bay chậm
            if (starfield && active) {
                starfield.rotation.y = elapsedTime * 0.015;
                starfield.rotation.x = elapsedTime * 0.008;
            }

            if (controls) {
                controls.autoRotate = active;
                controls.update();
            }
            renderer.render(scene, camera);
        }

        function onWindowResize() {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        }
    </script>
@endsection
