package com.pos.server.service

import com.pos.server.domain.exception.ValidasiException
import com.pos.server.domain.model.Barang
import com.pos.server.domain.model.KategoriBarang
import com.pos.server.dto.product.CreateCategoryRequest
import com.pos.server.dto.product.CreateProductRequest
import com.pos.server.dto.product.UpdateProductRequest
import com.pos.server.repository.BarangRepository
import com.pos.server.repository.KategoriRepository

class ProductService(
    private val barangRepository: BarangRepository,
    private val kategoriRepository: KategoriRepository
) {
    fun getAllCategories(): List<KategoriBarang> = kategoriRepository.findAll()

    fun createCategory(request: CreateCategoryRequest): KategoriBarang {
        val cat = KategoriBarang(nama = request.name)
        kategoriRepository.insert(cat)
        return cat
    }

    fun getAllProducts(keyword: String? = null, categoryId: String? = null, onlyActive: Boolean = true): List<Barang> {
        return barangRepository.findAll(keyword, categoryId, onlyActive)
    }

    fun getProductById(id: String): Barang {
        return barangRepository.findByIdOrThrow(id)
    }

    fun getProductByBarcode(barcode: String): Barang? {
        return barangRepository.findByKode(barcode)
    }

    fun createProduct(request: CreateProductRequest, employeeId: String = "SYSTEM"): Barang {
        if (barangRepository.findByKode(request.barcode) != null) {
            throw ValidasiException("Kode barcode '${request.barcode}' sudah terdaftar pada barang lain.")
        }
        if (request.categoryId != null && kategoriRepository.findById(request.categoryId) == null) {
            throw ValidasiException("Kategori dengan ID '${request.categoryId}' tidak ditemukan.")
        }

        val barang = Barang(
            kode = request.barcode,
            nama = request.name,
            kategoriId = request.categoryId,
            hargaBeli = request.buyPrice,
            hargaJual = request.sellPrice,
            stokAwal = request.initialStock,
            stokMinimum = request.minStock
        )
        barangRepository.insert(barang)
        return barang
    }

    fun updateProduct(id: String, request: UpdateProductRequest): Barang {
        val barang = barangRepository.findByIdOrThrow(id)

        if (request.name != null) barang.nama = request.name
        if (request.categoryId != null) {
            if (kategoriRepository.findById(request.categoryId) == null) {
                throw ValidasiException("Kategori '${request.categoryId}' tidak ditemukan.")
            }
            barang.kategoriId = request.categoryId
        }
        if (request.buyPrice != null || request.sellPrice != null) {
            val buy = request.buyPrice ?: barang.hargaBeli
            val sell = request.sellPrice ?: barang.hargaJual
            barang.ubahHarga(buy, sell)
        }
        if (request.minStock != null) barang.stokMinimum = request.minStock
        if (request.active != null) barang.aktif = request.active

        barangRepository.update(barang)
        return barang
    }
}

