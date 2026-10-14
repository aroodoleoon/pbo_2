package com.pos.server.repository

import com.pos.server.domain.model.Member
import com.pos.server.domain.model.Pelanggan
import java.sql.ResultSet
import java.util.UUID
import javax.sql.DataSource

class PelangganRepository(private val dataSource: DataSource) {

    private fun mapRow(rs: ResultSet): Pelanggan {
        val id = rs.getString("id")
        val nama = rs.getString("nama")
        val kontak = rs.getString("kontak")
        val nomorMember = rs.getString("nomor_member")

        return if (nomorMember != null) {
            val memberId = rs.getString("member_id") ?: id
            val poin = rs.getInt("poin")
            Member(
                id = id,
                nama = nama,
                kontak = kontak,
                nomorMember = nomorMember,
                poin = poin
            )
        } else {
            Pelanggan(id = id, nama = nama, kontak = kontak)
        }
    }

    fun findById(id: String): Pelanggan? {
        val sql = """
            SELECT p.id, p.nama, p.kontak, m.id as member_id, m.nomor_member, m.poin
            FROM pelanggan p
            LEFT JOIN member m ON m.pelanggan_id = p.id
            WHERE p.id = ?
        """.trimIndent()
        dataSource.connection.use { c ->
            c.prepareStatement(sql).use { s ->
                s.setString(1, id)
                s.executeQuery().use { rs ->
                    return if (rs.next()) mapRow(rs) else null
                }
            }
        }
    }

    fun findByQuery(query: String): List<Pelanggan> {
        val sql = """
            SELECT p.id, p.nama, p.kontak, m.id as member_id, m.nomor_member, m.poin
            FROM pelanggan p
            LEFT JOIN member m ON m.pelanggan_id = p.id
            WHERE LOWER(p.nama) LIKE ? OR p.kontak LIKE ? OR m.nomor_member LIKE ?
            ORDER BY p.nama ASC
        """.trimIndent()
        dataSource.connection.use { c ->
            c.prepareStatement(sql).use { s ->
                val q = "%${query.trim().lowercase()}%"
                s.setString(1, q)
                s.setString(2, q)
                s.setString(3, q)
                s.executeQuery().use { rs ->
                    val list = mutableListOf<Pelanggan>()
                    while (rs.next()) {
                        list.add(mapRow(rs))
                    }
                    return list
                }
            }
        }
    }

    fun findAll(): List<Pelanggan> {
        val sql = """
            SELECT p.id, p.nama, p.kontak, m.id as member_id, m.nomor_member, m.poin
            FROM pelanggan p
            LEFT JOIN member m ON m.pelanggan_id = p.id
            ORDER BY p.nama ASC
        """.trimIndent()
        dataSource.connection.use { c ->
            c.prepareStatement(sql).use { s ->
                s.executeQuery().use { rs ->
                    val list = mutableListOf<Pelanggan>()
                    while (rs.next()) {
                        list.add(mapRow(rs))
                    }
                    return list
                }
            }
        }
    }

    fun insertPelanggan(nama: String, kontak: String?): Pelanggan {
        val id = UUID.randomUUID().toString()
        val sql = "INSERT INTO pelanggan (id, nama, kontak) VALUES (?, ?, ?)"
        dataSource.connection.use { c ->
            c.prepareStatement(sql).use { s ->
                s.setString(1, id)
                s.setString(2, nama)
                s.setString(3, kontak)
                s.executeUpdate()
            }
        }
        return Pelanggan(id = id, nama = nama, kontak = kontak)
    }

    fun insertMember(nama: String, kontak: String?, nomorMember: String, poinAwal: Int = 0): Member {
        val pelangganId = UUID.randomUUID().toString()
        val memberId = UUID.randomUUID().toString()

        dataSource.connection.use { c ->
            c.autoCommit = false
            try {
                c.prepareStatement("INSERT INTO pelanggan (id, nama, kontak) VALUES (?, ?, ?)").use { s ->
                    s.setString(1, pelangganId)
                    s.setString(2, nama)
                    s.setString(3, kontak)
                    s.executeUpdate()
                }
                c.prepareStatement("INSERT INTO member (id, pelanggan_id, nomor_member, poin) VALUES (?, ?, ?, ?)").use { s ->
                    s.setString(1, memberId)
                    s.setString(2, pelangganId)
                    s.setString(3, nomorMember)
                    s.setInt(4, poinAwal)
                    s.executeUpdate()
                }
                c.commit()
            } catch (e: Exception) {
                c.rollback()
                throw e
            } finally {
                c.autoCommit = true
            }
        }

        return Member(
            id = pelangganId,
            nama = nama,
            kontak = kontak,
            nomorMember = nomorMember,
            poin = poinAwal
        )
    }

    fun updatePelanggan(id: String, nama: String?, kontak: String?) {
        val sql = "UPDATE pelanggan SET nama = COALESCE(?, nama), kontak = COALESCE(?, kontak) WHERE id = ?"
        dataSource.connection.use { c ->
            c.prepareStatement(sql).use { s ->
                s.setString(1, nama)
                s.setString(2, kontak)
                s.setString(3, id)
                s.executeUpdate()
            }
        }
    }

    fun updatePoin(memberNumber: String, deltaPoin: Int) {
        val sql = "UPDATE member SET poin = GREATEST(0, poin + ?) WHERE nomor_member = ?"
        dataSource.connection.use { c ->
            c.prepareStatement(sql).use { s ->
                s.setInt(1, deltaPoin)
                s.setString(2, memberNumber)
                s.executeUpdate()
            }
        }
    }
}

