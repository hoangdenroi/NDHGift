@extends('layouts.gift-player')

@section('head')
    <!-- Three.js CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <!-- OrbitControls.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
@endsection

@section('effect-content')
    <style>
        /* --- STYLE THEME MÙA ĐÔNG NỀN SÁNG --- */
        body {
            background: linear-gradient(to bottom, #8fa9c4 0%, #eef2f7 100%) !important; /* Xanh xám lạnh chuyển sang trắng tuyết */
            color: #0f172a !important; /* Chữ tối để dễ đọc trên nền sáng */
        }

        /* Mở đầu */
        #opening-screen {
            background: radial-gradient(circle at center, #ffffff 0%, #bdcedf 100%) !important;
        }
        #opening-screen h2, #opening-screen p, #opening-screen span {
            color: #0f172a !important;
        }

        /* Glassmorphism phiên bản sáng */
        .glass-panel {
            background: rgba(255, 255, 255, 0.75) !important;
            backdrop-filter: blur(20px) !important;
            -webkit-backdrop-filter: blur(20px) !important;
            border: 1px solid rgba(13, 89, 242, 0.08) !important;
            box-shadow: 0 8px 32px 0 rgba(13, 89, 242, 0.08) !important;
            color: #1e293b !important;
        }

        /* Card lời chúc */
        #text-overlay h1 {
            color: #0d59f2 !important; /* Màu xanh tuyết */
            text-shadow: 0 0 15px rgba(13, 89, 242, 0.1) !important;
        }
        #text-overlay h2 {
            color: #0f172a !important;
        }
        #text-overlay p {
            color: #334155 !important;
        }

        /* Control Panel */
        #control-center button {
            color: #0f172a !important;
        }
        #control-center span {
            color: #334155 !important;
        }
        #control-center button:hover {
            background-color: rgba(13, 89, 242, 0.05) !important;
        }
        #control-center .border-b {
            border-bottom-color: rgba(0, 0, 0, 0.08) !important;
        }

        /* Nút phong thư */
        #envelope-widget button {
            background: linear-gradient(135deg, #0d59f2 0%, #3b82f6 100%) !important;
            box-shadow: 0 4px 15px rgba(13, 89, 242, 0.25) !important;
        }

        /* Hướng dẫn cuộn chuột mượt mà ở góc dưới bên phải */
        #scroll-hint {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 40;
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.6);
            padding: 8px 14px;
            border-radius: 20px;
            border: 1px solid rgba(0, 0, 0, 0.05);
            font-size: 11px;
            font-weight: 600;
            color: #475569;
            pointer-events: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            animation: pulse-hint 2s infinite ease-in-out;
        }

        @keyframes pulse-hint {
            0%, 100% { opacity: 0.6; transform: translateY(0); }
            50% { opacity: 0.9; transform: translateY(-3px); }
        }
    </style>

    @if($isDemo ?? false)
    <style>
        /* Watermark Demo màu thẫm mờ */
        .fixed.inset-0.flex.items-center.justify-center.pointer-events-none span {
            color: rgba(15, 23, 42, 0.03) !important;
        }
    </style>
    @endif

    <div id="scroll-hint">
        <span class="material-symbols-outlined text-[16px]">swap_vertical</span>
        Cuộn chuột hoặc kéo màn hình để tham quan album ❄️
    </div>

    <script>
        // Khi SDK báo sẵn sàng (sau khi click Mở Quà)
        window.NDHGift.onReady((giftData) => {
            initThree(giftData);
            animate();
        });

        // --- ENGINE THREE.JS ---
        let scene, camera, renderer, controls;
        let snowParticles, albumGroup;
        const container = document.getElementById('canvas-container');
        const clock = new THREE.Clock();

        // Biến phục vụ Scroll Lerp (Cuộn mượt mà)
        let targetZ = 0;
        let currentZ = 0;
        let targetY = 0;
        let currentY = 0;

        // Biến phục vụ Mouse Parallax
        let mouseX = 0;
        let mouseY = 0;

        function initThree(giftData) {
            // 1. Scene & Fog sáng tuyết
            scene = new THREE.Scene();
            scene.fog = new THREE.FogExp2(0xb6c9dc, 0.01);

            // 2. Camera
            camera = new THREE.PerspectiveCamera(60, window.innerWidth / window.innerHeight, 1, 1000);
            camera.position.set(0, 2, 28);

            // 3. Renderer
            renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
            renderer.setPixelRatio(window.devicePixelRatio);
            renderer.setSize(window.innerWidth, window.innerHeight);
            container.appendChild(renderer.domElement);

            // 4. OrbitControls với damping mượt mà
            controls = new THREE.OrbitControls(camera, renderer.domElement);
            controls.enableDamping = true;
            controls.dampingFactor = 0.03; // Trơn tru hơn bản gốc
            controls.enableZoom = false; // Tắt zoom mặc định của controls để dùng Scroll Lerp tùy biến
            controls.minPolarAngle = Math.PI / 3; // Giới hạn góc nhìn nghiêng để giữ bố cục
            controls.maxPolarAngle = Math.PI / 1.8;

            // 5. Ánh sáng
            const ambientLight = new THREE.AmbientLight(0xffffff, 0.9);
            scene.add(ambientLight);

            const dirLight = new THREE.DirectionalLight(0xffffff, 0.6);
            dirLight.position.set(10, 20, 15);
            scene.add(dirLight);

            // 6. Tạo Hệ thống bông tuyết rơi (Snow system)
            createSnowSystem();

            // 7. Tạo Album ảnh 3D lơ lửng
            createPhotoAlbum(giftData);

            // 8. Đăng ký sự kiện
            window.addEventListener('resize', onWindowResize, false);
            
            // Lắng nghe sự kiện cuộn chuột (Scroll)
            window.addEventListener('wheel', (e) => {
                targetZ += e.deltaY * 0.05;
                targetZ = Math.max(-10, Math.min(40, targetZ)); // Giới hạn di chuyển sâu

                targetY -= e.deltaY * 0.01;
                targetY = Math.max(-6, Math.min(6, targetY));
            }, { passive: true });

            // Lắng nghe sự kiện vuốt chạm (Mobile Touch Scroll)
            let touchStartY = 0;
            window.addEventListener('touchstart', (e) => {
                touchStartY = e.touches[0].clientY;
            }, { passive: true });

            window.addEventListener('touchmove', (e) => {
                let touchY = e.touches[0].clientY;
                let deltaY = touchStartY - touchY;
                touchStartY = touchY;
                
                targetZ += deltaY * 0.1;
                targetZ = Math.max(-10, Math.min(40, targetZ));
                
                targetY -= deltaY * 0.02;
                targetY = Math.max(-6, Math.min(6, targetY));
            }, { passive: true });

            // Lắng nghe di chuột (Mouse Move Parallax)
            window.addEventListener('mousemove', (e) => {
                mouseX = (e.clientX / window.innerWidth) - 0.5;
                mouseY = (e.clientY / window.innerHeight) - 0.5;
            }, { passive: true });
        }

        // Tạo Texture bông tuyết mềm mại bằng Canvas
        function createSnowflakeTexture() {
            const canvas = document.createElement('canvas');
            canvas.width = 16;
            canvas.height = 16;
            const ctx = canvas.getContext('2d');
            
            const grad = ctx.createRadialGradient(8, 8, 0, 8, 8, 8);
            grad.addColorStop(0, 'rgba(255, 255, 255, 1)');
            grad.addColorStop(0.5, 'rgba(255, 255, 255, 0.8)');
            grad.addColorStop(1, 'rgba(255, 255, 255, 0)');
            
            ctx.fillStyle = grad;
            ctx.beginPath();
            ctx.arc(8, 8, 8, 0, Math.PI * 2);
            ctx.fill();
            
            return new THREE.CanvasTexture(canvas);
        }

        function createSnowSystem() {
            const particleCount = 1500;
            const geometry = new THREE.BufferGeometry();
            const positions = new Float32Array(particleCount * 3);
            const velocities = [];

            for (let i = 0; i < particleCount; i++) {
                // Rải hạt trong không gian lớn
                positions[i * 3] = (Math.random() - 0.5) * 80;
                positions[i * 3 + 1] = Math.random() * 50 - 10;
                positions[i * 3 + 2] = (Math.random() - 0.5) * 80;

                // Tốc độ rơi, chao liệng ngẫu nhiên
                velocities.push({
                    y: 0.05 + Math.random() * 0.08,
                    x: (Math.random() - 0.5) * 0.04,
                    z: (Math.random() - 0.5) * 0.04,
                    waveSpeed: 1 + Math.random() * 2,
                    waveOffset: Math.random() * 100
                });
            }

            geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));

            const material = new THREE.PointsMaterial({
                size: 0.45,
                map: createSnowflakeTexture(),
                transparent: true,
                blending: THREE.NormalBlending,
                depthWrite: false,
            });

            snowParticles = new THREE.Points(geometry, material);
            snowParticles.userData.velocities = velocities;
            scene.add(snowParticles);
        }

        function createPhotoAlbum(giftData) {
            albumGroup = new THREE.Group();
            scene.add(albumGroup);

            // Cấu hình 5 ảnh tương ứng với các tỷ lệ hình học
            const photoConfig = [
                {
                    key: 'photo_1_1',
                    ratio: 1 / 1,
                    w: 4.8, h: 4.8,
                    pos: new THREE.Vector3(-7.5, 2.5, -6),
                    rotY: 0.45
                },
                {
                    key: 'photo_4_3',
                    ratio: 4 / 3,
                    w: 6.4, h: 4.8,
                    pos: new THREE.Vector3(8.0, 3.0, -12),
                    rotY: -0.45
                },
                {
                    key: 'photo_3_2',
                    ratio: 3 / 2,
                    w: 7.2, h: 4.8,
                    pos: new THREE.Vector3(-8.5, -3.2, -18),
                    rotY: 0.35
                },
                {
                    key: 'photo_16_9',
                    ratio: 16 / 9,
                    w: 8.53, h: 4.8,
                    pos: new THREE.Vector3(8.5, -2.5, -24),
                    rotY: -0.35
                },
                {
                    key: 'photo_9_16',
                    ratio: 9 / 16,
                    w: 2.7, h: 4.8,
                    pos: new THREE.Vector3(0, 0.5, -30),
                    rotY: 0
                }
            ];

            const loader = new THREE.TextureLoader();

            photoConfig.forEach((cfg) => {
                // Lấy đường dẫn ảnh từ dữ liệu, fallback về ảnh mẫu tương ứng
                const imgPath = giftData[cfg.key] || `/assets/images/${cfg.key.replace('photo_', '').replace('_', '-')}.png`;

                loader.load(imgPath, (texture) => {
                    texture.minFilter = THREE.LinearFilter;
                    
                    // Group riêng cho mỗi khung ảnh (giúp xử lý hiệu ứng chao nghiêng cục bộ)
                    const frameGroup = new THREE.Group();
                    frameGroup.position.copy(cfg.pos);
                    frameGroup.rotation.y = cfg.rotY;

                    // 1. Mặt ảnh chính (MeshBasicMaterial để ảnh sáng rực rỡ không bị bóng)
                    const photoGeom = new THREE.PlaneGeometry(cfg.w, cfg.h);
                    const photoMat = new THREE.MeshBasicMaterial({
                        map: texture,
                        side: THREE.DoubleSide
                    });
                    const photoMesh = new THREE.Mesh(photoGeom, photoMat);
                    frameGroup.add(photoMesh);

                    // 2. Viền trắng xung quanh (Passpartout) và khung sau
                    const borderSize = 0.25;
                    const frameGeom = new THREE.PlaneGeometry(cfg.w + borderSize, cfg.h + borderSize);
                    
                    // Màu xám tuyết / bạc mờ sang trọng
                    const frameMat = new THREE.MeshStandardMaterial({
                        color: 0xffffff,
                        roughness: 0.1,
                        metalness: 0.1,
                        side: THREE.DoubleSide
                    });
                    const frameMesh = new THREE.Mesh(frameGeom, frameMat);
                    frameMesh.position.z = -0.02; // Đặt lùi về sau 1 chút
                    frameGroup.add(frameMesh);

                    // 3. Khung viền kim loại dày (Màu xanh thẫm)
                    const borderThickness = 0.08;
                    const backFrameGeom = new THREE.PlaneGeometry(
                        cfg.w + borderSize + borderThickness, 
                        cfg.h + borderSize + borderThickness
                    );
                    const backFrameMat = new THREE.MeshStandardMaterial({
                        color: 0x1e3a8a, // Xanh thẫm mùa đông
                        roughness: 0.2,
                        metalness: 0.8,
                        side: THREE.DoubleSide
                    });
                    const backFrameMesh = new THREE.Mesh(backFrameGeom, backFrameMat);
                    backFrameMesh.position.z = -0.04;
                    frameGroup.add(backFrameMesh);

                    // Lưu trữ thông số quay ban đầu để tính parallax
                    frameGroup.userData = {
                        baseRotY: cfg.rotY,
                        baseRotX: 0
                    };

                    albumGroup.add(frameGroup);
                });
            });
        }

        function animate() {
            requestAnimationFrame(animate);

            const time = clock.getElapsedTime();

            // 1. Xoay/cập nhật controls mượt mà
            controls.update();

            // 2. Di chuyển các bông tuyết rơi chao liệng sinh động
            if (snowParticles) {
                const positions = snowParticles.geometry.attributes.position.array;
                const velocities = snowParticles.userData.velocities;

                for (let i = 0; i < velocities.length; i++) {
                    const vel = velocities[i];
                    
                    // Rơi Y
                    positions[i * 3 + 1] -= vel.y;

                    // Chao liệng X & Z theo hàm hình Sin ngẫu nhiên
                    positions[i * 3] += vel.x + Math.sin(time * vel.waveSpeed + vel.waveOffset) * 0.008;
                    positions[i * 3 + 2] += vel.z + Math.cos(time * vel.waveSpeed + vel.waveOffset) * 0.008;

                    // Nếu rơi xuống quá thấp, đưa tuyết trở lại đỉnh
                    if (positions[i * 3 + 1] < -12) {
                        positions[i * 3] = (Math.random() - 0.5) * 80;
                        positions[i * 3 + 1] = 35;
                        positions[i * 3 + 2] = (Math.random() - 0.5) * 80;
                    }
                }
                snowParticles.geometry.attributes.position.needsUpdate = true;
            }

            // 3. Scroll Lerp (Cuộn mượt mà tịnh tiến vị trí Album)
            currentZ += (targetZ - currentZ) * 0.05;
            currentY += (targetY - currentY) * 0.05;
            if (albumGroup) {
                albumGroup.position.z = currentZ;
                albumGroup.position.y = currentY;

                // 4. Mouse Parallax (Chao nghiêng nhẹ album theo tọa độ di chuột)
                albumGroup.rotation.y += (mouseX * 0.15 - albumGroup.rotation.y) * 0.04;
                albumGroup.rotation.x += (mouseY * 0.1 - albumGroup.rotation.x) * 0.04;

                // Chao nghiêng nhẹ từng khung ảnh độc lập tạo chiều sâu lập thể
                albumGroup.children.forEach((frame) => {
                    if (frame.userData) {
                        frame.rotation.y = frame.userData.baseRotY + (mouseX * 0.08);
                        frame.rotation.x = frame.userData.baseRotX + (mouseY * 0.05);
                    }
                });
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
