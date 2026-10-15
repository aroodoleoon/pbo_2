package com.pos.server.service

import com.pos.server.domain.enums.Role
import com.pos.server.domain.exception.AksesDitolakException
import com.pos.server.domain.exception.AutentikasiException
import com.pos.server.domain.model.Admin
import com.pos.server.domain.model.Karyawan
import com.pos.server.domain.model.Kasir
import com.pos.server.dto.auth.CreateEmployeeRequest
import com.pos.server.dto.auth.LoginRequest
import com.pos.server.dto.auth.LoginResponse
import com.pos.server.dto.auth.UserResponse
import com.pos.server.repository.KaryawanRepository
import java.util.UUID

/**
 * Layanan Autentikasi dan Kontrol Akses (Modul 4 Dhia).
 */
class AuthService(
    private val karyawanRepository: KaryawanRepository
) {
    fun login(request: LoginRequest): LoginResponse {
        val karyawan = karyawanRepository.findByUsername(request.username)
            ?: throw AutentikasiException("Username atau kata sandi salah.")

        if (!karyawan.aktif) {
            throw AksesDitolakException("Akun karyawan dinonaktifkan. Silakan hubungi Administrator.")
        }

        if (!karyawan.verifikasiPassword(request.password)) {
            throw AutentikasiException("Username atau kata sandi salah.")
        }

        val user = UserResponse(
            id = karyawan.id,
            name = karyawan.nama,
            username = karyawan.username,
            role = karyawan.role.name,
            active = karyawan.aktif
        )
        val token = "POS-SEC-" + UUID.randomUUID().toString().replace("-", "") + UUID.randomUUID().toString().replace("-", "")
        com.pos.server.plugins.TokenManager.registerSession(token, user)

        return LoginResponse(
            accessToken = token,
            user = user
        )
    }

    fun getAllEmployees(): List<UserResponse> {
        return karyawanRepository.findAll().map {
            UserResponse(
                id = it.id,
                name = it.nama,
                username = it.username,
                role = it.role.name,
                active = it.aktif
            )
        }
    }

    fun createEmployee(request: CreateEmployeeRequest): UserResponse {
        val existing = karyawanRepository.findByUsername(request.username.trim())
        if (existing != null) {
            throw IllegalArgumentException("Username '${request.username}' sudah terdaftar.")
        }

        val role = try { Role.fromString(request.role) } catch (_: Exception) { Role.CASHIER }
        val passwordHash = Karyawan.hitungHash(request.password)

        val karyawan = when (role) {
            Role.ADMIN -> Admin(
                nama = request.name.trim(),
                username = request.username.trim().lowercase(),
                passwordHash = passwordHash,
                aktif = true
            )
            Role.CASHIER -> Kasir(
                nama = request.name.trim(),
                username = request.username.trim().lowercase(),
                passwordHash = passwordHash,
                aktif = true
            )
        }

        karyawanRepository.insert(karyawan)
        return UserResponse(
            id = karyawan.id,
            name = karyawan.nama,
            username = karyawan.username,
            role = karyawan.role.name,
            active = karyawan.aktif
        )
    }

    fun toggleStatus(id: String): UserResponse {
        val karyawan = getKaryawanById(id)
        val newStatus = !karyawan.aktif
        karyawanRepository.updateStatus(id, newStatus)
        return UserResponse(
            id = karyawan.id,
            name = karyawan.nama,
            username = karyawan.username,
            role = karyawan.role.name,
            active = newStatus
        )
    }

    fun deleteEmployee(id: String) {
        karyawanRepository.delete(id)
    }

    fun getKaryawanById(id: String): Karyawan {
        return karyawanRepository.findById(id)
            ?: throw AutentikasiException("Karyawan tidak ditemukan.")
    }

    fun validasiHakAkses(karyawanId: String, fitur: String): Boolean {
        val karyawan = getKaryawanById(karyawanId)
        if (!karyawan.aktif) return false
        return karyawan.memilikiAkses(fitur)
    }
}

