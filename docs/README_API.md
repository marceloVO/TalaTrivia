# TalaTrivia API - Documentación 
Base URL: http://localhost:8080

## Autores
- TalaTrivia
- Marcelo Verdejo Olivares
## proyecto creado con docker y php con el framework de symfony
## el proyecto consta de 1 usuario admin para poder crear categorias, preguntas, trivias, respuestas en ese orden 
## para poder jugar se necesitaria previamente haber creado estos pasos y tener un usuario normal para recien poder jugar
## authController sirve para crear usuarios admins, player, logear y deslogear la cuenta
## primer paso
- se debe crear el contenedor de docker con el siguiente comando docker-compose build --no-cache
- levantar los servicios -> docker-compose up -d
- una vez levantado el servicio hay que ejecutar el comando docker-compose exec php composer install --no-dev para poder instalar las dependencias
- hay que ejecutar el comando docker-compose exec php bin/console doctrine:database:create --if-not-exists para crear la base de datos en aso de que no exista
- luego del comando ejecutar el siguiente docker-compose exec php bin/console doctrine:migrations:migrate --no-interaction se ocupara para aplicar todas las migraciones para luego crear las tablas
## una vez ejecutado los comandos se pueden crear los usuarios
- Crear un usuario player o admin
- en mi caso ocupo el puerto 8080
- urls para usuarios player http://localhost:8080/register
- urls para usuarios administrador http://localhost:8080/register-admin
## segundo paso -> la mayoria de las funciones como categorias, preguntas, trivias, respuestas se manejan desde el controlador de AdminController
## se deben crear primero en el orden dejado para evitar problemas ya que algunas dependen de otras para funcionar
- Logear cuenta Admin
- 1) Crear Categorias
- 2) Crear Preguntas (Asociadas a una Categoría).
- 3) Crear Trivias (Asociadas a una Pregunta; solo una debe ser la correcta).
- 4) Crear Respuestas (Asociadas a un conjunto de Preguntas).

## tercer paso -> logear cuenta player y jugar alguna trivia creada previamente
## una vez creado todo en el home http://localhost:8080 se veran las trivias,
##  y si esta logeado con algun usuario se podra participar en dicha trivia
- Logear cuenta Player y acceder al Home (http://localhost:8080).
- En el Home, se listan las trivias disponibles y se muestra el Top 5 jugadores con mayor ranking.
- Iniciar Juego: Elegir una trivia y hacer click en el botón "Jugar".
- Desarrollo: Se listan las preguntas, donde cada una tiene un puntaje que se sumará al finalizar.
- Finalización: El puntaje obtenido se guarda en la base de datos. Los métodos para comenzar el juego (/play/start) y para enviar los resultados (/api/play/Participation/{id}/submit) residen en el PlayController