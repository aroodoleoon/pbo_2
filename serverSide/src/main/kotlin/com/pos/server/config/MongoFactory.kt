package com.pos.server.config

import com.mongodb.client.MongoClient
import com.mongodb.client.MongoClients
import com.mongodb.client.MongoDatabase
import org.bson.Document
import org.slf4j.LoggerFactory

object MongoFactory {
    private val logger = LoggerFactory.getLogger(MongoFactory::class.java)
    private var client: MongoClient? = null
    private var database: MongoDatabase? = null

    fun getDatabase(uri: String): MongoDatabase? {
        if (database != null) return database
        return try {
            val mongoClient = MongoClients.create(uri)
            val dbName = uri.substringAfterLast("/").substringBefore("?").ifBlank { "pos_logs" }
            val db = mongoClient.getDatabase(dbName)
            // Test ping with short timeout
            db.runCommand(Document("ping", 1))
            logger.info("[MONGODB] Berhasil terhubung ke MongoDB ($dbName): $uri")
            client = mongoClient
            database = db
            db
        } catch (e: Exception) {
            logger.warn("[MONGODB WARNING] Tidak dapat terhubung ke MongoDB: ${e.message}. Menggunakan fallback logging.")
            null
        }
    }

    fun close() {
        try {
            client?.close()
        } catch (_: Exception) {}
        client = null
        database = null
    }
}

