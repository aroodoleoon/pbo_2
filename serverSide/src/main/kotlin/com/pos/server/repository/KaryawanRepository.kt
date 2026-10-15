package com.pos.server.repository

import com.pos.server.domain.enums.Role
import com.pos.server.domain.model.Admin
import com.pos.server.domain.model.Karyawan
import com.pos.server.domain.model.Kasir
import java.sql.ResultSet
import javax.sql.DataSource

class KaryawanRepository(private val dataSource: DataSource) {

    private fun mapRow(rs: ResultSet): Karyawan {
        val id = rs.getString("id")
        val nama = rs.getString("nama")
        val username = rs.getString("username")
        val passwordHash = rs.getString("password_hash")
        val roleStr = rs.getString("role")
        val aktif = rs.getBoolean("aktif")

        return when (Role.fromString(roleStr)) {
            Role.ADMIN -> Admin(id, nama, username, passwordHash, aktif)
            Role.CASHIER -> Kasir(id, nama, username, passwordHash, aktif)
        }
    }

    fun findByUsername(username: String): Karyawan? {
        val sql = "SELECT * FROM karyawan WHERE LOWER(username) = LOWER(?)"
        dataSource.connection.use { c ->
            c.prepareStatement(sql).use { s ->
                s.setString(1, username)
                s.executeQuery().use { rs ->
                    return if (rs.next()) mapRow(rs) else null
                }
            }
        }
    }

    fun findById(id: String): Karyawan? {
        val sql = "SELECT * FROM karyawan WHERE id = ?"
        dataSource.connection.use { c ->
            c.prepareStatement(sql).use { s ->
                s.setString(1, id)
                s.executeQuery().use { rs ->
                    return if (rs.next()) mapRow(rs) else null
                }
            }
        }
    }

    fun findAll(): List<Karyawan> {
        val sql = "SELECT * FROM karyawan ORDER BY nama ASC"
        dataSource.connection.use { c ->
            c.prepareStatement(sql).use { s ->
                s.executeQuery().use { rs ->
                    val list = mutableListOf<Karyawan>()
                    while (rs.next()) {
                        list.add(mapRow(rs))
                    }
                    return list
                }
            }
        }
    }

    fun insert(karyawan: Karyawan) {
        val sql = """
            INSERT INTO karyawan (id, nama, username, password_hash, role, aktif)
            VALUES (?, ?, ?, ?, ?, ?)
        """.trimIndent()
        dataSource.connection.use { c ->
            c.prepareStatement(sql).use { s ->
                s.setString(1, karyawan.id)
                s.setString(2, karyawan.nama)
                s.setString(3, karyawan.username)
                s.setString(4, karyawan.passwordHash)
                s.setString(5, karyawan.role.name)
                s.setBoolean(6, karyawan.aktif)
                s.executeUpdate()
            }
        }
    }

    fun updateStatus(id: String, aktif: Boolean) {
        val sql = "UPDATE karyawan SET aktif = ? WHERE id = ?"
        dataSource.connection.use { c ->
            c.prepareStatement(sql).use { s ->
                s.setBoolean(1, aktif)
                s.setString(2, id)
                s.executeUpdate()
            }
        }
    }

    fun delete(id: String) {
        try {
            val sql = "DELETE FROM karyawan WHERE id = ?"
            dataSource.connection.use { c ->
                c.prepareStatement(sql).use { s ->
                    s.setString(1, id)
                    s.executeUpdate()
                }
            }
        } catch (_: Exception) {
            updateStatus(id, false)
        }
    }
}

