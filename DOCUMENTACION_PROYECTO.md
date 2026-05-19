# Documentación funcional y técnica del proyecto Cisternas

Este documento explica las partes propias del proyecto, dejando fuera las piezas genéricas que vienen de Laravel. La idea es que una persona con conocimientos básicos de programación pueda entender que hace cada módulo importante, como se conectan entre sí y donde tocar si hay que modificar una funcionalidad.

## 1. Resumen general

La aplicación gestiona cisternas de transporte. Permite:

- Consultar un listado de cisternas con filtros por texto, fecha y año.
- Crear, editar, ver y eliminar cisternas.
- Importar cisternas desde archivos Excel.
- Revisar los datos importados antes de guardarlos.
- Exportar cisternas a Excel.
- Registrar horas reales de consumo.
- Gestionar usuarios y roles.
- Mantener una pacification temporal exportable a Excel.
- Ver indicadores de estado en un dashboard.

El dato principal del sistema es una cisterna. Una cisterna tiene datos de identificación, conductor, origen, destino, matrículas, fechas, horas de consumo, certificaciones y observaciones.

## 2. Roles y permisos

Los roles se definen principalmente en `app/Models/User.php` y se comprueban desde controladores y middleware.

Roles usados:

- `Root`: acceso total.
- `Administrador` o `admin`: gestiona usuarios, cisternas, importaciones, exportaciones y planificación.
- `Usuario` o `user`: puede trabajar con cisternas, pero no administra usuarios.
- `operario` u `Operario`: orientado a registrar datos operativos, especialmente horas reales de consumo.

Métodos importantes del modelo `User`:

- `isRoot()`: devuelve `true` si el usuario tiene rol `Root`.
- `isAdmin()`: devuelve `true` si es administrador o root.
- `isUser()`: devuelve `true` si es usuario normal.
- `isOperario()`: devuelve `true` si es operario.
- `canView()`: indica si puede visualizar datos. En la practica, cualquier usuario autenticado.
- `canCreate()`: indica si puede crear registros.
- `canEdit()`: indica si puede editar registros completos.
- `canDelete()`: indica si puede eliminar registros.
- `canImport()`: indica si puede importar Excel.
- `canExport()`: indica si puede exportar datos.

Middleware propio:

- `app/Http/Middleware/CheckRole.php`

Este middleware recibe una lista de roles permitidos en una ruta. Si el usuario no está autenticado, lo manda al login. Si está autenticado, pero su rol no está permitido, devuelve error 403.

## 3. Modelo de datos principal: Cisterna

Archivo:

- `app/Models/Cisterna.php`

Representa una fila de la tabla `cisternas`.

Campos principales:

- `OF`: orden de fabricación.
- `NumeroCisterna`: número identificador de la cisterna.
- `Conductor`: conductor asignado.
- `Telefono`: teléfono del conductor.
- `Origen`: origen del transporte.
- `Destino`: destino del transporte.
- `Matricula`: matricula del camion.
- `MatriculaCisterna`: matricula de la cisterna.
- `Transporte`: empresa o medio de transporte.
- `FechaFabricacionHuelva`: fecha de fabricación.
- `HoraSalida`: salida desde origen.
- `FechaEntradaMG`: entrada en Madrid/Guadalajara.
- `HoraLlegadaEstimada`: hora estimada de llegada.
- `FechaConsumoMG`: fecha prevista de consumo.
- `HoraEstimadaConsumoL1`: hora estimada de consumo en linea 1.
- `HoraEstimadaConsumoL2`: hora estimada de consumo en linea 2.
- `HoraRealConsumoL1`: hora real de consumo en linea 1.
- `HoraRealConsumoL2`: hora real de consumo en linea 2.
- `GlobalGAP`: certificación GlobalGAP.
- `FDA`: certificación FDA.
- `Observaciones`: notas generales.
- `Incidencias`: problemas detectados.

Configuración importante del modelo:

