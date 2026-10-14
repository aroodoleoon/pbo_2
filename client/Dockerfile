FROM php:8.2-cli

WORKDIR /app

# Install curl extension if needed
RUN apt-get update && apt-get install -y libcurl4-openssl-dev \
    && docker-php-ext-install curl \
    && rm -rf /var/lib/apt/lists/*

# Copy client source code
COPY . /app

EXPOSE 80

# Start PHP built-in web server
CMD ["php", "-S", "0.0.0.0:80", "-t", "/app"]
