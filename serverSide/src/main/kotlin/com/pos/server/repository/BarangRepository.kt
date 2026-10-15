package com.pos.server.repository

import com.pos.server.domain.exception.BarangTidakDitemukanException
import com.pos.server.domain.model.Barang
import java.sql.Connection
import java.sql.ResultSet
import javax.sql.DataSource

class BarangRepository(val dataSource: DataSource) {

    private fun mapRow(rs: ResultSet): Barang {
        val barang = Barang(
            id = rs.getString("id"),
            kode = rs.getString("kode"),
            nama = rs.getString("nama"),
            kategoriId = rs.getString("kategori_id"),
            hargaBeli = rs.getLong("harga_beli"),
            hargaJual = rs.getLong("harga_jual"),
            stokAwal = rs.getInt("stok"),
            stokMinimum = rs.getInt("stok_minimum"),
            aktif = rs.getBoolean("aktif")
        )
        return barang
    }

    fun findById(id: String): Barang? {
        val sql = "SELECT * FROM barang WHERE id = ?"
        dataSource.connection.use { c ->
            c.prepareStatement(sql).use { s ->
                s.setString(1, id)
                s.executeQuery().use { rs ->
                    return if (rs.next()) mapRow(rs) else null
                }
            }
        }
    }

    fun findByIdOrThrow(id: String): Barang {
        return findById(id) ?: throw BarangTidakDitemukanException("Barang dengan ID '$id' tidak ditemukan.")
    }

    fun findByKode(kode: String): Barang? {
        val sql = "SELECT * FROM barang WHERE kode = ?"
        dataSource.connection.use { c ->
            c.prepareStatement(sql).use { s ->
                s.setString(1, kode)
                s.executeQuery().use { rs ->
                    return if (rs.next()) mapRow(rs) else null
                }
            }
        }
    }

    fun findAll(keyword: String? = null, categoryId: String? = null, onlyActive: Boolean = true): List<Barang> {
        val sql = StringBuilder("SELECT * FROM barang WHERE 1=1")
        val params = mutableListOf<Any>()

        if (onlyActive) {
            sql.append(" AND aktif = TRUE")
        }
        if (!categoryId.isNullOrBlank()) {
            sql.append(" AND kategori_id = ?")
            params.add(categoryId)
        }
        if (!keyword.isNullOrBlank()) {
            sql.append(" AND (LOWER(nama) LIKE ? OR LOWER(kode) LIKE ?)")
            val kw = "%${keyword.lowercase()}%"
            params.add(kw)
            params.add(kw)
        }
        sql.append(" ORDER BY nama ASC")

        dataSource.connection.use { c ->
            c.prepareStatement(sql.toString()).use { s ->
                params.forEachIndexed { i, p ->
                    s.setObject(i + 1, p)
                }
                s.executeQuery().use { rs ->
                    val list = mutableListOf<Barang>()
                    while (rs.next()) {
                        list.add(mapRow(rs))
                    }
                    return list
                }
            }
        }
    }

    fun insert(barang: Barang) {
        val sql = """
            INSERT INTO barang (id, kode, nama, kategori_id, harga_beli, harga_jual, stok, stok_minimum, aktif)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        """.trimIndent()
        dataSource.connection.use { c ->
            c.prepareStatement(sql).use { s ->
                s.setString(1, barang.id)
                s.setString(2, barang.kode)
                s.setString(3, barang.nama)
                if (barang.kategoriId != null) {
                    s.setString(4, barang.kategoriId)
                } else {
                    s.setNull(4, java.sql.Types.VARCHAR)
                }
                s.setLong(5, barang.hargaBeli)
                s.setLong(6, barang.hargaJual)
                s.setInt(7, barang.stok)
                s.setInt(8, barang.stokMinimum)
                s.setBoolean(9, barang.aktif)
                s.executeUpdate()
            }
        }
    }

    fun update(barang: Barang) {
        val sql = """
            UPDATE barang 
            SET kode = ?, nama = ?, kategori_id = ?, harga_beli = ?, harga_jual = ?, stok = ?, stok_minimum = ?, aktif = ?
            WHERE id = ?
        """.trimIndent()
        dataSource.connection.use { c ->
            c.prepareStatement(sql).use { s ->
                s.setString(1, barang.kode)
                s.setString(2, barang.nama)
                if (barang.kategoriId != null) {
                    s.setString(3, barang.kategoriId)
                } else {
                    s.setNull(3, java.sql.Types.VARCHAR)
                }
                s.setLong(4, barang.hargaBeli)
                s.setLong(5, barang.hargaJual)
                s.setInt(6, barang.stok)
                s.setInt(7, barang.stokMinimum)
                s.setBoolean(8, barang.aktif)
                s.setString(9, barang.id)
                s.executeUpdate()
            }
        }
    }

    fun updateStock(connection: Connection, barangId: String, newStock: Int) {
        val sql = "UPDATE barang SET stok = ? WHERE id = ?"
        connection.prepareStatement(sql).use { s ->
            s.setInt(1, newStock)
            s.setString(2, barangId)
            s.executeUpdate()
        }
    }

    fun findByIdWithConnection(connection: Connection, id: String): Barang? {
        val sql = "SELECT * FROM barang WHERE id = ? FOR UPDATE"
        connection.prepareStatement(sql).use { s ->
            s.setString(1, id)
            s.executeQuery().use { rs ->
                return if (rs.next()) mapRow(rs) else null
            }
        }
    }
}