- `$primaryKey = 'IdCisterna'`: la clave primaria no se llama `id`, sino `IdCisterna`.
- `$dateFormat = 'Ymd H:i:s'`: formato usado al guardar fechas, pensado para SQL Server.
- `$fillable`: lista de campos que se pueden crear/actualizar masivamente.
- `casts()`: convierte fechas a objetos fecha y certificaciones a booleanos.

Métodos útiles:

- `getNumeroFormateadoAttribute()`: devuelve el número de cisternas con ceros a la izquierda. Ejemplo: `42` pasa a `0042`.
- `getEstadoAttribute()`: calcula el estado textual de la cisterna:
  - `incidencia` si tiene incidencias.
  - `consumida` si tiene hora real de consumo.
  - `pendiente` si no tiene fecha de consumo.
  - `hoy` si se consume hoy.
  - `futura` si se consume en una fecha posterior.
  - `pasada` para casos ya vencidos sin consumo real.
- `scopeConsumidas()`: filtro reutilizable para obtener cisternas con consumo real.
- `scopePendientes()`: filtro reutilizable para obtener cisternas sin consumo real ni incidencias.
- `scopeHoy()`: filtro reutilizable para cisternas programadas para hoy.

## 4. Tabla `cisternas`

Archivo:

- `database/migrations/2026_03_17_112840_create_cisternas_table.php`

Crea la tabla donde se guardan las cisternas. La estructura coincide con el modelo `Cisterna`.

Puntos importantes:

- `IdCisterna` es autoincrement y clave primaria.
- `OF`, `NumeroCisterna` y `Conductor` son obligatorios.
- Muchos campos logísticos son opcionales porque pueden venir vacíos desde Excel.
- Las fechas y horas se guardan como `dateTime`.
- `GlobalGAP` y `FDA` son booleanos.
- `Observaciones` e `Incidencias` son textos largos.

## 5. Rutas propias del proyecto

Archivo:

- `routes/web.php`

La raíz `/` redirige al listado de cisternas.

Rutas de cisternas:

- `GET /cisterna`: listado.
- `GET /cisterna/create`: formulario de creación.
- `POST /cisterna`: guardar nueva cisterna.
- `GET /cisterna/{cisterna}`: ver detalle.
- `GET /cisterna/{cisterna}/edit`: formulario de edición.
- `PATCH /cisterna/{cisterna}`: actualizar cisterna.
- `DELETE /cisterna/{cisterna}`: eliminar cisterna.
- `PATCH /cisterna/{cisterna}/consumo`: actualizar horas reales de consumo desde el modal.

Rutas de importación:

- `GET /cisterna/bulk-upload`: pantalla para subir Excel.
- `POST /cisterna/bulk-upload`: procesa el archivo y genera previsualización.
- `GET /cisterna/bulk-confirm`: pantalla de confirmación.
- `POST /cisterna/bulk-confirm`: importa lo confirmado.

Rutas de exportación:

- `GET /cisterna/export`: descarga un Excel de cisternas.

Rutas especiales:

- `DELETE /cisterna/destroy-all`: elimina todas las cisternas.
- `GET /dashboard`: panel de indicadores.

Rutas de administración:

- `GET /admin/users`: listado de usuarios.
- `GET /admin/users/create`: crear usuario.
- `POST /admin/users`: guardar usuario.
- `GET /admin/users/{user}`: ver detalle de usuario.
- `GET /admin/users/{user}/edit`: editar usuario.
- `PATCH /admin/users/{user}`: actualizar usuario.
- `DELETE /admin/users/{user}`: eliminar usuario.
- `PATCH /admin/users/{user}/toggle`: activar/desactivar usuario.
- `PATCH /admin/users/{user}/role`: cambiar rol.

Rutas de planificación:

