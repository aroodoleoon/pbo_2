package com.pos.server.config

data class DatabaseConfig(
    val url: String,
    val user: String,
    val pass: String
)

data class AppConfig(
    val dbConfig: DatabaseConfig
) {
    companion object {
        fun load(): AppConfig {
            val host = System.getenv("POSTGRES_HOST") ?: "localhost"
            val port = System.getenv("POSTGRES_PORT") ?: "5432"
            val dbName = System.getenv("POSTGRES_DB") ?: "pos_db"
            val user = System.getenv("POSTGRES_USER") ?: "pos_app"
            val pass = System.getenv("POSTGRES_PASSWORD") ?: "pos_password"

            val url = System.getenv("JDBC_DATABASE_URL") ?: "jdbc:postgresql://$host:$port/$dbName"

            return AppConfig(
                dbConfig = DatabaseConfig(
                    url = url,
                    user = user,
                    pass = pass
                )
            )
        }
    }
}