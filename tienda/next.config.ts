import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  // Genera .next/standalone: servidor mínimo para la imagen Docker de producción
  output: "standalone",
  turbopack: {
    rules: {
      "*.css": {
        loaders: ["@tailwindcss/turbopack"],
        as: "*.css",
      },
    },
  },
  images: {
    remotePatterns: [
      {
        protocol: "https",
        hostname: "ejercicio-pedidos.onrender.com",
      },
    ],
  },
};

export default nextConfig;