- `GET /planificacion`: listado de planificación.
- `POST /planificacion`: crear fila de planificación.
- `GET /planificacion/{id}/edit`: editar fila.
- `PATCH /planificacion/{id}`: actualizar fila.
- `DELETE /planificacion/{id}`: borrar fila.
- `DELETE /planificacion`: limpiar toda la planificación.
- `GET /planificacion/exportar`: exportar planificación a Excel.

## 6. Controlador de cisternas

Archivo:

- `app/Http/Controllers/CisternaController.php`

Es el controlador principal de la aplicación.

### `index(Request $request)`

Muestra el listado de cisternas.

Hace estas operaciones:

- Crea una consulta inicial sobre `Cisterna`.
- Aplica filtro por año si llega `year`.
- Si no llega año, filtra automáticamente el año actual y diciembre del año anterior.
- Aplica filtro por texto si llega `texto`.
- Aplica filtro por fecha si llega `fecha`.
- Válida la columna de ordenación para evitar columnas no permitidas.
- Pagina manualmente a 30 registros.
- Devuelve la vista `cisterna.index`.

### `create()`

Muestra el formulario de creación.

Antes comprueba que el usuario sea root, administrador o usuario normal. Si no lo es, devuelve 403.

### `store(Request $request)`

Guarda una cisterna creada manualmente.

Hace estas operaciones:

- Comprueba permisos.
- Válida los campos obligatorios y formatos.
- Copia `FechaConsumoMG` a `FechaEntradaMG` mediante `syncFechasConsumoEntrada()`.
- Llama a `autoConsumir()`, que actualmente no modifica datos.
- Crea la cisterna en base de datos.
- Redirige al listado.

### `show(Cisterna $cisterna)`

Muestra el detalle de una cisterna.

Laravel recibe el identificador en la ruta y carga automáticamente el modelo correspondiente.

### `edit(Cisterna $cisterna)`

Muestra el formulario de edición de una cisterna.

### `update(Request $request, Cisterna $cisterna)`

Actualiza una cisterna.

Tiene comportamientos distintos por rol:

- Root, administrador y usuario normal pueden editar datos completos.
- Operario solo puede actualizar horas reales de consumo y observaciones.

Para operarios, calcula una fecha base con `baseConsumptionDate()` y combina esa fecha con la hora introducida.

### `destroy(Cisterna $cisterna)`

Elimina una cisterna.

Solo root y administrador pueden hacerlo. Antes de eliminar, guarda en sesión algunas observaciones o incidencias para poder mostrarlas después si la vista lo necesita.

### `updateConsumo(Request $request, Cisterna $cisterna)`

Actualiza las horas reales de consumo desde el modal del listado.

Válida que las horas tengan formato `H:i`.

Si el usuario es operario:

- Solo rellena los campos enviados.
- Si un campo viene vacío, mantiene el valor anterior.

Si no es operario:

- Permite limpiar el campo dejándolo en `null`.

### `bulkUpload()`

Muestra la pantalla para subir un Excel.

Solo root y administrador.

### `bulkStore(Request $request)`

Recibe el Excel subido.

Hace estas operaciones:

- Válida que el archivo sea `xlsx` o `xls`.
- Lo guarda temporalmente en `storage`.
- Usa `ExcelImportService` para previsualizar los datos.
- Guarda la previsualización y la ruta temporal en sesión.
- Redirige a la pantalla de confirmacion.

### `bulkConfirm()`

Muestra la pantalla donde el usuario revisa y edita las filas extraídas del Excel.

Si no hay previsualización en sesión, vuelve a la pantalla de subida.

### `bulkConfirmStore(Request $request)`

Guarda las filas confirmadas de la importación.

Puntos importantes:

