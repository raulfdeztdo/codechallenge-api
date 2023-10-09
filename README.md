<p align="center">
    <img src="[https://media.licdn.com/dms/image/C4D1BAQGHrcRmbivWoQ/company-background_10000/0/1595936903607?e=1696690800&v=beta&t=LyW64PwCMCxoSZ8JFfrWgZu6zjfZrZLaDRdZbFC9KZs](https://asset.brandfetch.io/idBPU3UFya/idx5evPLCW.jpeg)" alt="iAhorro Cabecera">
</p>

# Prueba técnica

## iAhorro Code Challenge
Estimado candidato:

Tu tarea es refactorizar un controlador de **API REST** en **Laravel 10** que actualmente
contiene métodos para crear, mostrar, editar, actualizar y eliminar registros de la
entidad "Lead". El controlador proporcionado tiene problemas de diseño y no sigue las
mejores prácticas de codificación. Se espera que implementes principios de "Clean
Code" y patrones SOLID en tu solución.

### El controlador actual contiene los siguientes métodos:

* **create**: para crear un nuevo lead.
* **show**: para mostrar los detalles de un lead existente.
* **edit**: para editar un lead existente.
* **update**: para actualizar un lead existente.
* **destroy**: para eliminar un lead existente.

Además, el controlador interactúa con un servicio de scoring, que determina la calidad
del lead.

## Objetivo

Tu objetivo es revisar el código proporcionado y realizar las acciones necesarias para
mejorar su calidad. Algunas áreas a tener en cuenta son:

1. Aplicar principios SOLID y "Clean Code".
2. Evaluar el manejo de respuestas y códigos HTTP.
3. Mejorar la legibilidad y mantenibilidad del código.
4. Considerar la separación de responsabilidades y la estructura del código.
5. Manejar adecuadamente los errores.
6. Realizar los tests necesarios.

## Entrega

Por favor, envía un repositorio de Git con tu solución, que debe incluir:

* Código fuente.
* Pruebas automatizadas.
* Instrucciones para instalar y correr tu proyecto localmente.
* Un archivo README que explique tu enfoque, decisiones de diseño y cómo correr las pruebas.

Nos interesa no solo que el código funcione, sino también tu enfoque para resolver problemas y la calidad de tu código.

Buena suerte y esperamos ver tu solución.

# Solución

Para abarcar esta solución necesitamos crear un proyecto de Laravel 10 en el que podamos implementar el controlador dado y refactorizarlo.

Por lo que empezamos creando el repositorio clonandolo y creando el proyecto de Laravel y levantando el entorno con los comandos:

```curl -s "https://laravel.build/codechallenge-api" | bash ```

```sail up```

Una vez creado el proyecto y levantado el entorno analizamos el controlador dado y necesitaremos crear el modelo y la migración Lead y de Client mediante:

```sail artisan make:model Lead -m```

```sail artisan make:model Client -m```

Con estos comandos se crean los modelo Lead y Cliente junto con sus migraciones para crear las tablas en la base de datos. Una vez completada las migraciones quedarían así:

```
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->integer('score')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
```

```
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_id');
            $table->string('email')->unique();
            $table->timestamps();

            $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};

```

Y los modelos quedarían de la siguiente forma:

```
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'phone', 'score'];
}
```

```
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'lead_id'
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
}

```

Continuamos creando el service, creando la carpeta "Services" dentro de "app" y creando el service "LeadScoringService.php" que implementaremos de la siguiente forma con una función que devolverá un número aleatorio para simular la obtención del score:

```
<?php

namespace App\Services;

use App\Models\Lead;

class LeadScoringService
{
    public function getLeadScore(Lead $lead): int
    {
        // Por simplicidad, se devolverá un número aleatorio para simular el scoring
        return rand(1, 100);
    }
}
```
 ### Refactorización del código:

Vamos a refactorizar el código proporcionado del controlador LeadController.php para hacerlo más limpio, mantenible y siguiendo las mejores prácticas. En particular, vamos a realizar el refactor del código aplicando los principios SOLID y Clean Code:

