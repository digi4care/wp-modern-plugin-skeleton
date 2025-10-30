import { defineConfig } from "vite";
import react from "@vitejs/plugin-react";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [react(), tailwindcss()],
    define: {
        "process.env.NODE_ENV": JSON.stringify("production"),
    },
    build: {
        manifest: true,
        outDir: "../dist",
        emptyOutDir: true,
        cssCodeSplit: true,
        rollupOptions: {
            input: {
                app: "./i18n-loader.js",
            },
            output: {
                format: "es",
                entryFileNames: "[name].js",
                assetFileNames: "[name].[ext]",
                chunkFileNames: "[name].js",
                globals: {
                    react: "React",
                    "react-dom": "ReactDOM",
                    "@wordpress/i18n": "wp.i18n",
                },
            },
        },
    },
    assetsInclude: ["**/*.json"],
    server: {
        host: "0.0.0.0",
        port: 5173,
        strictPort: true,
    },
});
