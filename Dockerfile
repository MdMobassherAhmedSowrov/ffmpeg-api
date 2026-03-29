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

# ম্যাজিক স্টেপ: ম্যানুয়ালি আপলোড করার বদলে ডকার নিজে থেকেই ফাইলগুলো ডাউনলোড করে নিবে
RUN git clone https://github.com/WooMai/tgs-to-gif.git /tmp/tgs \
    && cp /tmp/tgs/package.json /tmp/tgs/package-lock.json ./ \
    && npm ci \
    && cp /tmp/tgs/*.js ./ \
    && rm -rf /tmp/tgs

# আপনার রিপোজিটরির t.php এবং index.php কপি করবে
COPY . .

CMD sh -c "php -S 0.0.0.0:${PORT:-10000}"