- S - Principio de Responsabilidad Única (SRP): Asegurarse de que la clase tenga una sola razón para cambiar.
- O - Principio Abierto/Cerrado (OCP): Software debe estar abierto para la extensión pero cerrado para la modificación.
- L - Principio de Sustitución Liskov (LSP): Instancias de la superclase deberían poder ser reemplazadas por instancias de la subclase sin afectar la corrección del programa.
- I - Principio de Segregación de la Interfaz (ISP): No forzar a una clase a implementar interfaces que no va a usar.
- D - Principio de Inversión de Dependencia (DIP): Dependencias deben ser abstracciones no concreciones.

Teniendo en cuenta los principios SOLID descritos realizamos las siguientes modificaciones:

- **Inyección de dependencias e implantación del servicio:**</br>
En vez de instanciar LeadScoringService dentro del controlador, vamos a inyectarlo en el constructor de LeadService, el cual también vamos a implementar el servicio para separar partes del código que son más propias de un servicio y dejar en el controlador el código que recibe o no datos y devuelve directamente los datos una vez pasados por el servicio.

- **Mejor manejo de errores:**</br>
Utilizar una función getLead() en la que comprueba si existe el Lead y que se utiliza en distintas funciones del controlador y servicio, si no existe soltará una excepción que se capturará en distintas funciones del controlador, segun sea la petición e informará de que no ha encontrado el Lead. Además retornará códigos de estado HTTP adecuados en el controlador con mensaje de error en vez de cadenas de texto.

- **Limpieza de código innecesario:**</br>
Al ser una API REST no es necesario devolver vistas, por lo que omitimos las funciones de create, edit y añadimos una función index para mostrar un listado de Leads.

Por lo que el controlador LeadController.php y el servicio LeadService.php quedarían con el siguiente código:

```
<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Services\LeadService;

/**
 * Lead Controller.
 */
class LeadController extends Controller
{
    /**
     * Constructor del controlador.
     *
     * @param LeadService $leadService El servicio para gestionar los Leads.
     */
    public function __construct(private LeadService $leadService)
    {
    }

    /**
     * Listar todos los Leads.
     *
     * @return \Illuminate\Database\Eloquent\Collection|Lead[] Listado de Leads.
     */
    public function index()
    {
        return Lead::all();
    }

    /**
     * Almacenar un nuevo Lead.
     *
     * @param Request $request Los datos del Lead a crear.
     * @return \Illuminate\Http\JsonResponse La respuesta después de crear un Lead.
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:clients',
                'phone' => 'nullable|numeric|digits_between:5,10',
            ]);

            $lead = $this->leadService->createLead($data);

            return response()->json([
                'message' => 'Lead creado con éxito',
                'lead' => $lead
            ], Response::HTTP_CREATED);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], $e->status);
        }
    }

    /**
     * Mostrar un Lead específico.
     *
     * @param int $idLead El identificador del Lead a mostrar.
     * @return \Illuminate\Http\JsonResponse Los detalles del Lead o un mensaje de error.
     */
    public function show($idLead)
    {
        try {
            $lead = $this->leadService->getLead($idLead);
            return response()->json($lead);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Lead no encontrado'], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Actualizar un Lead específico.
     *
     * @param Request $request Los datos a actualizar.
     * @param int $idLead El identificador del Lead a actualizar.
     * @return \Illuminate\Http\JsonResponse La respuesta después de actualizar el Lead.
     */
    public function update(Request $request, $idLead)
    {
        $currentEmail = Lead::find($idLead)->email;
        $rules = [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|numeric|digits_between:5,10',
        ];
        if ($currentEmail != $request->input('email')) {
            $rules['email'] = 'required|email|max:255|unique:clients,email';
        } else {
            $rules['email'] = 'required|email|max:255';
        }

        try {
            $data = $request->validate($rules);

            $lead = $this->leadService->updateLead($idLead, $data);
            return response()->json(['message' => 'Lead actualizado con éxito', 'lead' => $lead]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], $e->status);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Lead no encontrado'], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Eliminar un Lead específico.
     *
     * @param int $idLead El identificador del Lead a eliminar.
     * @return \Illuminate\Http\JsonResponse La respuesta después de eliminar el Lead.
     */
    public function destroy($idLead)
    {
        try {
            $this->leadService->deleteLead($idLead);
            return response()->json(['message' => 'Lead eliminado con éxito']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Lead no encontrado'], Response::HTTP_NOT_FOUND);
        }
    }
}

```

