FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    ffmpeg \
    python3 \
    python3-pip \
    libcairo2 \
    libpango-1.0-0 \
    libpangocairo-1.0-0 \
    libgdk-pixbuf2.0-0 \
    shared-mime-info \
    && rm -rf /var/lib/apt/lists/*

RUN pip3 install lottie cairosvg --break-system-packages

WORKDIR /app
COPY . .

CMD php -S 0.0.0.0:${PORT:-10000}
