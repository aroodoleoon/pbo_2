package com.pos.server.repository

import com.pos.server.domain.model.AuditLog
import java.sql.Timestamp
import javax.sql.DataSource

class AuditLogRepository(private val dataSource: DataSource) {

    fun insert(log: AuditLog) {
        val sql = """
            INSERT INTO audit_logs (id, timestamp, actor_id, action, entity_type, entity_id, result, metadata, request_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?::jsonb, ?)
        """.trimIndent()
        dataSource.connection.use { c ->
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
    }
}

