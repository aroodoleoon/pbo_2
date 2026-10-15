package com.pos.server.routes

import com.pos.server.config.DatabaseConfig
import com.pos.server.config.DatabaseFactory
import com.pos.server.dto.auth.LoginResponse
import com.pos.server.plugins.configureRouting
import com.pos.server.plugins.configureSecurity
import com.pos.server.plugins.configureSerialization
import com.pos.server.plugins.configureStatusPages
import io.ktor.client.request.*
import io.ktor.client.statement.*
import io.ktor.http.*
import io.ktor.server.testing.*
import kotlinx.serialization.json.Json
import kotlin.test.Test
import kotlin.test.assertEquals
import kotlin.test.assertTrue

class AuthRoutesTest {

    private val json = Json { ignoreUnknownKeys = true }

    private fun setupTestApp(testBlock: suspend ApplicationTestBuilder.() -> Unit) = testApplication {
        val dbConfig = DatabaseConfig(
            url = "jdbc:h2:mem:auth_test_${System.currentTimeMillis()};DB_CLOSE_DELAY=-1",
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
    fun `TC-01 login with incorrect password returns 401 Unauthorized`() = setupTestApp {
        val response = client.post("/api/v1/auth/login") {
            header(HttpHeaders.ContentType, ContentType.Application.Json.toString())
            setBody("""{"username":"admin","password":"wrong_password"}""")
        }

        assertEquals(HttpStatusCode.Unauthorized, response.status)
        assertTrue(response.bodyAsText().contains("UNAUTHORIZED"))
    }

    @Test
    fun `login with valid credentials returns 200 and access token`() = setupTestApp {
        val response = client.post("/api/v1/auth/login") {
            header(HttpHeaders.ContentType, ContentType.Application.Json.toString())
            setBody("""{"username":"admin","password":"admin123"}""")
        }

        assertEquals(HttpStatusCode.OK, response.status)
        val body = response.bodyAsText()
        val loginData = json.decodeFromString<LoginResponse>(body)
        assertTrue(loginData.accessToken.isNotBlank())
        assertEquals("ADMIN", loginData.user.role)
    }

    @Test
    fun `TC-02 cashier accessing employee management returns 403 Forbidden`() = setupTestApp {
        // 1. Login sebagai kasir (kasir1 dengan password kasir123)
        val loginRes = client.post("/api/v1/auth/login") {
            header(HttpHeaders.ContentType, ContentType.Application.Json.toString())
            setBody("""{"username":"kasir1","password":"kasir123"}""")
        }
        assertEquals(HttpStatusCode.OK, loginRes.status)
        val loginData = json.decodeFromString<LoginResponse>(loginRes.bodyAsText())
        val cashierToken = loginData.accessToken

        // 2. Akses endpoint ADMIN /api/v1/employees dengan token CASHIER
        val employeeRes = client.get("/api/v1/employees") {
            header(HttpHeaders.Authorization, "Bearer $cashierToken")
        }

        assertEquals(HttpStatusCode.Forbidden, employeeRes.status)
        assertTrue(employeeRes.bodyAsText().contains("FORBIDDEN"))
    }

    @Test
    fun `admin accessing employee management returns 200 OK`() = setupTestApp {
        // 1. Login sebagai admin
        val loginRes = client.post("/api/v1/auth/login") {
            header(HttpHeaders.ContentType, ContentType.Application.Json.toString())
            setBody("""{"username":"admin","password":"admin123"}""")
        }
        assertEquals(HttpStatusCode.OK, loginRes.status)
        val loginData = json.decodeFromString<LoginResponse>(loginRes.bodyAsText())
        val adminToken = loginData.accessToken

        // 2. Akses endpoint ADMIN /api/v1/employees dengan token ADMIN
        val employeeRes = client.get("/api/v1/employees") {
            header(HttpHeaders.Authorization, "Bearer $adminToken")
        }

        assertEquals(HttpStatusCode.OK, employeeRes.status)
        assertTrue(employeeRes.bodyAsText().contains("admin"))
    }

    @Test
    fun `logout revokes token preventing further access to protected endpoints`() = setupTestApp {
        // 1. Login sebagai admin
        val loginRes = client.post("/api/v1/auth/login") {
            header(HttpHeaders.ContentType, ContentType.Application.Json.toString())
            setBody("""{"username":"admin","password":"admin123"}""")
        }
        val adminToken = json.decodeFromString<LoginResponse>(loginRes.bodyAsText()).accessToken

        // 2. Akses berhasil sebelum logout
        val beforeLogout = client.get("/api/v1/employees") {
            header(HttpHeaders.Authorization, "Bearer $adminToken")
        }
        assertEquals(HttpStatusCode.OK, beforeLogout.status)

        // 3. Logout dan cabut token
        val logoutRes = client.post("/api/v1/auth/logout") {
            header(HttpHeaders.Authorization, "Bearer $adminToken")
        }
        assertEquals(HttpStatusCode.OK, logoutRes.status)

        // 4. Akses ditolak setelah logout (401 Unauthorized)
        val afterLogout = client.get("/api/v1/employees") {
            header(HttpHeaders.Authorization, "Bearer $adminToken")
        }
        assertEquals(HttpStatusCode.Unauthorized, afterLogout.status)
        assertTrue(afterLogout.bodyAsText().contains("UNAUTHORIZED"))
    }

    @Test
    fun `forged token with known employee ID without active session returns 401 Unauthorized`() = setupTestApp {
        // Coba bypass autentikasi dengan token palsu menggunakan ID admin yang diketahui
        val forgedToken = "POS-TOKEN-11111111-1111-1111-1111-111111111111-fake-token"
        val response = client.get("/api/v1/employees") {
            header(HttpHeaders.Authorization, "Bearer $forgedToken")
        }

        assertEquals(HttpStatusCode.Unauthorized, response.status)
        assertTrue(response.bodyAsText().contains("UNAUTHORIZED"))
    }
}
