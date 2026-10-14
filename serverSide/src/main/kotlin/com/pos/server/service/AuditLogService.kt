package com.pos.server.service

import com.pos.server.domain.model.AuditLog
import com.pos.server.repository.AuditLogRepository

class AuditLogService(
    private val auditLogRepository: AuditLogRepository
) {
    fun log(
        actorId: String?,
        action: String,
        entityType: String,
        entityId: String?,
        result: String = "SUCCESS",
        metadata: String? = null,
        requestId: String? = null
    ) {
        try {
            val logEntry = AuditLog(
                actorId = actorId,
                action = action,
                entityType = entityType,
                entityId = entityId,
                result = result,
                metadata = metadata,
                requestId = requestId
            )
            auditLogRepository.insert(logEntry)
        } catch (e: Exception) {
            // Log audit tidak boleh menggagalkan alur transaksi utama jika terjadi kegagalan non-kritis
            System.err.println("[AUDIT LOG ERROR] Gagal mencatat audit log: ${e.message}")
        }
    }
}

