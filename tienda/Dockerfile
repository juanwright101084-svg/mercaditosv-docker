# syntax=docker/dockerfile:1.7
# =============================================================================
# Frontend Next.js 16 (App Router) - imagen de producción multi-stage
#   base    -> Node + pnpm (corepack) compartidos por las etapas de build
#   deps    -> instala dependencias (capa cacheada mientras no cambie el lockfile)
#   builder -> next build (genera .next/standalone)
#   runner  -> imagen final mínima: solo lo necesario para ejecutar el servidor
# =============================================================================
ARG NODE_VERSION=20

# ---------- base ----------
FROM node:${NODE_VERSION}-alpine AS base
ENV PNPM_HOME=/pnpm \
    npm_config_store_dir=/pnpm/store \
    COREPACK_ENABLE_DOWNLOAD_PROMPT=0 \
    NEXT_TELEMETRY_DISABLED=1
ENV PATH="${PNPM_HOME}:${PATH}"
# corepack lee "packageManager" de package.json y usa exactamente esa versión de pnpm
RUN corepack enable
WORKDIR /app

# ---------- deps ----------
FROM base AS deps
# Solo los archivos que definen dependencias: si no cambian, Docker reutiliza esta capa
COPY package.json pnpm-lock.yaml pnpm-workspace.yaml ./
RUN --mount=type=cache,id=pnpm-store,target=/pnpm/store \
    pnpm install --frozen-lockfile

# ---------- builder ----------
FROM base AS builder
COPY --from=deps /app/node_modules ./node_modules
COPY . .
# Las variables NEXT_PUBLIC_* se incrustan en el JavaScript del navegador durante
# el build, por eso entran como ARG (y no como variables de runtime).
ARG NEXT_PUBLIC_API_URL
ARG NEXT_PUBLIC_IMAGE_URL
ARG NEXT_PUBLIC_STRIPE_PUBLISHABLE_KEY
ENV NEXT_PUBLIC_API_URL=${NEXT_PUBLIC_API_URL} \
    NEXT_PUBLIC_IMAGE_URL=${NEXT_PUBLIC_IMAGE_URL} \
    NEXT_PUBLIC_STRIPE_PUBLISHABLE_KEY=${NEXT_PUBLIC_STRIPE_PUBLISHABLE_KEY}
RUN pnpm build

# ---------- runner (imagen final) ----------
FROM node:${NODE_VERSION}-alpine AS runner
ENV NODE_ENV=production \
    NEXT_TELEMETRY_DISABLED=1 \
    PORT=3000 \
    HOSTNAME=0.0.0.0
WORKDIR /app

# Usuario sin privilegios
RUN addgroup -S nodejs -g 1001 && adduser -S nextjs -u 1001 -G nodejs

# Salida "standalone": servidor mínimo con solo las dependencias que realmente se usan
COPY --from=builder --chown=nextjs:nodejs /app/public ./public
COPY --from=builder --chown=nextjs:nodejs /app/.next/standalone ./
COPY --from=builder --chown=nextjs:nodejs /app/.next/static ./.next/static

USER nextjs
EXPOSE 3000

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
  CMD node -e "require('net').connect(3000,'127.0.0.1').on('connect',()=>process.exit(0)).on('error',()=>process.exit(1))"

CMD ["node", "server.js"]