```<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Client;
use App\Services\LeadScoringService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Lead Service.
 */
class LeadService
{
    /**
     * Servicio para obtener el puntaje (score) de un Lead.
     */
    private LeadScoringService $leadScoringService;

    /**
     * Constructor del servicio.
     *
     * @param LeadScoringService $leadScoringService Servicio para calcular el puntaje de los Leads.
     */
    public function __construct(LeadScoringService $leadScoringService)
    {
        $this->leadScoringService = $leadScoringService;
    }

    /**
     * Crear un nuevo Lead y asignarle un puntaje.
     *
     * @param array $data Datos del Lead a crear.
     * @return Lead El Lead creado.
     */
    public function createLead(array $data): Lead
    {
        $lead = Lead::create($data);

        Client::create([
            'email' => $lead->email,
            'lead_id' => $lead->id
        ]);

        $score = $this->leadScoringService->getLeadScore($lead);
        $lead->score = $score;
        $lead->save();

        return $lead;
    }

    /**
     * Obtener un Lead específico por su ID.
     *
     * @param int $idLead El identificador del Lead.
     * @return Lead El Lead encontrado.
     * @throws ModelNotFoundException Si no se encuentra el Lead.
     */
    public function getLead(int $idLead): Lead
    {
        if (!$lead = Lead::find($idLead)) {
            throw new ModelNotFoundException("Lead no encontrado");
        }
        return $lead;
    }

    /**
     * Actualizar un Lead y obtiene un nuevo puntaje.
     *
     * @param int $idLead El identificador del Lead a actualizar.
     * @param array $data Datos para actualizar el Lead.
     * @return Lead El Lead actualizado.
     */
    public function updateLead(int $idLead, array $data): Lead
    {
        $lead = $this->getLead($idLead);
        $lead->update($data);

        // Obtener nuevo puntaje tras la actualización
        $score = $this->leadScoringService->getLeadScore($lead);
        $lead->score = $score;
        $lead->save();

        return $lead;
    }

    /**
     * Eliminar un Lead específico.
     *
     * @param int $idLead El identificador del Lead a eliminar.
     * @return void
     */
    public function deleteLead(int $idLead): void
    {
        $lead = $this->getLead($idLead);
        $lead->delete();
    }
}

```

Una vez creado todo esto necesitamos modificar nuestro fichero api.php para añadir las rutas necesarias a las que se realizarán las modificaciones, para realizar posteriormente pruebas mediante el software Insomnia añadimos un middleware y una petición login para obtener un token que será necesario para las pruebas y asi añadimos seguridad en nuestra API REST.

```
Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('leads')->group(function () {
        Route::get('/list', [LeadController::class, 'index']);
        Route::post('/store', [LeadController::class, 'store']);
        Route::get('/{id}', [LeadController::class, 'show']);
        Route::post('/{id}', [LeadController::class, 'update']);
        Route::delete('/{id}', [LeadController::class, 'destroy']);
    });
});

Route::post('/login', [AuthController::class, 'login']);
```

## Pruebas

Vamos a realizar dos tipos de pruebas unas mediante Insomnia y otra mediante pruebas unitarias.

### Requisitos

