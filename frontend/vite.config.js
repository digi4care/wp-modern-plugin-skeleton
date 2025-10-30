import { defineConfig } from "vite";
import react from "@vitejs/plugin-react";
import tailwindcss from "@tailwindcss/vite";
import { resolve } from 'path';

export default defineConfig(
	{
		plugins: [react(), tailwindcss()],
		define: {
			'process.env.NODE_ENV': JSON.stringify( process.env.NODE_ENV || 'development' ),
		},
		build: {
			manifest: true,
			outDir: "../dist",
			emptyOutDir: true,
			cssCodeSplit: true,
			lib: {
				entry: {
					'blocks': './blocks/index.js',
					'app': './i18n-loader.js',
				},
				name: 'wpModernPlugin',
				formats: ['es'],
			},
			rollupOptions: {
				external: [
				'react',
				'react-dom',
				'@wordpress/blocks',
				'@wordpress/block-editor',
				'@wordpress/components',
				'@wordpress/element',
				'@wordpress/i18n',
				],
				output: {
					globals: {
						react: 'React',
						'react-dom': 'ReactDOM',
						'@wordpress/blocks': 'wp.blocks',
						'@wordpress/block-editor': 'wp.blockEditor',
						'@wordpress/components': 'wp.components',
						'@wordpress/element': 'wp.element',
						'@wordpress/i18n': 'wp.i18n',
					},
					assetFileNames: '[name].[ext]',
					entryFileNames: '[name].js',
					chunkFileNames: '[name].js',
				},
			},
		},
		assetsInclude: ["**/*.json"],
		server: {
			host: "0.0.0.0",
			port: 5173,
			strictPort: true,
		},
	}
);
