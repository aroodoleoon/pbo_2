FROM gradle:8.10-jdk21 AS build
WORKDIR /app
COPY . .
RUN gradle installDist --no-daemon

FROM eclipse-temurin:21-jre-alpine
WORKDIR /app
COPY --from=build /app/build/install/serverSide .
EXPOSE 8080
ENTRYPOINT ["./bin/serverSide"]

