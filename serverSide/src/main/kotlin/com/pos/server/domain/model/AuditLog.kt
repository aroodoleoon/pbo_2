package com.pos.server.domain.model

import java.time.Instant
import java.util.UUID

data class AuditLog(
    val id: String = UUID.randomUUID().toString(),
    val timestamp: Instant = Instant.now(),
    val actorId: String? = null,
    val action: String,
    val entityType: String,
    val entityId: String? = null,
    val result: String,
    val metadata: String? = null,
    val requestId: String? = null
)

