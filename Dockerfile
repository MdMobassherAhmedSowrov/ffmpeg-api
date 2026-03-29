FROM rust:alpine as builder
RUN apk add --no-cache musl-dev
RUN cargo install --version 1.7.0 gifski

FROM node:18-alpine
COPY --from=builder /usr/local/cargo/bin/gifski /usr/local/bin/gifski

RUN apk add --no-cache \
      chromium \
      nss \
      freetype \
      freetype-dev \
      harfbuzz \
      ca-certificates \
      ttf-freefont \
      git \
      libwebp-tools \
      ffmpeg \
      php \
      php-cli \
      php-curl \
      php-json \
      php-openssl \
      php-phar \
      php-mbstring

ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD true
ENV CHROMIUM_PATH /usr/bin/chromium-browser
ENV USE_SANDBOX false

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY . .

CMD sh -c "php -S 0.0.0.0:${PORT:-10000}"
