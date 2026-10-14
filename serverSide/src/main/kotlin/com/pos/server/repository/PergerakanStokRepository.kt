package com.pos.server.repository

import com.pos.server.domain.enums.StockMovementType
import com.pos.server.domain.model.PergerakanStok
import java.sql.Connection
import java.sql.Timestamp
import java.time.Instant
import javax.sql.DataSource

class PergerakanStokRepository(private val dataSource: DataSource) {

    fun insert(connection: Connection, pergerakan: PergerakanStok) {
        val sql = """
            INSERT INTO pergerakan_stok (id, barang_id, jenis, jumlah, alasan, karyawan_id, dibuat_pada)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        """.trimIndent()
        connection.prepareStatement(sql).use { s ->
            s.setString(1, pergerakan.id)
            s.setString(2, pergerakan.barangId)
            s.setString(3, pergerakan.jenis.name)
            s.setInt(4, pergerakan.jumlah)
            s.setString(5, pergerakan.alasan)
            s.setString(6, pergerakan.karyawanId)
            s.setTimestamp(7, Timestamp.from(pergerakan.dibuatPada))
            s.executeUpdate()
        }
    }

    fun findMovements(
        productId: String? = null,
        from: Instant? = null,
        to: Instant? = null
    ): List<PergerakanStok> {
        val sql = StringBuilder("SELECT * FROM pergerakan_stok WHERE 1=1")
        val params = mutableListOf<Any>()

        if (!productId.isNullOrBlank()) {
            sql.append(" AND barang_id = ?")
            params.add(productId)
        }
        if (from != null) {
            sql.append(" AND dibuat_pada >= ?")
            params.add(Timestamp.from(from))
        }
        if (to != null) {
            sql.append(" AND dibuat_pada <= ?")
            params.add(Timestamp.from(to))
        }
        sql.append(" ORDER BY dibuat_pada DESC")

        dataSource.connection.use { c ->
            c.prepareStatement(sql.toString()).use { s ->
                params.forEachIndexed { i, p -> s.setObject(i + 1, p) }
                s.executeQuery().use { rs ->
                    val list = mutableListOf<PergerakanStok>()
                    while (rs.next()) {
                        list.add(
                            PergerakanStok(
                                id = rs.getString("id"),
                                barangId = rs.getString("barang_id"),
                                jenis = StockMovementType.fromString(rs.getString("jenis")),
                                jumlah = rs.getInt("jumlah"),
                                alasan = rs.getString("alasan"),
                                karyawanId = rs.getString("karyawan_id"),
                                dibuatPada = rs.getTimestamp("dibuat_pada").toInstant()
                            )
                        )
                    }
                    return list
                }
            }
        }
    }
}

