import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import react from "@vitejs/plugin-react";

export default defineConfig({
    plugins: [
        laravel({
            input: "resources/js/app.jsx",
            refresh: true,
        }),
        react(),
    ],
});

//run this
/* For Command:
	Laravel: php artisan serve --host=192.168.10.101 --port=8000
	Vite: npm run dev -- --host
192.168.10.101 */
