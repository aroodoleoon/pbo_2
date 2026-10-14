package com.pos.server.service

import com.pos.server.domain.exception.AksesDitolakException
import com.pos.server.domain.exception.AutentikasiException
import com.pos.server.domain.model.Karyawan
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

        val token = "POS-TOKEN-${karyawan.id}-${UUID.randomUUID()}"
        return LoginResponse(
            accessToken = token,
            user = UserResponse(
                id = karyawan.id,
                name = karyawan.nama,
                username = karyawan.username,
                role = karyawan.role.name,
                active = karyawan.aktif
            )
        )
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

