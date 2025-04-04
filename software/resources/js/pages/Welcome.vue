<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import { useAppearance } from '@/composables/useAppearance';
import { getSystemTheme } from '@/lib/getSystemTheme';
import { Link } from '@inertiajs/vue3';
import * as THREE from 'three';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

// appearance system handles theme switching (light/dark/system) for the application
const { appearance } = useAppearance();

const possibleCanasColors = Object.freeze({
    light: 'hsl(0, 0%, 100%)',
    dark: 'hsl(222.2, 84%, 4.9%)',
});

const possibleSphereColors = Object.freeze({
    light: 'hsl(221.2, 83.2%, 53.3%)',
    dark: 'hsl(217.2, 91.2%, 59.8%)',
});

// pick the right canvas background color based on current theme
const currentCanvasBG = computed(() => {
    switch (appearance.value) {
        case 'light':
            return possibleCanasColors.light;
        case 'dark':
            return possibleCanasColors.dark;
        default:
            const theme = getSystemTheme();
            const color = theme === 'light' ? possibleCanasColors.light : possibleCanasColors.dark;
            return color;
    }
});

// pick the right sphere color based on current theme
const currentSphereColor = computed(() => {
    switch (appearance.value) {
        case 'light':
            return possibleSphereColors.light;
        case 'dark':
            return possibleSphereColors.dark;
        default:
            const theme = getSystemTheme();
            return theme === 'light' ? possibleSphereColors.light : possibleSphereColors.dark;
    }
});

// reference to the canvas element where three.js will render the scene
const canvas = ref<HTMLCanvasElement | null>(null);

// three.js core variables
let scene: THREE.Scene;
let camera: THREE.PerspectiveCamera;
let renderer: THREE.WebGLRenderer;
let controls: OrbitControls;
let sphere: THREE.Mesh;

// update background color when theme changes
watch(currentCanvasBG, (newValue) => {
    if (renderer) {
        renderer.setClearColor(newValue, 1);
    }
});

// update sphere color when theme changes
watch(currentSphereColor, (newValue) => {
    if (sphere && sphere.material) {
        (sphere.material as THREE.MeshBasicMaterial).color.set(newValue);
    }
});

// resize handler ensures the visualization remains properly scaled and centered
// when the browser window changes size
const onResize = () => {
    if (!camera || !renderer) return;
    // update camera aspect ratio to match new dimensions
    camera.aspect = window.innerWidth / window.innerHeight;
    camera.updateProjectionMatrix();
    // orbit controls must be updated after camera changes to maintain proper behavior
    controls.update();
    // resize the renderer to match window dimensions
    renderer.setSize(window.innerWidth, window.innerHeight);
};

onMounted(() => {
    if (!canvas.value) return;

    // initialize the three.js environment
    scene = new THREE.Scene();

    // set up the camera with perspective view (field of view, aspect ratio, clipping planes)
    camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
    camera.position.z = 5;

    // initialize the renderer with antialiasing for smoother edges
    renderer = new THREE.WebGLRenderer({ canvas: canvas.value, antialias: true });
    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.setClearColor(currentCanvasBG.value, 1);

    // add orbit controls to enable user interaction with the sphere (rotate, zoom, pan)
    controls = new OrbitControls(camera, renderer.domElement);
    controls.update();

    // create the task sphere using sphere geometry and wireframe material
    const geometry = new THREE.SphereGeometry(2.5, 32, 16);
    const material = new THREE.MeshBasicMaterial({ color: currentSphereColor.value, wireframe: true });
    sphere = new THREE.Mesh(geometry, material);
    scene.add(sphere);

    window.addEventListener('resize', onResize);

    // animation loop handles continuous rotation of the sphere on multiple axes
    // creating a dynamic, floating effect for the task sphere visualization
    const animate = () => {
        requestAnimationFrame(animate);
        sphere.rotation.x += 0.002;
        sphere.rotation.z += 0.002;
        sphere.rotation.y += 0.002;
        renderer.render(scene, camera);
    };
    animate();
});

// clean up three.js resources when component is unmounted to prevent memory leaks
onUnmounted(() => {
    window.removeEventListener('resize', onResize);
    renderer.dispose();
});
</script>
<template>
    <div class="min-h-screen w-full overflow-hidden">
        <!-- this header stays at the top of the page and shows the logo and login button -->
        <header class="fixed top-0 z-50 w-full border-b bg-background/50 backdrop-blur dark:bg-background/80">
            <div class="max-w-360 mx-auto flex items-center justify-between gap-5 px-4 py-3 md:px-10">
                <!-- logo and app name -->
                <div class="flex items-center justify-center gap-2">
                    <AppLogoIcon class="h-4 w-4 fill-primary" />
                    <span class="text-lg font-bold">Task Sphere</span>
                </div>
                <!-- navigation links that change based on if user is logged in -->
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

        <!-- main area where the 3d sphere and welcome text is shown -->
        <main>
            <div class="flex min-h-screen w-full items-center justify-center">
                <!-- this canvas is where three.js draws the 3d sphere -->
                <canvas ref="canvas" class="fixed inset-0 z-10"></canvas>

                <!-- this box shows the welcome message on top of the 3d sphere with a blur effect -->
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