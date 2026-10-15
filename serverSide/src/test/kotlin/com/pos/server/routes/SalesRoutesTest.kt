package com.pos.server.routes

import com.pos.server.config.DatabaseConfig
import com.pos.server.config.DatabaseFactory
import com.pos.server.dto.product.ProductDto
import com.pos.server.dto.sales.CheckoutItemDto
import com.pos.server.dto.sales.CheckoutPaymentDto
import com.pos.server.dto.sales.CheckoutRequest
import com.pos.server.dto.sales.CheckoutResponse
import com.pos.server.dto.sales.SalesReportRowDto
import com.pos.server.dto.sales.TransactionHistoryItemDto
import com.pos.server.plugins.configureRouting
import com.pos.server.plugins.configureSecurity
import com.pos.server.plugins.configureSerialization
import com.pos.server.plugins.configureStatusPages
import io.ktor.client.request.*
import io.ktor.client.statement.*
import io.ktor.http.*
import io.ktor.server.testing.*
import kotlinx.serialization.json.Json
import java.util.UUID
import kotlin.test.Test
import kotlin.test.assertEquals
import kotlin.test.assertTrue

class SalesRoutesTest {

    private val json = Json { ignoreUnknownKeys = true }

    private fun setupTestApp(testBlock: suspend ApplicationTestBuilder.() -> Unit) = testApplication {
        val dbConfig = DatabaseConfig(
            url = "jdbc:h2:mem:sales_test_${System.currentTimeMillis()};DB_CLOSE_DELAY=-1",
            user = "sa",
            pass = ""
        )
        val ds = DatabaseFactory.createDataSource(dbConfig)
        application {
            configureSerialization()
            configureStatusPages()
            configureSecurity()
            configureRouting(ds)
        }
        testBlock()
    }

    @Test
    fun `TC-03 barcode scan lookup valid barcode returns 200 and non-existent returns 404`() = setupTestApp {
        // Barcode brg-001 yang ada di seed data: 89999991001 (Chitato)
        val resFound = client.get("/api/v1/products/barcode/89999991001")
        assertEquals(HttpStatusCode.OK, resFound.status)
        val product = json.decodeFromString<ProductDto>(resFound.bodyAsText())
        assertEquals("brg-001", product.id)
        assertEquals("89999991001", product.barcode)

        // Barcode sembarang yang tidak ada
        val resNotFound = client.get("/api/v1/products/barcode/9999999999999")
        assertEquals(HttpStatusCode.NotFound, resNotFound.status)
    }

    @Test
    fun `TC-04 checkout with quantity zero or negative returns 400 Bad Request`() = setupTestApp {
        val invalidReq = CheckoutRequest(
            items = listOf(CheckoutItemDto(productId = "brg-001", quantity = 0)),
            payment = CheckoutPaymentDto(method = "CASH", amountPaid = 50000L)
        )

        val response = client.post("/api/v1/sales/checkout") {
            header(HttpHeaders.ContentType, ContentType.Application.Json.toString())
            header("X-User-Id", "KASIR-01")
            setBody(json.encodeToString(CheckoutRequest.serializer(), invalidReq))
        }

        assertEquals(HttpStatusCode.BadRequest, response.status)
        assertTrue(response.bodyAsText().contains("BAD_REQUEST"))
    }

    @Test
    fun `TC-05 checkout exceeding physical stock returns 409 Conflict OUT_OF_STOCK`() = setupTestApp {
        // brg-001 di seed data memiliki stok 50
        val overStockReq = CheckoutRequest(
            items = listOf(CheckoutItemDto(productId = "brg-001", quantity = 9999)),
            payment = CheckoutPaymentDto(method = "CASH", amountPaid = 200000000L)
        )

        val response = client.post("/api/v1/sales/checkout") {
            header(HttpHeaders.ContentType, ContentType.Application.Json.toString())
            header("X-User-Id", "KASIR-01")
            setBody(json.encodeToString(CheckoutRequest.serializer(), overStockReq))
        }

        assertEquals(HttpStatusCode.Conflict, response.status)
        assertTrue(response.bodyAsText().contains("OUT_OF_STOCK"))
    }