- Lee las filas enviadas por formulario.
- Si detecta que el POST viene truncado, vuelve a leer el Excel temporal y mezcla las ediciones enviadas en JSON.
- Omite filas no marcadas para importar.
- Omite filas con error.
- Normaliza `OF`, `NumeroCisterna` y `Conductor`.
- Si faltan datos obligatorios, omite la fila.
- Convierte `GlobalGAP` y `FDA` a booleanos.
- Normaliza observaciones vacías a `null`.
- Normaliza horas vacías a `null`.
- Si la cisterna ya existe:
  - En importación total la actualiza.
  - En importación seleccionada la omite.
- Si no existe, la crea.
- Borra el archivo temporal y limpia la sesión.

### `export(Request $request)`

Exporta cisternas a Excel.

Aplica filtros parecidos al listado:

- Año.
- Texto.
- Fecha.

Luego llama a `ExcelExportService` y descarga el archivo generado.

### `dashboard(Request $request)`

Calcula datos resumidos para el dashboard.

Indicadores calculados:

- Total de cisternas.
- Consumidas.
- Pendientes.
- Con incidencias.
- Programadas para hoy.
- En transito.
- Últimas cisternas creadas.
- Cisternas de hoy.
- Años disponibles.
- Cisternas filtradas por año seleccionado.

### Helpers privados del controlador

`autoConsumir(array $data, ?Cisterna $cisterna = null)`

Actualmente, devuelve los datos sin cambios. Está preparado como punto de extension para reglas automáticas de consumo.

`syncFechasConsumoEntrada(array $data)`

Copia `FechaConsumoMG` a `FechaEntradaMG`. Si no hay fecha de consumo, deja `FechaEntradaMG` en `null`.

`baseConsumptionDate(Cisterna $cisterna)`

Devuelve la fecha que se debe usar como base para guardar una hora real:

- Primero usa `FechaConsumoMG`.
- Si no existe, usa `FechaEntradaMG`.
- Si tampoco existe, usa la fecha actual.

`normalizeImportConsumptionHours(array $data)`

Convierte campos de hora vacíos en `null`. Evita guardar cadenas vacías en columnas de fecha/hora.

`normalizeRequiredImportFields(array $data)`

Convierte `OF` y `NumeroCisterna` a enteros si son numéricos. Si vienen vacíos o no numéricos, los deja como `null`. También limpia los espacios en `Conductor`.

`hasRequiredImportFields(array $data)`

Comprueba que una fila importada tenga `OF`, `NumeroCisterna` y `Conductor`.

`destroyAll()`

Elimina todas las cisternas mediante `truncate()`. Solo root y administrador.

## 7. Importación desde Excel

Archivo:

- `app/Services/ExcelImportService.php`

Este servicio lee archivos Excel con una estructura fija. Cada hoja puede representar una cisterna.

### Celdas esperadas

Datos principales:

- `M3`: OF.
- `M2`: número de cisternas.
- `H16`: conductor.
- `H17`: teléfono.
- `M9`: origen.
- `M10`: destino.
- `M5`: matrícula camion.
- `M6`: matrícula cisterna.
- `M7`: transporte.
- `M1`: fecha fabricación.

Fechas y horas:

- `D16`: fecha salida.
- `D17`: hora salida.
- `J16`: fecha llegada.
- `J17`: hora llegada.

Observaciones:

- `C14`: concepto.
- `H14`: BRIX.
- `L14`: kilos.
- `D15`: precintos.
- `J15`: tara.

### `preview(string $filePath, bool $skipExisting = true)`

Genera una lista de filas extraídas sin guardarlas.

Si `$skipExisting` es `true`, no devuelve cisternas que ya existan con la misma combinación `OF` + `NumeroCisterna`.

Añade dos campos técnicos:

- `_hoja`: nombre de la hoja de Excel.
- `_error`: error detectado, o `null`.

### `import(string $filePath)`

Importa directamente el Excel.

En este proyecto el flujo principal usa previsualización y confirmación, pero este método existe para importar sin pantalla intermedia.

Devuelve:

- `imported`: numero de cisternas creadas.
- `errors`: errores por hoja.

### `procesarExcel(string $filePath, callable $callback)`

Método base interno.

