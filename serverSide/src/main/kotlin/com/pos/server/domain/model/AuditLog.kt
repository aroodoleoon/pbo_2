package com.pos.server.domain.model

import com.pos.server.util.InstantSerializer
import kotlinx.serialization.Serializable
import java.time.Instant
import java.util.UUID

@Serializable
data class AuditLog(
    val id: String = UUID.randomUUID().toString(),
    @Serializable(with = InstantSerializer::class)
    val timestamp: Instant = Instant.now(),
    val actorId: String? = null,
    val action: String,
    val entityType: String,
    val entityId: String? = null,
    val result: String,
    val metadata: String? = null,
    val requestId: String? = null
)
