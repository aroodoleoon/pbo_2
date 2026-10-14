package com.pos.server

import com.pos.server.config.AppConfig
import com.pos.server.config.DatabaseFactory
import com.pos.server.plugins.configureCallLogging
import com.pos.server.plugins.configureRouting
import com.pos.server.plugins.configureSecurity
import com.pos.server.plugins.configureSerialization
import com.pos.server.plugins.configureStatusPages
import io.ktor.server.application.Application
import io.ktor.server.engine.embeddedServer
import io.ktor.server.netty.Netty

fun main() {
    embeddedServer(Netty, port = 8080, host = "0.0.0.0", module = Application::module)
        .start(wait = true)
}

fun Application.module() {
    val config = AppConfig.load()
    val dataSource = DatabaseFactory.createDataSource(config.dbConfig)

    configureSerialization()
    configureStatusPages()
    configureCallLogging()
    configureSecurity()
    configureRouting(dataSource)
}