Hace estas operaciones:

- Carga el archivo con PhpSpreadsheet.
- Recorre cada hoja.
- Extrae datos con `extractFromSheet()`.
- Ignora hojas completamente vacías.
- Marca error si faltan datos obligatorios.
- Ejecuta el callback recibido para decidir qué hacer con la fila.
- Captura errores para que una hoja rota no pare toda la importación.

### `extractFromSheet(Worksheet $ws)`

Lee las celdas concretas de una hoja y construye un array compatible con el modelo `Cisterna`.

También inicializa algunos campos:

- Horas estimadas y reales de consumo a `null`.
- `GlobalGAP` y `FDA` a `false`.
- `Incidencias` a `null`.

### `cellValue(Worksheet $ws, string $coord)`

Devuelve el valor calculado de una celda como texto limpio, sin espacios al principio o final.

### `integerCellValue(Worksheet $ws, string $coord)`

Lee una celda que debería ser numérica.

Importante: si la celda está vacía, devuelve `null`, no `0`. Esto evita crear cisternas falsas con `OF = 0` o `NumeroCisterna = 0`.

### `buildObservaciones(Worksheet $ws)`

Une varias celdas del Excel en un solo texto de observaciones.

Ejemplo de formato:

```text
Concepto: X | BRIX: 12 | Kilos: 24000 | Precintos: A123 | Tara: 8000
```

Si todas las celdas están vacías, devuelve `null`.

### `parseDate($value)`

Convierte un valor de Excel a fecha.

soporta:

- Numero de serie de Excel.
- Objeto `DateTime`.
- Texto parseable como fecha.

Si el año es menor que 2000 o mayor que 2100, devuelve `null`.

### `parseDateTime($dateValue, $timeValue)`

Combina una fecha y una hora de Excel.

Si la hora existe, ajusta la hora del objeto fecha. Si no existe, conserva la fecha con la hora que tenga.

## 8. Confirmación de importación

Archivo:

- `resources/views/cisterna/bulk_confirm.blade.php`

Esta pantalla permite revisar y editar los datos antes de importarlos.

Elementos importantes:

- Un checkbox por fila para decidir si se importa.
- Inputs editables para campos principales.
- Campos de fecha y horas estimadas.
- Checkboxes para `GlobalGAP` y `FDA`.
- Textarea para observaciones.
- Modal para mostrar errores de una fila.

JavaScript propio:

- `toggleTodos(checked)`: marca o desmarca todos los checkboxes.
- `collectRowsData()`: lee todos los inputs de la tabla y genera un objeto con los valores editados.
- `submitImportAll()`: prepara el JSON de filas editadas y envía el formulario de importar todo.
- Listener del formulario principal: antes de enviar, guarda el JSON de filas editadas en un input oculto.
- `mostrarError(...)`: rellena y abre el modal de error.

El JSON oculto existe para evitar perder ediciones cuando el navegador no envía todos los campos como se espera o cuando se detecta un POST truncado.

## 9. Exportación a Excel

Archivo:

- `app/Services/ExcelExportService.php`

Genera un Excel temporal con las cisternas filtradas.

### `export(Collection $cisternas)`

Método público principal.

Hace estas operaciones:

- Crea un documento Excel.
- Crea la hoja `Cisternas`.
- Escribe cabeceras.
- Escribe datos.
- Aplica colores por estado.
- Ajusta anchos de columnas.
- Guarda un archivo temporal.
- Devuelve la ruta del archivo.

### `escribirCabecera($sheet)`

Escribe las columnas `A` a `T` con títulos como `OF`, `Nº Cisterna`, `Conductor`, `Fecha Consumo MG`, etc.

Aplica:

- Negrita.
- Texto blanco.
- Fondo oscuro.
- Alineación centrada.

### `escribirDatos($sheet, Collection $cisternas)`

Recorre todas las cisternas y, por cada una:

