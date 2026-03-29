FROM php:8.2-cli

RUN apt-get update && apt-get install -y ffmpeg nodejs npm

RUN npm install -g lottie-web lottie-cli

WORKDIR /app
COPY . .

CMD ["php", "-S", "0.0.0.0:10000"]
