// <--Galeri --> 

let scene, camera, renderer, globe, images = [];
let isRotating = true;
let isDragging = false;
let previousMousePosition = { x: 0, y: 0 };
let autoRotateTimeout;

// Gallery data
const galleryData = [
    {
        url: 'https://images.unsplash.com/photo-1595257841889-eca2678454e2?w=400',
        title: 'Lapis Legit',
        desc: 'Lapis legit spesial dengan rempah'
    },
    {
        url: 'https://images.unsplash.com/photo-1595257841889-eca2678454e2?w=400',
        title: 'Lapis Legit',
        desc: 'Lapis legit spesial dengan rempah'
    },
    {
        url: 'https://images.unsplash.com/photo-1595257841889-eca2678454e2?w=400',
        title: 'Lapis Legit',
        desc: 'Lapis legit spesial dengan rempah'
    },
    {
        url: 'https://images.unsplash.com/photo-1595257841889-eca2678454e2?w=400',
        title: 'Lapis Legit',
        desc: 'Lapis legit spesial dengan rempah'
    },
    {
        url: 'https://images.unsplash.com/photo-1595257841889-eca2678454e2?w=400',
        title: 'Lapis Legit',
        desc: 'Lapis legit spesial dengan rempah'
    },
    {
        url: 'https://images.unsplash.com/photo-1595257841889-eca2678454e2?w=400',
        title: 'Lapis Legit',
        desc: 'Lapis legit spesial dengan rempah'
    },
    {
        url: 'https://images.unsplash.com/photo-1595257841889-eca2678454e2?w=400',
        title: 'Lapis Legit',
        desc: 'Lapis legit spesial dengan rempah'
    },
    {
        url: 'https://images.unsplash.com/photo-1595257841889-eca2678454e2?w=400',
        title: 'Lapis Legit',
        desc: 'Lapis legit spesial dengan rempah'
    },
    {
        url: 'https://images.unsplash.com/photo-1595257841889-eca2678454e2?w=400',
        title: 'Lapis Legit',
        desc: 'Lapis legit spesial dengan rempah'
    },
    {
        url: 'https://images.unsplash.com/photo-1595257841889-eca2678454e2?w=400',
        title: 'Lapis Legit',
        desc: 'Lapis legit spesial dengan rempah'
    },
        {
        url: 'https://images.unsplash.com/photo-1595257841889-eca2678454e2?w=400',
        title: 'Lapis Legit',
        desc: 'Lapis legit spesial dengan rempah'
    },
    {
        url: 'https://images.unsplash.com/photo-1595257841889-eca2678454e2?w=400',
        title: 'Lapis Legit',
        desc: 'Lapis legit spesial dengan rempah'
    },
    {
        url: 'https://images.unsplash.com/photo-1595257841889-eca2678454e2?w=400',
        title: 'Lapis Legit',
        desc: 'Lapis legit spesial dengan rempah'
    },
    {
        url: 'https://images.unsplash.com/photo-1595257841889-eca2678454e2?w=400',
        title: 'Lapis Legit',
        desc: 'Lapis legit spesial dengan rempah'
    },
    {
        url: 'https://images.unsplash.com/photo-1595257841889-eca2678454e2?w=400',
        title: 'Lapis Legit',
        desc: 'Lapis legit spesial dengan rempah'
    },
    {
        url: 'https://images.unsplash.com/photo-1595257841889-eca2678454e2?w=400',
        title: 'Lapis Legit',
        desc: 'Lapis legit spesial dengan rempah'
    },
    {
        url: 'https://images.unsplash.com/photo-1595257841889-eca2678454e2?w=400',
        title: 'Lapis Legit',
        desc: 'Lapis legit spesial dengan rempah'
    },
    {
        url: 'https://images.unsplash.com/photo-1595257841889-eca2678454e2?w=400',
        title: 'Lapis Legit',
        desc: 'Lapis legit spesial dengan rempah'
    },
    {
        url: 'https://images.unsplash.com/photo-1595257841889-eca2678454e2?w=400',
        title: 'Lapis Legit',
        desc: 'Lapis legit spesial dengan rempah'
    },
    {
        url: 'https://images.unsplash.com/photo-1595257841889-eca2678454e2?w=400',
        title: 'Lapis Legit',
        desc: 'Lapis legit spesial dengan rempah'
    },
        {
        url: 'https://images.unsplash.com/photo-1595257841889-eca2678454e2?w=400',
        title: 'Lapis Legit',
        desc: 'Lapis legit spesial dengan rempah'
    }
];

