# Changelog

## [v0.1.53] - 02 de noviembre de 2024

### Cambios implementados

- En el método `auth()` de la clase `DLAuth` se establece el valor por defecto a `null` en el tercer parámetro para conservar la compatibilidad sin perder las nuevas características agregadas en la versión `v0.1.52`.
- Se permite que el autenticador establezca los el nombre y contenido de la cookie de autenticación.
- Se agregan las directivas`@break`, `@continue` y `@varname(var, value)`

### Cómo Actualizar

Para integrar esta nueva funcionalidad en tu aplicación, actualiza a la versión `v0.1.53` de la biblioteca. Sigue las mejores prácticas de actualización de tu gestor de dependencias.

### Notas Adicionales

- Si estás utilizando versiones anteriores a la `v0.1.53`, se recomienda actualizar para aprovechar las últimas correcciones y mejoras.
