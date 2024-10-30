// game.js

// 創建場景、相機和渲染器
const scene = new THREE.Scene();
const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
const renderer = new THREE.WebGLRenderer();
renderer.setSize(window.innerWidth, window.innerHeight);
document.body.appendChild(renderer.domElement);

// 創建地圖
const geometry = new THREE.PlaneGeometry(100, 100);
const material = new THREE.MeshBasicMaterial({ color: 0x00ff00, side: THREE.DoubleSide });
const ground = new THREE.Mesh(geometry, material);
ground.rotation.x = - Math.PI / 2; // 使地面水平
scene.add(ground);

// 創建角色
const characterGeometry = new THREE.BoxGeometry(1, 1, 1);
const characterMaterial = new THREE.MeshBasicMaterial({ color: 0xff0000 });
const character = new THREE.Mesh(characterGeometry, characterMaterial);
character.position.y = 0.5; // 使角色在地面上方
scene.add(character);

// 設置相機位置
camera.position.z = 5;
camera.position.y = 2;
camera.lookAt(character.position);

// 處理鍵盤事件
const keys = {};
window.addEventListener('keydown', (event) => {
    keys[event.code] = true;
});
window.addEventListener('keyup', (event) => {
    keys[event.code] = false;
});

// 更新角色位置
function updateCharacter() {
    if (keys['ArrowUp']) {
        character.position.z -= 0.1; // 向前移動
    }
    if (keys['ArrowDown']) {
        character.position.z += 0.1; // 向後移動
    }
    if (keys['ArrowLeft']) {
        character.position.x -= 0.1; // 向左移動
    }
    if (keys['ArrowRight']) {
        character.position.x += 0.1; // 向右移動
    }
}

// 渲染循環
function animate() {
    requestAnimationFrame(animate);
    updateCharacter();
    renderer.render(scene, camera);
}

animate();