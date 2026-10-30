4. Dominios de imágenes externas autorizados en `next.config.ts` (`images.remotePatterns`)
5. Auto-deploy activado en cada push a `main`

### Backend — Render ✅ En producción

**URL:** https://ejercicio-pedidos.onrender.com

1. Código en GitHub (repo `ejercicio-pedidos-api`)
2. Servicio tipo **Web Service** con runtime **Docker**, usando el `dockerfile` del repo
3. Base de datos **PostgreSQL** creada en Render (servicio `tienda-db`)
4. Variables de entorno configuradas en el panel de Render (`APP_ENV=production`, `APP_DEBUG=false`, credenciales de `tienda-db`, claves de Stripe, `JWT_SECRET`, etc.)
5. Auto-deploy activado en cada push a `main`

---

## 📄 Licencia

Este proyecto está bajo la licencia **MIT**. Ver el archivo [LICENSE](LICENSE) para más detalles.

---

## 👤 Autor

**Juan Guzman**

- GitHub: [@juanwright101084-svg](https://github.com/juanwright101084-svg)
- Repositorio: [mercaditosv](https://github.com/juanwright101084-svg/mercaditosv)
- Demo: [mercaditosv-rho.vercel.app](https://mercaditosv-rho.vercel.app)
- Email: juanwright101084@gmail.com

---

## 🙏 Agradecimientos

- [Next.js](https://nextjs.org) — Framework de React
- [Laravel](https://laravel.com) — Framework de PHP
- [Stripe](https://stripe.com) — Procesamiento de pagos
- [Tailwind CSS](https://tailwindcss.com) — Framework de CSS
- [Lucide](https://lucide.dev) — Iconos
- [Vercel](https://vercel.com) — Hosting del frontend
- [Render](https://render.com) — Hosting del backend

---

<div align="center">

**⭐ Si te gustó este proyecto, dale una estrella en GitHub ⭐**

Hecho con ❤️ por [Juan Guzman](https://github.com/juanwright101084-svg)

</div>