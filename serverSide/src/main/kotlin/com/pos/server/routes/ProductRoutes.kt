package com.pos.server.routes

import com.pos.server.dto.common.ApiErrorResponse
import com.pos.server.dto.product.CategoryDto
import com.pos.server.dto.product.CreateCategoryRequest
import com.pos.server.dto.product.CreateProductRequest
import com.pos.server.dto.product.ProductDto
import com.pos.server.dto.product.UpdateProductRequest
import com.pos.server.service.ProductService
import io.ktor.http.HttpStatusCode
import io.ktor.server.application.call
import io.ktor.server.request.receive
import io.ktor.server.response.respond
import io.ktor.server.routing.Route
import io.ktor.server.routing.get
import io.ktor.server.routing.patch
import io.ktor.server.routing.post
import io.ktor.server.routing.route

fun Route.productRoutes(productService: ProductService) {
    route("/categories") {
        get {
            val categories = productService.getAllCategories().map { CategoryDto(it.id, it.nama) }
            call.respond(HttpStatusCode.OK, categories)
        }

        post {
            try {
                val req = call.receive<CreateCategoryRequest>()
                val cat = productService.createCategory(req)
                call.respond(HttpStatusCode.Created, CategoryDto(cat.id, cat.nama))
            } catch (e: Exception) {
                call.respond(HttpStatusCode.BadRequest, ApiErrorResponse("BAD_REQUEST", e.message ?: "Gagal membuat kategori."))
            }
        }
    }

    route("/products") {
        get {
            val query = call.request.queryParameters["query"]
            val categoryId = call.request.queryParameters["categoryId"]
            val products = productService.getAllProducts(query, categoryId).map {
                ProductDto(
                    id = it.id,
                    barcode = it.kode,
                    name = it.nama,
                    categoryId = it.kategoriId,
                    buyPrice = it.hargaBeli,
                    sellPrice = it.hargaJual,
                    stock = it.stok,
                    minStock = it.stokMinimum,
                    active = it.aktif
                )
            }
            call.respond(HttpStatusCode.OK, products)
        }

        post {
            try {
                val req = call.receive<CreateProductRequest>()
                val p = productService.createProduct(req)
                call.respond(
                    HttpStatusCode.Created,
                    ProductDto(
                        id = p.id,
                        barcode = p.kode,
                        name = p.nama,
                        categoryId = p.kategoriId,
                        buyPrice = p.hargaBeli,
                        sellPrice = p.hargaJual,
                        stock = p.stok,
                        minStock = p.stokMinimum,
                        active = p.aktif
                    )
                )
            } catch (e: Exception) {
                call.respond(HttpStatusCode.BadRequest, ApiErrorResponse("BAD_REQUEST", e.message ?: "Gagal membuat produk."))
            }
        }

        get("/barcode/{barcode}") {
            val barcode = call.parameters["barcode"]?.trim()
            if (barcode.isNullOrBlank()) {
                return@get call.respond(HttpStatusCode.BadRequest, ApiErrorResponse("BAD_REQUEST", "Barcode tidak boleh kosong."))
            }
            val p = productService.getProductByBarcode(barcode)
            if (p == null) {
                return@get call.respond(
                    HttpStatusCode.NotFound,
                    ApiErrorResponse("NOT_FOUND", "Barang dengan barcode '$barcode' tidak ditemukan.")
                )
            }
            call.respond(
                HttpStatusCode.OK,
                ProductDto(
                    id = p.id,
                    barcode = p.kode,
                    name = p.nama,
                    categoryId = p.kategoriId,
                    buyPrice = p.hargaBeli,
                    sellPrice = p.hargaJual,
                    stock = p.stok,
                    minStock = p.stokMinimum,
                    active = p.aktif
                )
            )
        }

        get("/{id}") {
            try {
                val id = call.parameters["id"] ?: throw IllegalArgumentException("ID barang diperlukan.")
                val p = productService.getProductById(id)
                call.respond(
                    HttpStatusCode.OK,
                    ProductDto(
                        id = p.id,
                        barcode = p.kode,
                        name = p.nama,
                        categoryId = p.kategoriId,
                        buyPrice = p.hargaBeli,
                        sellPrice = p.hargaJual,
                        stock = p.stok,
                        minStock = p.stokMinimum,
                        active = p.aktif
                    )
                )
            } catch (e: Exception) {
                call.respond(HttpStatusCode.NotFound, ApiErrorResponse("NOT_FOUND", e.message ?: "Barang tidak ditemukan."))
            }
        }

        patch("/{id}") {
            try {
                val id = call.parameters["id"] ?: throw IllegalArgumentException("ID barang diperlukan.")
                val req = call.receive<UpdateProductRequest>()
                val p = productService.updateProduct(id, req)
                call.respond(
                    HttpStatusCode.OK,
                    ProductDto(
                        id = p.id,
                        barcode = p.kode,
                        name = p.nama,
                        categoryId = p.kategoriId,
                        buyPrice = p.hargaBeli,
                        sellPrice = p.hargaJual,
                        stock = p.stok,
                        minStock = p.stokMinimum,
                        active = p.aktif
                    )
                )
            } catch (e: Exception) {
                call.respond(HttpStatusCode.BadRequest, ApiErrorResponse("BAD_REQUEST", e.message ?: "Gagal memperbarui barang."))
            }
        }
    }
}