- Docker y Docker Compose: Debes tener Docker y Docker Compose instalados en tu máquina. Si aún no los tienes, puedes descargarlos desde el sitio oficial de Docker.
    - Si se quiere probar desde Windows se tiene que tener instalado wsl2 y que este activada la relación con Docker desde los ajustes de Docker

- Git: Necesitarás Git para clonar el repositorio.
- Composer: Es necesario para instalar las dependencias del proyecto

### Pasos comunes

Los siguientes pasos son comunes para realizar los 2 tipos de pruebas:

1. Clonar repositorio -> ```git clone https://github.com/raulfdeztdo/codechallenge-api.git```
2. Posicionarte en el directorio del repositorio -> ```cd codechallenge-api```
3. Copiar las variables de entorno -> ```cp .env.example .env```
4. Instalar las dependencias del proyecto -> ```composer install```
    - Si se usa Windows aqui hay que entrar por wsl a la maquina virtualizada de Ubuntu (por ejemplo) y acceder a la ruta del proyecto (normalmente por /mnt/c/...) para proseguir con los siguientes pasos.
5. Dar permiso de ejecución a Sail -> ```chmod +x ./vendor/bin/sail```
6. Iniciar entorno sail -> ```./vendor/bin/sail up```
7. Instalar las claves del proyecto -> ```./vendor/bin/sail artisan key:generate```
8. Ejecutar migraciones -> ```./vendor/bin/sail artisan migrate```
    - Si al realizar las migraciones ocurre algun problema, habría que entrar a mysql como root y dar los privilegios necesarios al ususario "sail":
        - ```./vendor/bin/sail bash```
        - ```mysql -h mysql -u root -ppassword```
        - ```GRANT ALL ON codechallenge_api.* TO 'sail'@'%';```
        - ```FLUSH PRIVILEGES;``` --> Despues de ejecutar salir de mysql y del bash
        - ```./vendor/bin/sail down```
        - ```./vendor/bin/sail up -d```
        - ```./vendor/bin/sail artisan migrate```
9. Ejecutar seeders para añadir datos a la base de datos -> ```./vendor/bin/sail artisan db:seed```

### Insomnia

Para realizar las pruebas mediante el software Insomnia es necesario tener el proyecto levantado como se ha explicado en los pasos anteriores, y si no ha habido ningún error procederemos a continuar con los pasos siguientes para realizar las peticiones a nuestra API REST.


1. Abrir Insomnia y realizar una peticion tipo POST a http://localhost/api/login con los siguientes datos
    - Key: "email", Value: "test@email.com"
    - Key: "password", Value: "PruebaCodeChallenge"
2. El paso anterior devolverá un token que será necesario para realizar las pruebas
3. Por ejemplo para realizar una petición list que devolverá un listado de los leads se necesita hacer una peticion GET a la url http://localhost/api/leads/list y como metodo de autentificación se elegirá "Bearer Token" en el que se debe poner el token obtenido en el paso 1

Ejemplo de obtención de datos de una petición list y una petición store:

![Petición List](https://i.ibb.co/F0bbkJ9/image.png)

![Petición Store](https://i.ibb.co/HtjHSb9/image.png)


### Pruebas unitarias

Para realizar las pruebas unitarias hemos creado un fichero test mediante el comando ```sail artisan make:test LeadControllerTest``` y en el que hemos implementado pruebas para listar, almacenar, mostrar, actualizar y borrar leads.

Ejecutar las pruebas ```./vendor/bin/sail artisan test --filter LeadControllerTest```

Aqui se muestra un ejemplo de la resolución de las pruebas, en la imagen 1 se realizan las pruebas desde un sistema macOS donde se ha desarrollado el proyecto y en la imagen 2 se muestra la salida de los test desde un sistema con Windows 11 donde se ha levantado el entorno mediante Docker y wsl:

#### macOS

![Resultado pruebas unitarias - macOS](https://i.ibb.co/M2F2Xcp/image.png)

#### Windows

![Resultado pruebas unitarias - Windows 11](https://i.ibb.co/rdGQ7jb/image.png)
