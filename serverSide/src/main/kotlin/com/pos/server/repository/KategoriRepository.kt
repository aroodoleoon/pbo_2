package com.pos.server.repository

import com.pos.server.domain.model.KategoriBarang
import javax.sql.DataSource

class KategoriRepository(private val dataSource: DataSource) {

    fun findAll(): List<KategoriBarang> {
        val sql = "SELECT id, nama FROM kategori_barang ORDER BY nama ASC"
        dataSource.connection.use { c ->
            c.prepareStatement(sql).use { s ->
                s.executeQuery().use { rs ->
                    val list = mutableListOf<KategoriBarang>()
                    while (rs.next()) {
                        list.add(KategoriBarang(id = rs.getString("id"), nama = rs.getString("nama")))
                    }
                    return list
                }
            }
        }
    }

    fun findById(id: String): KategoriBarang? {
        val sql = "SELECT id, nama FROM kategori_barang WHERE id = ?"
        dataSource.connection.use { c ->
            c.prepareStatement(sql).use { s ->
                s.setString(1, id)
                s.executeQuery().use { rs ->
                    return if (rs.next()) KategoriBarang(id = rs.getString("id"), nama = rs.getString("nama")) else null
                }
            }
        }
    }

    fun insert(kategori: KategoriBarang) {
        val sql = "INSERT INTO kategori_barang (id, nama) VALUES (?, ?)"
        dataSource.connection.use { c ->
            c.prepareStatement(sql).use { s ->
                s.setString(1, kategori.id)
                s.setString(2, kategori.nama)
                s.executeUpdate()
            }
        }
    }
}

