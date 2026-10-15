package com.pos.server.repository

import com.mongodb.client.MongoDatabase
import com.mongodb.client.model.Sorts
import com.pos.server.domain.model.AuditLog
import org.bson.Document
import java.sql.Timestamp
import java.time.Instant
import java.util.Date
import javax.sql.DataSource

class AuditLogRepository(
    private val dataSource: DataSource,
    private val mongoDatabase: MongoDatabase? = null
) {
    fun insert(log: AuditLog) {
        // 1. Tulis ke MongoDB (NoSQL) jika tersedia
        if (mongoDatabase != null) {
            try {
                val collection = mongoDatabase.getCollection("audit_logs")
                val doc = Document()
                    .append("_id", log.id)
                    .append("timestamp", Date.from(log.timestamp))
                    .append("actorId", log.actorId)
                    .append("action", log.action)
                    .append("entityType", log.entityType)
                    .append("entityId", log.entityId)
                    .append("result", log.result)
                    .append("metadata", log.metadata)
                    .append("requestId", log.requestId)
                collection.insertOne(doc)
            } catch (e: Exception) {
                System.err.println("[MONGODB LOG ERROR] Gagal insert log ke MongoDB: ${e.message}")
            }
        }

        // 2. Tulis ke Relational Database (Audit Logs table) sebagai secondary / fallback
        try {
            dataSource.connection.use { c ->
                val isPg = c.metaData.databaseProductName.contains("PostgreSQL", ignoreCase = true)
                val sql = if (isPg) {
                    """
                    INSERT INTO audit_logs (id, timestamp, actor_id, action, entity_type, entity_id, result, metadata, request_id)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?::jsonb, ?)
                    """.trimIndent()
                } else {
                    """
                    INSERT INTO audit_logs (id, timestamp, actor_id, action, entity_type, entity_id, result, metadata, request_id)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                    """.trimIndent()
                }

                c.prepareStatement(sql).use { s ->
                    s.setString(1, log.id)
                    s.setTimestamp(2, Timestamp.from(log.timestamp))
                    s.setString(3, log.actorId)
                    s.setString(4, log.action)
                    s.setString(5, log.entityType)
                    s.setString(6, log.entityId)
                    s.setString(7, log.result)
                    s.setString(8, log.metadata ?: "{}")
                    s.setString(9, log.requestId)
                    s.executeUpdate()
                }
            }
        } catch (_: Exception) {
            // Abaikan jika tabel audit_logs pada DB relational belum diinisialisasi
        }
    }

    fun getRecentLogs(limit: Int = 50): List<AuditLog> {
        // Prioritaskan pembacaan dari NoSQL MongoDB
        if (mongoDatabase != null) {
            try {
                val collection = mongoDatabase.getCollection("audit_logs")
                val docs = collection.find()
                    .sort(Sorts.descending("timestamp"))
                    .limit(limit)
                return docs.map { doc ->
                    val ts = when (val t = doc.get("timestamp")) {
                        is Date -> t.toInstant()
                        is String -> Instant.parse(t)
                        else -> Instant.now()
                    }
                    AuditLog(
                        id = doc.getString("_id") ?: "",
                        timestamp = ts,
                        actorId = doc.getString("actorId"),
                        action = doc.getString("action") ?: "",
                        entityType = doc.getString("entityType") ?: "",
                        entityId = doc.getString("entityId"),
                        result = doc.getString("result") ?: "SUCCESS",
                        metadata = doc.getString("metadata"),
                        requestId = doc.getString("requestId")
                    )
                }.toList()
            } catch (e: Exception) {
                System.err.println("[MONGODB FETCH ERROR] Gagal membaca log dari MongoDB: ${e.message}")
            }
        }

        // Fallback baca dari Relational Database
        return try {
            dataSource.connection.use { c ->
                val isPg = c.metaData.databaseProductName.contains("PostgreSQL", ignoreCase = true)
                val sql = if (isPg) {
                    """
                    SELECT id, timestamp, actor_id, action, entity_type, entity_id, result, metadata::text, request_id
                    FROM audit_logs
                    ORDER BY timestamp DESC
                    LIMIT ?
                    """.trimIndent()
                } else {
                    """
                    SELECT id, timestamp, actor_id, action, entity_type, entity_id, result, metadata, request_id
                    FROM audit_logs
                    ORDER BY timestamp DESC
                    LIMIT ?
                    """.trimIndent()
                }

                c.prepareStatement(sql).use { s ->
                    s.setInt(1, limit)
                    s.executeQuery().use { rs ->
                        val list = mutableListOf<AuditLog>()
                        while (rs.next()) {
                            list.add(
                                AuditLog(
                                    id = rs.getString("id"),
                                    timestamp = rs.getTimestamp("timestamp").toInstant(),
                                    actorId = rs.getString("actor_id"),
                                    action = rs.getString("action"),
                                    entityType = rs.getString("entity_type"),
                                    entityId = rs.getString("entity_id"),
                                    result = rs.getString("result"),
                                    metadata = rs.getString("metadata"),
                                    requestId = rs.getString("request_id")
                                )
                            )
                        }
                        list
                    }
                }
            }
        } catch (_: Exception) {
            emptyList()
        }
    }
}
