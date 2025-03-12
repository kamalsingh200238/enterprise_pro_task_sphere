<template>
    <div class="relative flex min-h-screen w-screen items-center justify-center">
        <canvas ref="canvas" class="absolute inset-0 z-10"></canvas>
        <div class="relative z-20 bg-background/50 backdrop-blur-sm text-foreground px-10 py-5 rounded-md">
            <h1 class="text-3xl font-bold">Welcome to Task Sphere</h1>
        </div>
    </div>
</template>

<script setup lang="ts">
import * as THREE from 'three';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
import { onMounted, ref } from 'vue';

// Create a ref for the canvas container
const canvas = ref<HTMLCanvasElement | null>(null);

onMounted(() => {
    if (!canvas.value) return;

    // Create the scene, camera, and renderer
    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
    const renderer = new THREE.WebGLRenderer({ canvas: canvas.value });

    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.setClearColor(0xeeeeee, 1); // Set background color to light gray

    const controls = new OrbitControls(camera, renderer.domElement);

    // Create the sphere geometry
    const geometry = new THREE.SphereGeometry(2, 32, 16);

    // Create a material with a lighting effect (Phong or Standard material)
    const material = new THREE.MeshBasicMaterial({ color: '#ffba00', wireframe: true });

    // Create the sphere mesh
    const sphere = new THREE.Mesh(geometry, material);
    scene.add(sphere);

    // Position the camera
    camera.position.z = 5;
    controls.update();

    // Animation loop
    const animate = () => {
        requestAnimationFrame(animate);

        // Rotate the sphere on each frame
        sphere.rotation.x += 0.005;
        sphere.rotation.z += 0.005;
        sphere.rotation.y += 0.005;

        // Render the scene from the camera's perspective
        renderer.render(scene, camera);
    };

    animate();
});
</script>
