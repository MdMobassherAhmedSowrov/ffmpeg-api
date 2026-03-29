FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    ffmpeg \
    python3 \
    python3-pip \
    python3-cairosvg \
    shared-mime-info \
    && rm -rf /var/lib/apt/lists/*

RUN pip3 install lottie --break-system-packages

WORKDIR /app
COPY . .

CMD sh -c "php -S 0.0.0.0:${PORT:-10000}"