function init() {
    const container = document.getElementById('globe-container');
    container.innerHTML = '';

    // Scene
    scene = new THREE.Scene();

    // Camera
    camera = new THREE.PerspectiveCamera(
        75,
        container.clientWidth / container.clientHeight,
        0.1,
        1000
    );
    camera.position.z = 10;

    // Renderer
    renderer = new THREE.WebGLRenderer({
        antialias: true,
        alpha: true
    });
    renderer.setSize(container.clientWidth, container.clientHeight);
    renderer.setClearColor(0x000000, 0);
    container.appendChild(renderer.domElement);

    // Sphere
    const geometry = new THREE.SphereGeometry(5, 64, 64);
    const material = new THREE.MeshBasicMaterial({
        color: 0x0a1f0f,
        wireframe: false
    });

    globe = new THREE.Mesh(geometry, material);
    scene.add(globe);

    // Images
    const textureLoader = new THREE.TextureLoader();
    const imageCount = galleryData.length;

    const cols = Math.ceil(Math.sqrt(imageCount * 2));
    const rows = Math.ceil(imageCount / cols);
    let imageIndex = 0;

    for (let row = 0; row < rows && imageIndex < imageCount; row++) {
        for (let col = 0; col < cols && imageIndex < imageCount; col++) {
            const data = galleryData[imageIndex];

            textureLoader.load(data.url, (texture) => {
                const latStart = (row / rows) * Math.PI - Math.PI / 2;
                const latEnd = ((row + 1) / rows) * Math.PI - Math.PI / 2;
                const lonStart = (col / cols) * Math.PI * 2;
                const lonEnd = ((col + 1) / cols) * Math.PI * 2;

                const segmentGeometry = new THREE.SphereGeometry(
                    5.05,
                    16,
                    16,
                    lonStart,
                    lonEnd - lonStart,
                    latStart + Math.PI / 2,
                    latEnd - latStart
                );

                const segmentMaterial = new THREE.MeshBasicMaterial({
                    map: texture,
                    side: THREE.DoubleSide
                });

                const segment = new THREE.Mesh(segmentGeometry, segmentMaterial);
                segment.userData = data;

                globe.add(segment);
                images.push(segment);
            });

            imageIndex++;
        }
    }

    // Lighting
    scene.add(new THREE.AmbientLight(0xffffff, 0.8));

    const pointLight = new THREE.PointLight(0xff8c42, 0.5);
    pointLight.position.set(10, 10, 10);
    scene.add(pointLight);

    // Events
    renderer.domElement.addEventListener('mousedown', onMouseDown);
    renderer.domElement.addEventListener('mousemove', onMouseMove);
    renderer.domElement.addEventListener('mouseup', onMouseUp);
    renderer.domElement.addEventListener('mouseleave', onMouseUp);
    renderer.domElement.addEventListener('click', onMouseClick);

    renderer.domElement.addEventListener('touchstart', onTouchStart);
    renderer.domElement.addEventListener('touchmove', onTouchMove);
    renderer.domElement.addEventListener('touchend', onTouchEnd);

    window.addEventListener('resize', onWindowResize);

    animate();
}

function animate() {
    requestAnimationFrame(animate);
    if (isRotating && !isDragging) {
        globe.rotation.y += 0.003;
    }
    renderer.render(scene, camera);
}

// ==== MOUSE ====
function onMouseDown(e) {
    isDragging = true;
    isRotating = false;
    clearTimeout(autoRotateTimeout);

    previousMousePosition = { x: e.clientX, y: e.clientY };
}

function onMouseMove(e) {
    if (!isDragging) return;

    const deltaX = e.clientX - previousMousePosition.x;
    const deltaY = e.clientY - previousMousePosition.y;

    globe.rotation.y += deltaX * 0.005;
    globe.rotation.x += deltaY * 0.005;

    previousMousePosition = { x: e.clientX, y: e.clientY };
}

function onMouseUp() {
    if (!isDragging) return;

    isDragging = false;
    autoRotateTimeout = setTimeout(() => (isRotating = true), 500);
}

function onMouseClick(e) {
    if (isDragging) return;

    const mouse = new THREE.Vector2();
    const rect = renderer.domElement.getBoundingClientRect();

    mouse.x = ((e.clientX - rect.left) / rect.width) * 2 - 1;
    mouse.y = -((e.clientY - rect.top) / rect.height) * 2 + 1;

    const raycaster = new THREE.Raycaster();
    raycaster.setFromCamera(mouse, camera);

    const intersects = raycaster.intersectObjects(images);
    if (intersects.length > 0) showModal(intersects[0].object.userData);
}

// ==== TOUCH ====
let touchStartPos = { x: 0, y: 0 };
let touchMoved = false;

function onTouchStart(e) {
    isDragging = true;
    isRotating = false;
    touchMoved = false;
    clearTimeout(autoRotateTimeout);

    touchStartPos = {
        x: e.touches[0].clientX,
        y: e.touches[0].clientY
    };

    previousMousePosition = touchStartPos;
}

function onTouchMove(e) {
    if (!isDragging) return;

    touchMoved = true;

    const deltaX = e.touches[0].clientX - previousMousePosition.x;
    const deltaY = e.touches[0].clientY - previousMousePosition.y;

    globe.rotation.y += deltaX * 0.005;
    globe.rotation.x += deltaY * 0.005;

    previousMousePosition = {
        x: e.touches[0].clientX,
        y: e.touches[0].clientY
    };
}

function onTouchEnd(e) {
    if (!touchMoved) {
        const touch = e.changedTouches[0];
        const mouse = new THREE.Vector2();
        const rect = renderer.domElement.getBoundingClientRect();

        mouse.x = ((touch.clientX - rect.left) / rect.width) * 2 - 1;
        mouse.y = -((touch.clientY - rect.top) / rect.height) * 2 + 1;

        const raycaster = new THREE.Raycaster();
        raycaster.setFromCamera(mouse, camera);

        const intersects = raycaster.intersectObjects(images);
        if (intersects.length > 0) showModal(intersects[0].object.userData);
    }

    isDragging = false;
    touchMoved = false;

    autoRotateTimeout = setTimeout(() => (isRotating = true), 500);
}

function onWindowResize() {
    const container = document.getElementById('globe-container');
    camera.aspect = container.clientWidth / container.clientHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(container.clientWidth, container.clientHeight);
}

// ==== MODAL ====
function showModal(data) {
    document.getElementById('modalImage').src = data.url.replace('w=400', 'w=800');
    document.getElementById('modalTitle').textContent = data.title;
    document.getElementById('modalDesc').textContent = data.desc;

    document.getElementById('imageModal').classList.add('active');
}

function closeModal() {
    document.getElementById('imageModal').classList.remove('active');
}

document.getElementById('closeModal').addEventListener('click', closeModal);

document.getElementById('imageModal').addEventListener('click', (e) => {
    if (e.target === e.currentTarget) closeModal();
});

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeModal();
});

// Init
window.addEventListener('load', init);