- Escribe sus datos.
- Aplica color según estado.

### `escribirFilaCisterna($sheet, int $fila, Cisterna $cisterna)`

Escribe una cisterna concreta en una fila del Excel.

Formatea fechas y horas para que sean legibles:

- Fechas completas: `d/m/Y H:i`.
- Fecha consumo: `d/m/Y`.
- Horas: `H:i`.

### `formatearBooleano(?bool $valor)`

Convierte booleanos a texto:

- `true` -> `Si`.
- `false` -> `No`.
- `null` -> raya o valor vacío según visualización.

### `aplicarColorFila($sheet, int $fila, Cisterna $cisterna, $hoy)`

Aplica color a toda la fila si corresponde.

### `determinarColorFila(Cisterna $cisterna, $hoy)`

Prioridad de colores:

- Rojo si tiene incidencias.
- Verde si ya tiene hora real de consumo.
- Azul si la fecha de consumo es hoy.
- Amarillo si la fecha de consumo es futura.
- Sin color si no aplica.

### `ajustarAnchosColumnas($sheet)`

Activa ancho automático para columnas `A` a `T`.

### `generarArchivo(Spreadsheet $spreadsheet)`

Guarda el Excel en `storage/app/private/temp` con un nombre tipo:

```text
cisternas_2026-05-14_12-30.xlsx
```

## 10. Administración de usuarios

Archivo:

- `app/Http/Controllers/AdminController.php`

Controlador para gestionar usuarios del sistema.

### `index()`

Lista todos los usuarios ordenados por `fecha_registro` descendente.

### `create()`

Muestra el formulario de creación y envía a la vista los roles disponibles.

### `store(Request $request)`

Crea un usuario.

Hace estas operaciones:

- Válida el email único, password generado y rol.
- Genera un password determinística a partir del email.
- Comprueba que el password enviado coincide con la generada.
- Crea el usuario con password hasheada.
- Registra la acción en logs.
- Redirige mostrando la contraseña generada.

### `toggle(User $user)`

Activa o desactiva un usuario.

No permite modificar el usuario root protegido `root@local.es`.

### `changeRole(Request $request, User $user)`

Cambia el rol de un usuario.

No permite cambiar el rol del root protegido.

### `destroy(User $user)`

Elimina un usuario.

No permite eliminar el root protegido.

### `edit(User $user)`

Muestra el formulario de edición de usuario.

### `show(User $user)`

Muestra detalles del usuario, incluyendo:

- Password generado teórico.
- Capacidades según rol.

### `update(Request $request, User $user)`

Actualiza rol y estado activo.

### Helpers privados

`esUsuarioRootProtegido(User $user)`

Devuelve `true` si el email es `root@local.es`.

`crearUsuario(string $email, string $role, string $password)`

Crea el usuario en base de datos con:

- Nombre sacado de la parte antes de `@`.
- Email.
- Password hasheada.
- Rol.
- Usuario activo.
- Fecha de registro actual.

`logCreacionUsuario(string $userEmail)`

Escribe en log que un administrador creo un usuario.

`generatePasswordFromEmail(string $email)`

Genera una password a partir del email:

1. Toma la parte antes de `@`.
2. La pasa a mayúsculas.
3. Añade el código ASCII del primer carácter.
4. Añade el código ASCII del último carácter.

Ejemplo aproximado:

```text
Email: juan@empresa.com
Parte local: JUAN
Password: JUAN7488
```

`getRoleCapabilities(string $role)`

Devuelve textos descriptivos con lo que puede hacer cada rol.

## 11. Planificación

Archivo:

- `app/Http/Controllers/PlanificacionController.php`

La planificación no usa una tabla de base de datos. Guarda sus filas en:

```text
storage/app/planificación.json
```

Cada fila tiene:

- `id`: identificador generado con `uniqid()`.
- `NumeroCisterna`.
- `Destino`.
- `FechaConsumo`.
- `FechaFabricacionHuelva`.
- `HoraEstimadaConsumoL1`.
- `HoraEstimadaConsumoL2`.

