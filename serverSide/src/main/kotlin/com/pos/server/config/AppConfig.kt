package com.pos.server.config

data class DatabaseConfig(
    val url: String,
    val user: String,
    val pass: String
)

data class AppConfig(
    val dbConfig: DatabaseConfig,
    val mongoUri: String
) {
    companion object {
        fun load(): AppConfig {
            val host = System.getenv("POSTGRES_HOST") ?: "localhost"
            val port = System.getenv("POSTGRES_PORT") ?: "5432"
            val dbName = System.getenv("POSTGRES_DB") ?: "pos_db"
            val user = System.getenv("POSTGRES_USER") ?: "pos_app"
            val pass = System.getenv("POSTGRES_PASSWORD") ?: "pos_password"

            val rawUrl = System.getenv("JDBC_DATABASE_URL") ?: "jdbc:postgresql://$host:$port/$dbName"
            val url = if (rawUrl.startsWith("jdbc:postgresql") && !rawUrl.contains("stringtype=")) {
                if (rawUrl.contains("?")) "$rawUrl&stringtype=unspecified" else "$rawUrl?stringtype=unspecified"
            } else rawUrl

            val mongoUri = System.getenv("MONGO_URI") ?: "mongodb://localhost:27017/pos_logs"

            return AppConfig(
                dbConfig = DatabaseConfig(
                    url = url,
                    user = user,
                    pass = pass
                ),
                mongoUri = mongoUri
            )
        }
    }
}