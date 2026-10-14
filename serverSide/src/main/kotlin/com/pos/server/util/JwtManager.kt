package com.pos.server.util

import java.util.UUID

object JwtManager {
    fun generateSimpleToken(userId: String, role: String): String {
        return "TOKEN-$role-$userId-${UUID.randomUUID()}"
    }
}

