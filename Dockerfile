FROM php:8.2-cli

RUN apt-get update && apt-get install -y ffmpeg python3 python3-pip

RUN pip3 install lottie

WORKDIR /app
COPY . .

CMD ["php", "-S", "0.0.0.0:10000"]