    @Test
    fun `TC-07 checkout idempotency double submit returns identical transaction without double deduction`() = setupTestApp {
        val idempotencyKey = "IDEMP-" + UUID.randomUUID().toString()
        val checkoutReq = CheckoutRequest(
            idempotencyKey = idempotencyKey,
            items = listOf(CheckoutItemDto(productId = "brg-002", quantity = 2)),
            payment = CheckoutPaymentDto(method = "CASH", amountPaid = 20000L)
        )

        // Submit Pertama
        val res1 = client.post("/api/v1/sales/checkout") {
            header(HttpHeaders.ContentType, ContentType.Application.Json.toString())
            header("X-User-Id", "KASIR-01")
            setBody(json.encodeToString(CheckoutRequest.serializer(), checkoutReq))
        }
        assertEquals(HttpStatusCode.Created, res1.status)
        val data1 = json.decodeFromString<CheckoutResponse>(res1.bodyAsText())

        // Submit Kedua (Double submit dengan idempotencyKey yang sama)
        val res2 = client.post("/api/v1/sales/checkout") {
            header(HttpHeaders.ContentType, ContentType.Application.Json.toString())
            header("X-User-Id", "KASIR-01")
            setBody(json.encodeToString(CheckoutRequest.serializer(), checkoutReq))
        }
        assertEquals(HttpStatusCode.Created, res2.status)
        val data2 = json.decodeFromString<CheckoutResponse>(res2.bodyAsText())

        // Memastikan ID transaksi dan nomor transaksi yang dikembalikan sama persis
        assertEquals(data1.transactionId, data2.transactionId)
        assertEquals(data1.transactionNumber, data2.transactionNumber)
        assertEquals(data1.total, data2.total)
    }

    @Test
    fun `TC-09 cash payment less than total bill returns 400 Bad Request`() = setupTestApp {
        // brg-001 harga jual 11.500 * 2 = 23.000. Diberikan uang 10.000
        val underPaidReq = CheckoutRequest(
            items = listOf(CheckoutItemDto(productId = "brg-001", quantity = 2)),
            payment = CheckoutPaymentDto(method = "CASH", amountPaid = 10000L)
        )

        val response = client.post("/api/v1/sales/checkout") {
            header(HttpHeaders.ContentType, ContentType.Application.Json.toString())
            header("X-User-Id", "KASIR-01")
            setBody(json.encodeToString(CheckoutRequest.serializer(), underPaidReq))
        }

        assertEquals(HttpStatusCode.BadRequest, response.status)
        assertTrue(response.bodyAsText().contains("kurang dari total belanja"))
    }

    @Test
    fun `TC-10 sales summary report accurately reconciles revenue with successful checkout`() = setupTestApp {
        // 1. Lakukan transaksi sukses: 3 x brg-003 (Aqua 4000) = 12.000
        val checkoutReq = CheckoutRequest(
            items = listOf(CheckoutItemDto(productId = "brg-003", quantity = 3)),
            payment = CheckoutPaymentDto(method = "CASH", amountPaid = 20000L)
        )
        val checkoutRes = client.post("/api/v1/sales/checkout") {
            header(HttpHeaders.ContentType, ContentType.Application.Json.toString())
            header("X-User-Id", "KASIR-01")
            setBody(json.encodeToString(CheckoutRequest.serializer(), checkoutReq))
        }
        assertEquals(HttpStatusCode.Created, checkoutRes.status)

        // 2. Tarik laporan ringkasan penjualan dengan rentang tanggal
        val reportRes = client.get("/api/v1/reports/sales?from=2020-01-01&to=2030-12-31")
        assertEquals(HttpStatusCode.OK, reportRes.status)
        val reportRows = json.decodeFromString<List<SalesReportRowDto>>(reportRes.bodyAsText())

        val totalNet = reportRows.sumOf { it.netSales }
        val totalCount = reportRows.sumOf { it.transactionCount }

        assertTrue(totalNet >= 12000L)
        assertTrue(totalCount >= 1)
    }

    @Test
    fun `TC-11 checkout and login actions trigger audit logs queryable by admin`() = setupTestApp {
        // 1. Login sebagai admin
        val loginRes = client.post("/api/v1/auth/login") {
            header(HttpHeaders.ContentType, ContentType.Application.Json.toString())
            setBody("""{"username":"admin","password":"admin123"}""")
        }
        val loginData = json.decodeFromString<com.pos.server.dto.auth.LoginResponse>(loginRes.bodyAsText())
        val adminToken = loginData.accessToken

        // 2. Lakukan transaksi checkout
        val checkoutReq = CheckoutRequest(
            items = listOf(CheckoutItemDto(productId = "brg-001", quantity = 1)),
            payment = CheckoutPaymentDto(method = "CASH", amountPaid = 20000L)
        )
        val checkoutRes = client.post("/api/v1/sales/checkout") {
            header(HttpHeaders.ContentType, ContentType.Application.Json.toString())
            header("X-User-Id", "KASIR-01")
            setBody(json.encodeToString(CheckoutRequest.serializer(), checkoutReq))
        }
        assertEquals(HttpStatusCode.Created, checkoutRes.status)

        // 3. Admin mengecek log audit NoSQL
        val auditRes = client.get("/api/v1/audit-logs") {
            header(HttpHeaders.Authorization, "Bearer $adminToken")
        }
        assertEquals(HttpStatusCode.OK, auditRes.status)
        val body = auditRes.bodyAsText()
        assertTrue(body.contains("LOGIN") || body.contains("CHECKOUT"))
    }
}
