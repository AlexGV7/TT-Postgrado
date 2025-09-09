# 🔧 Anexo técnico: Servicios personalizados

Este anexo detalla los servicios específicos creados para extender la funcionalidad del proyecto, facilitando tareas complejas como integración con DataTables, generación de correos, sincronización de datos externos, entre otros. Se recomienda su revisión para entender el funcionamiento interno y extender la plataforma de manera coherente.

---

## Servicio para DataTables Server Side (`App\Service\DataTableService`)

Este servicio permite integrar **tablas Server Side** mediante la biblioteca JavaScript [DataTables](https://datatables.net/) con una respuesta generada directamente desde Symfony, optimizando el rendimiento en bases de datos con gran volumen de registros.

### a) Archivos principales

- **`DataTableService.php`**: Servicio central que encapsula toda la lógica de paginación, ordenamiento, filtrado y formateo de la respuesta en formato JSON requerido por DataTables en modo server-side.
- **`tables.js`**: Script JavaScript donde se inicializa DataTables, se configura su comportamiento (columnDefs, ajax, botones personalizados, getters, etc.) y se establece la lógica de interacción con el backend.
- **`ExampleController.php`**: Controlador Symfony donde se define una ruta dedicada a entregar la respuesta JSON que DataTables espera. Este método utiliza `DataTableService` y define joins, filtros y condiciones.
- **`ExampleRepository.php`**: Repositorio de Doctrine de la entidad en cuestión, donde se declaran métodos genéricos que el servicio utilizará para construir y ejecutar las consultas de datos y conteo total.

### b) Comunicación frontend-backend usando DataTables (`tables.js`)

Esta sección explica cómo se configura la comunicación entre el frontend y el backend utilizando DataTables en modo server-side. Se detallan los parámetros personalizados enviados desde JavaScript, que permiten al backend generar respuestas dinámicas según la configuración de columnas, acciones y búsquedas definidas en cada tabla.

#### Explicación de propiedades personalizadas:

- `d.getters`: Lista de métodos que se llamarán en cada objeto del backend para obtener los valores de cada columna.

- `d.buttons`: Define los botones de acción que aparecerán en la última columna.

    - `path`: Nombre de la ruta Symfony.

    - `class`: Clases CSS para el botón.

    - `label`: Texto visible del botón.

    - `icon`: (opcional) Icono asociado proveniente de material-icons, si aplica.

    - `target`: (opcional) Atributo HTML para abrir en nueva pestaña.

    - `conditional`: (opcional) Método booleano para condicionar si se muestra el botón.

    - `params`: (opcional) Diccionario clave => getter para enviar parámetros personalizados a la ruta. Por defecto siempre dará el ID de la entidad.

- `d.searchBy`: Objeto que define cómo realizar búsquedas personalizadas en columnas relacionadas (joins).

    - `columns`: Lista de columnas específicas que se utilizarán para buscar.

    - `pattern`: (opcional) Patrón de búsqueda mediante regex

#### Ejemplo de uso

Este ejemplo demuestra cómo se inicializa una tabla con DataTables, definiendo nombres de columnas, getters para obtener los datos desde la entidad, y botones para acciones personalizadas.

```js
$('.datatable-users-students').DataTable({
    autoWidth: false,
    // En casos de búsqueda y ordenamiento, por defecto tratará de usar
    // el valor de la propiedad que coincida con name, a menos que especifiquemos
    // lo contrarío en searchBy, dentro de los parámetros de ajax.data

    columnDefs: [
        { name: "lastName", width: "15%", "targets": 0 },
        { name: "name", width: "15%", "targets": 1 },
        { name: "email", width: "17%", "targets": 2 },
        { name: "sex", width: "8%", "targets": 3 },
        { name: "faculty", width: "15%", "targets": 4 },
        { name: "major", width: "15%", "targets": 5 },
        { name: "Actions", width: "15%", "targets": 6, orderable: false, searchable: false },
    ],
    processing: true,
    serverSide: true,
    ajax: {
        url: '/user/student/ajax',
        type: 'POST',
        data: function (d) {
            // Se utilizarán estos getter para mostrar la información en cada
            // casilla de la tabla de datos
            d.getters = [
                'getLastName', 
                'getName', 
                'getEmail',
                'getSexChar',
                'getStudentFacultyName',
                'getStudentMajorName'
            ];
            // Se utilizarán estas rutas como botones de acción dentro de
            // la tabla de datos.
            d.buttons = [
                {
                    path: 'app_user_show',
                    class: 'btn btn-xs btn-info',
                    label: 'ver'
                },
                {
                    path: 'app_user_edit',
                    class: 'btn btn-xs btn-secondary',
                    label: 'editar',
                    icon: 'edit'
                },
                {
                    path: 'app_user_siding_update',
                    class: 'btn btn-xs btn-primary',
                    label: 'Sincronizar',
                    target: '_blank',
                    icon: 'sync'
                },
                {
                    path: 'app_user_impersonate',
                    class: 'btn btn-xs btn-danger',
                    label: 'personificar',
                    icon: 'people_alt'
                }
            ];
            // En caso de tener que buscar un valor en otra tabla que este relacionada 
            // mediante un join se debe especificar aquí cuál es el valor objetivo.
            // Este valor también será utilizado al realizar un ordenamiento
            // Se deben utilizar los alias acuñados a cada tabla
            // en el controlador de la entidad.
            d.searchBy = {
                'faculty': {
                    'columns': ['f.name'],
                },
                'major': {
                    'columns': ['m.name'],
                },
            };
        }
    },
});
```


### c) Ejemplo de ruta en el controlador (`ExampleController.php`)

Este controlador se encarga de procesar las solicitudes AJAX provenientes de DataTables. Define joins y condiciones personalizados para enriquecer los datos renderizados en la tabla.

```php
#[Route('/student/ajax', name: 'user_student_ajax', methods: ['GET', 'POST'])]
public function tableStudent(DataTableService $service, Request $request, UserRepository $userRepository) {

    // En caso de tener que buscar información dentro de otras tablas que
    // tengan relaciones con la entidad, se deben especificar los joins
    // de la siguiente forma
    $joins = [
        ['parentEntity' => 'table', 'relation' => 'student', 'alias' => 's'],
        ['parentEntity' => 's', 'relation' => 'faculty', 'alias' => 'f'],
        ['parentEntity' => 's', 'relation' => 'major', 'alias' => 'm'],
    ];

    // Para aplicar filtros o condiciones, se deben especificar de la siguiente forma
    $conditions = "table.roles LIKE '%ROLE_ENGINEER_STUDENT%' OR table.roles LIKE '%ROLE_EXTERNAL_STUDENT%'";
    
    // Obtenemos la información proveniente desde la base de datos
    $response = $service->getData($request, $userRepository, $joins, $conditions);

    // Retornamos un JSON con el cual interactuará la tabla de datos
    $returnResponse = new JsonResponse();
    $returnResponse->setJson($response);
    return $returnResponse;
}
```

### d) Métodos necesarios en el repositorio (`ExampleRepository.php`)

El repositorio necesita exponer dos métodos clave: uno para contar los registros y otro para obtener los resultados aplicando filtros, ordenamientos y paginación de acuerdo a la solicitud.

```php
public function __construct(ManagerRegistry $registry, DataTableService $service)
{
    parent::__construct($registry, User::class);
    $this->service = $service;
}

public function countObjects() {
    return $this
        ->createQueryBuilder('object')
        ->select("count(object.id)")
        ->getQuery()
        ->getSingleScalarResult();
}

public function getTableData($start, $length, $orders, $search, $columns, $joins, $conditions, $searchBy = []) {

    $table = 'table';

    $query = $this->createQueryBuilder($table);

    $countQuery = $this->createQueryBuilder($table);

    $this->service->addJoins($query, $countQuery, $joins);

    $this->service->addConditions($query, $countQuery, $conditions);

    $this->service->countObjectsInTable($countQuery,$table);

    $this->service->setLength($countQuery, $length);

    if ($search['value'] != "") {
        $this->service->performSearch($query, $countQuery, $table, $columns, $search, $searchBy);
    }

    $this->service->addLimits($query, $start, $length);
        
    $this->service->performOrdering($query, $orders, $table, $searchBy);

    $results = $query->getQuery()->getResult();
    $countResult = $countQuery->getQuery()->getSingleScalarResult();       
        
    return array(
        "results"       => $results,
        "countResult"   => $countResult
    );
}
```

### e) Renderizado del HTML en Twig

En el frontend, no es necesario definir el cuerpo de la tabla manualmente. Solo basta con incluir un componente Twig que renderiza el `<thead>` con los encabezados definidos, y el resto es gestionado automáticamente por DataTables con datos del backend.

#### Ejemplo de uso en una plantilla (`index.html.twig`)

```twig
{% include 'components/data-table.html.twig' with {
    customClass: 'datatable-users-students',
    serverSide: true,
    columns: [
        { label: 'Apellido' },
        { label: 'Nombre' },
        { label: 'Email' },
        { label: 'Género' },
        { label: 'Facultad' },
        { label: 'Major' },
        { label: 'Acciones' }
    ],
} %}
```

#### Componente reutilizable (components/data-table.html.twig)
```twig
<div class="container-fluid p-0">
    <div class="table-responsive">
        <table class="table table-striped {{ customClass }}">
            <thead>
                <tr>
                    {% for column in columns %}
                        <th>{{ column.label }}</th>
                    {% endfor %}
                </tr>
            </thead>
        </table>
    </div>
</div>
```


## Otros servicios personalizados

A continuación se describen brevemente otros servicios clave desarrollados para la plataforma, que permiten la integración con sistemas externos, la gestión de configuraciones, notificaciones y lógica de negocio avanzada.

---

### SidingService (`App\Service\Siding`)

Servicio para la integración con el sistema SIDING de la Escuela de Ingeniería UC. Permite autenticar usuarios, obtener información académica, consultar requisitos y estados de cursos, y sincronizar datos relevantes desde Siding.

---

### SidService (`App\Service\Sid`)

Servicio de consulta directa a la base de datos SID de la Escuela de Ingeniería UC. Se utiliza principalmente para obtener información avanzada sobre mentores, como su categoría académica, y otros datos institucionales.

---

### NotificationService (`App\Service\NotificationService`)

Servicio encargado del envío de notificaciones por correo electrónico a usuarios, mentores, estudiantes y administradores. Centraliza la lógica de generación de mensajes y el envío de emails transaccionales y automáticos.

---

Cada uno de estos servicios puede ser extendido o reutilizado en nuevos módulos, facilitando la escalabilidad y el mantenimiento del sistema. Se recomienda revisar su código fuente para comprender su funcionamiento y posibilidades de integración.