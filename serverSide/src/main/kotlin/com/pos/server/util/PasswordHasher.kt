package com.pos.server.util

import java.security.MessageDigest

object PasswordHasher {
    fun hash(raw: String): String {
        val digest = MessageDigest.getInstance("SHA-256")
        val bytes = digest.digest(raw.toByteArray(Charsets.UTF_8))
        return bytes.joinToString("") { "%02x".format(it) }
    }

    fun verify(raw: String, hashed: String): Boolean {
        return hash(raw) == hashed
    }
}

