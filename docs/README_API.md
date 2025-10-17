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
- 2) Crear Preguntas
- 3) Crear Trivias
- 4) Crear Respuestas 

## tercer paso -> logear cuenta player y jugar alguna trivia creada previamente
## una vez creado todo en el home http://localhost:8080 se veran las trivias,
##  y si esta logeado con algun usuario se podra participar en dicha trivia
- elegir trivia y hacer click al boton de jugar
- se listaran las preguntas relacionadas a las trivias cada pregunta tiene un puntaje el cual al finalizar se sumaran
- al final de la trivia se guarda el puntaje obtenido, y en el inicio de la pagina mostrara el top 5 jugadores con mayor ranking 
## en PlayController estan los metodos para comenzar el juego y para enviar los resultados