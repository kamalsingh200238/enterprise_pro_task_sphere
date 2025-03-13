<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { useAppearance } from '@/composables/useAppearance';
import { getSystemTheme } from '@/lib/getSystemTheme';
import { Link } from '@inertiajs/vue3';
import * as THREE from 'three';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const { appearance } = useAppearance();

const canvasBG = computed(() => {
    switch (appearance.value) {
        case 'light':
            return '#fff';
        case 'dark':
            return '#000';
        default:
            const theme = getSystemTheme();
            const color = theme === 'light' ? '#fff' : '#000';
            return color;
    }
});

// create a ref for the canvas container
const canvas = ref<HTMLCanvasElement | null>(null);

// common variables for the scene
let scene: THREE.Scene;
let camera: THREE.PerspectiveCamera;
let renderer: THREE.WebGLRenderer;
let controls: OrbitControls;
let sphere: THREE.Mesh;

watch(canvasBG, (newValue) => {
    renderer.setClearColor(newValue, 1);
});

const onResize = () => {
    if (!camera || !renderer) return;
    camera.aspect = window.innerWidth / window.innerHeight;
    camera.updateProjectionMatrix();
    controls.update();
    renderer.setSize(window.innerWidth, window.innerHeight);
};

onMounted(() => {
    if (!canvas.value) return;

    // create the scene - this is the container for all 3d objects
    scene = new THREE.Scene();

    // create a camera with perspective projection
    camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
    camera.position.z = 5;

    // create the renderer that will draw our 3d scene onto the canvas
    renderer = new THREE.WebGLRenderer({ canvas: canvas.value, antialias: true });
    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.setClearColor(canvasBG.value, 1); // Set background color to light gray

    // add orbitcontrols to allow user interaction with the scene (rotate, pan, zoom)
    controls = new OrbitControls(camera, renderer.domElement);
    controls.update();

    // create a sphere geometry to represent our "task sphere"
    const geometry = new THREE.SphereGeometry(2.5, 32, 16);

    // create a basic material with wireframe enabled to show the sphere's structure
    const material = new THREE.MeshBasicMaterial({ color: '#ffba00', wireframe: true });

    // combine the geometry and material to create a 3d mesh object
    sphere = new THREE.Mesh(geometry, material);
    scene.add(sphere);

    // listen for window resize events
    window.addEventListener('resize', onResize);

    // create an animation loop function that runs continuously
    const animate = () => {
        requestAnimationFrame(animate);
        sphere.rotation.x += 0.002;
        sphere.rotation.z += 0.002;
        sphere.rotation.y += 0.002;
        renderer.render(scene, camera);
    };
    animate();
});

onUnmounted(() => {
    window.removeEventListener('resize', onResize);
    renderer.dispose();
});
</script>
<template>
    <div class="min-h-screen w-full overflow-hidden">
        <!-- header with navigation and login button -->
        <header class="fixed top-0 z-50 w-full border-b bg-background/50 backdrop-blur dark:bg-background/80">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-5 px-4 py-3 md:px-10">
                <div>
                    <!-- TODO: Add logo -->
                    <span class="text-lg font-bold">Task Sphere</span>
                </div>
                <nav class="flex items-center">
                    <template v-if="$page.props.auth?.user">
                        <Button as-child>
                            <Link :href="route('dashboard')">Dashboard</Link>
                        </Button>
                    </template>
                    <template v-else>
                        <Button as-child>
                            <Link :href="route('login')">Login</Link>
                        </Button>
                    </template>
                </nav>
            </div>
        </header>

        <!-- main content area with 3d sphere visualization -->
        <main>
            <div class="flex min-h-screen w-full items-center justify-center">
                <!-- canvas for three.js rendering -->
                <canvas ref="canvas" class="fixed inset-0 z-10"></canvas>

                <!-- Centered content with backdrop blur -->
                <div class="relative z-20 rounded-md bg-background/50 px-10 py-8 text-center backdrop-blur dark:bg-background/80">
                    <h1 class="text-2xl font-bold tracking-tight">Welcome to Task Sphere</h1>
                    <p class="mt-2 max-w-md text-muted-foreground">
                        Organize your tasks, stay on top of deadlines, and get things done with ease. Let's make your day more productive, one task at
                        a time!
                    </p>
                </div>
            </div>
        </main>
    </div>
</template>