### `__construct()`

Calcula la ruta completa del archivo JSON.

### `leerFilas()`

Lee el JSON.

Si el archivo no existe o no contiene un array válido, devuelve `[]`.

### `guardarFilas(array $filas)`

Guarda el array de filas en JSON.

Si la carpeta no existe, la crea.

### `index()`

Muestra la vista de planificación con las filas leídas del JSON.

### `store(Request $request)`

Crea una fila de planificación.

Valida:

- Número de cisternas obligatorias.
- Destino opcional.
- Fechas opcionales.
- Horas opcionales con formato `H:i`.

Después genera un `id`, guarda la fila y redirige.

### `edit(string $id)`

Busca una fila por `id` y muestra el formulario de edición.

Si no existe, devuelve 404.

### `update(Request $request, string $id)`

Válida y actualiza una fila existente.

### `destroy(string $id)`

Elimina una fila concreta por `id`.

### `clear()`

Borra toda la planificación guardando un array vacío.

### `exportar()`

Exporta la planificación a Excel.

Si no hay filas, redirige con error.

### `generarExcelPlanificacion(array $filas)`

Crea un Excel con columnas:

- `Nº Cisterna`.
- `Destino`.
- `Fecha Consumo`.
- `Fecha Fab. Huelva`.
- `H.E.C L1`.
- `H.E.C L2`.

Aplica estilo a la cabecera, ajusta columnas y guarda el archivo temporal.

### `verificarPermisoAdmin()`

Solo permite modificar planificación a administradores.

## 12. Vistas principales

Vistas de cisternas:

- `resources/views/cisterna/index.blade.php`: listado principal, filtros, tabla, estados y modal de consumo.
- `resources/views/cisterna/create.blade.php`: formulario para crear cisterna.
- `resources/views/cisterna/edit.blade.php`: formulario para editar cisterna.
- `resources/views/cisterna/show.blade.php`: detalle de cisterna.
- `resources/views/cisterna/bulk.blade.php`: subida de Excel.
- `resources/views/cisterna/bulk_confirm.blade.php`: revision y confirmación de importación.
- `resources/views/cisterna/dashboard.blade.php`: dashboard de indicadores.

Vistas de administración:

- `resources/views/admin/users.blade.php`: listado de usuarios.
- `resources/views/admin/create.blade.php`: crear usuario.
- `resources/views/admin/edit.blade.php`: editar usuario.
- `resources/views/admin/show.blade.php`: detalle de usuario.

Vistas de planificación:

- `resources/views/planificacion/index.blade.php`: listado y acciones de planificación.
- `resources/views/planificacion/edit.blade.php`: edición de una fila.

## 13. Flujo completo de importación Excel

1. El administrador entra en `Importar Excel`.
2. Sube un archivo `.xlsx` o `.xls`.
3. `bulkStore()` guarda el archivo temporalmente.
4. `ExcelImportService::preview()` lee las hojas.
5. Se guarda la previsualizacion en sesion.
6. Se abre `bulk_confirm.blade.php`.
7. El usuario revisa, edita y marca filas.
8. Al enviar, se mandan los campos normales y un JSON con las ediciones.
9. `bulkConfirmStore()` valida y normaliza los datos.
10. Las filas incompletas, duplicadas o con error se omiten.
11. Las filas válidas se crean o actualizan según el modo.
12. Se borra el archivo temporal.
13. Se limpia la sesion.
14. Se vuelve al listado con un resumen.

## 14. Flujo completo de consumo

1. En el listado, el usuario pulsa el botón de reloj de una cisterna.
2. JavaScript abre el modal y rellena las horas existentes.
3. El usuario introduce `HoraRealConsumoL1` o `HoraRealConsumoL2`.
4. El formulario envía un `PATCH` a `/cisterna/{cisterna}/consumo`.
5. `updateConsumo()` valida formato de hora.
6. Calcula fecha base con `baseConsumptionDate()`.
7. Guarda la hora real como fecha completa.
8. Redirige al listado.

