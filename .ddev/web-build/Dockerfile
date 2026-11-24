# Bun — пакетный менеджер проекта (см. docs/development/local-environment.md).
#
# Node в образе DDEV уже есть, но Bun нужен внутри контейнера, а не только на
# хосте: `bun install` и `bun run build` выполняются в том же окружении, где
# работает Vite, иначе платформенные бинарники (sass-embedded, esbuild)
# разъезжаются между хостом и контейнером.
ARG BASE_IMAGE
FROM $BASE_IMAGE

# Версия зафиксирована: сборка образа не должна менять поведение сборки ассетов.
RUN npm install -g bun@1.4.2 \
	&& bun --version