## 15. Estados visuales de cisternas

En el listado y exportaciones se usan estados para colorear o etiquetar filas.

Criterios habituales:

- Consumida: tiene `HoraRealConsumoL1` o `HoraRealConsumoL2`, o destino especial Tamarite de Litera.
- Incidencia: tiene texto en `Incidencias`.
- Hoy: `FechaConsumoMG` es la fecha actual.
- Futura: `FechaConsumoMG` es posterior a hoy.
- Pendiente: no entra en los casos anteriores.

## 16. Dependencias importantes

Dependencias PHP relevantes:

- `laravel/framework`: base de la aplicación.
- `phpoffice/phpspreadsheet`: lectura y escritura de Excel.
- `doctrine/dbal`: soporte adicional para base de datos.

Dependencias frontend relevantes:

- `vite`: empaquetado frontend.
- `tailwindcss`: estilos.
- `alpinejs`: interactividad ligera.
- `axios`: peticiones HTTP si se necesitan desde JavaScript.

## 17. Archivos temporales y almacenamiento

Importación:

- Los Excel subidos se guardan temporalmente con `store('temp')`.
- La ruta temporal se guarda en sesión como `bulk_tempPath`.
- Al terminar la importacion se elimina con `Storage::delete()`.

Exportación:

- Los Excel generados se guardan en `storage/app/private/temp`.
- Se descargan con `response()->download(...)->deleteFileAfterSend(true)`.
- Esto borra el archivo después de enviarlo al navegador.

Planificación:

- Se guarda en `storage/app/planificacion.json`.
- No usa tabla SQL.

## 18. Puntos delicados del proyecto

- La importación depende de celdas concretas del Excel. Si cambia la plantilla, hay que actualizar las constantes de `ExcelImportService`.
- `OF`, `NumeroCisterna` y `Conductor` son obligatorios para importar.
- La combinación `OF` + `NumeroCisterna` se usa para detectar duplicados.
- La planificación se guarda en JSON, asi que no tiene historial ni relaciones de base de datos.
- Hay roles escritos con variantes (`admin` y `Administrador`, `user` y `Usuario`). Conviene mantener consistencia si se crean usuarios o migraciones nuevas.
- El proyecto usa formato de fecha `Ymd H:i:s`, importante si se conecta con SQL Server.

## 19. Donde modificar cada cosa

- Cambiar celdas del Excel de entrada: `app/Services/ExcelImportService.php`.
- Cambiar columnas del Excel exportado: `app/Services/ExcelExportService.php`.
- Cambiar permisos de cisternas: `app/Http/Controllers/CisternaController.php` y `app/Models/User.php`.
- Cambiar roles o capacidades: `app/Models/User.php` y `app/Http/Controllers/AdminController.php`.
- Cambiar rutas: `routes/web.php`.
- Cambiar campos de cisternas: migración de `cisternas`, modelo `Cisterna`, formularios Blade y controlador.
- Cambiar planificación: `app/Http/Controllers/PlanificacionController.php` y vistas de `resources/views/planificacion`.
- Cambiar listado principal: `resources/views/cisterna/index.blade.php` y método `index()` de `CisternaController`.
- Cambiar importación visual: `resources/views/cisterna/bulk_confirm.blade.php`.

## 20. Comandos útiles del proyecto

Instalar dependencias PHP:

```bash
composer install
```

Instalar dependencias frontend:

```bash
npm install
```

Ejecutar migraciones:

```bash
php artisan migrate
```

Levantar en torno simple:

```bash
composer run dev-simple
```

Ejecutar tests:

```bash
composer test
```

Compilar frontend:

```bash
npm run build
```

Limpiar caches:

```bash
composer clear
```
