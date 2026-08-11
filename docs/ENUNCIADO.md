PRUEBA TÉCNICA
Sistema de Cotización y Venta de Seguro de Viaje
Objetivo
Desarrollar una aplicación web sencilla que permita a un cliente cotizar y contratar un seguro de viaje en línea.
El objetivo de la prueba no es construir un sistema completo de seguros, sino evaluar criterios de arquitectura, desarrollo backend y frontend, manejo de datos, validaciones, integración con servicios externos y calidad general del código.
________________________________________
Stack requerido
•	Laravel
•	Vue.js
•	Base de datos MySQL
•	Git

El candidato podrá utilizar las versiones y librerías adicionales que considere apropiadas.
Debe explicar brevemente las decisiones técnicas tomadas.
________________________________________
Caso de negocio
Una compañía de seguros desea ofrecer un seguro de viaje mediante su página web.
El usuario deberá poder ingresar sus datos, seleccionar el país al que viajará, indicar las fechas del viaje y obtener una cotización.
Posteriormente podrá confirmar la contratación del seguro.
________________________________________
1. Cotización
Crear una pantalla que permita ingresar como mínimo:
Datos del asegurado
•	Nombres
•	Apellidos
•	Número de identificación
•	Correo electrónico
•	Fecha de nacimiento
Datos del viaje
•	País de destino
•	Fecha de salida
•	Fecha de regreso
El desarrollador deberá implementar las validaciones que considere necesarias, tanto en frontend como en backend.
Poder descargar la cotización en PDF
________________________________________
2. Integración con API externa
La lista de países deberá obtenerse desde una API pública.
Puede utilizarse:
REST Countries API
https://restcountries.com/
De esta API se puede obtener, por ejemplo:
•	Nombre del país
•	Código ISO
•	Región
•	Bandera
El candidato deberá decidir cómo integrar esta información dentro de la aplicación.
Se valorará el manejo adecuado de:
•	errores del servicio externo;
•	timeouts;
•	respuestas inesperadas;
•	disponibilidad temporal del API.
________________________________________
3. Cálculo de la cotización
Implementar una lógica sencilla para determinar el valor del seguro.
Se puede utilizar la siguiente regla:
Tarifa base
USD 3 por cada día de viaje.
Ejemplo:
Viaje de 10 días:
10 × $3 = $30
Recargo según región
Agregar los siguientes recargos:
Región	Recargo
South America	0%
North America	15%
Europe	20%
Asia	25%
Africa	20%
Oceania	25%
Ejemplo:
Viaje de 10 días a España:
Tarifa base:
10 × $3 = $30
Recargo Europa:
$30 × 20% = $6
Total
$36
La forma en que esta lógica sea organizada dentro de Laravel queda a criterio del desarrollador.
Se evaluará especialmente este punto.
________________________________________
4. Confirmación de contratación
Después de generar una cotización, el usuario deberá poder seleccionar:
Contratar seguro
Al confirmar la contratación deberá almacenarse como mínimo:
•	asegurado;
•	destino;
•	fechas del viaje;
•	cantidad de días;
•	tarifa base;
•	porcentaje de recargo;
•	valor total;
•	fecha de contratación;
•	estado.
Los estados mínimos serán:
•	Cotizado
•	Contratado
No es necesario integrar una pasarela de pagos real.
________________________________________
5. Consulta de contrataciones
Crear una pantalla donde se puedan visualizar las cotizaciones/seguros registrados.
Como mínimo mostrar:
•	Cliente
•	Identificación
•	Destino
•	Fecha de salida
•	Fecha de regreso
•	Valor
•	Estado
•	Fecha de creación
Puede utilizarse paginación, filtros o búsqueda si el desarrollador lo considera necesario.
________________________________________
Base de datos
La estructura de la base de datos deberá construirse utilizando:
•	Migraciones de Laravel
•	Relaciones Eloquent
•	Seeders cuando se considere necesario
No se deberá entregar un archivo SQL como mecanismo principal para crear la estructura de datos.
________________________________________
Arquitectura
El candidato tiene libertad para definir la arquitectura de la solución.
Debe evitar concentrar toda la lógica de negocio directamente dentro de Controllers o componentes Vue.
No existe una única arquitectura correcta.
El candidato deberá incluir en el README una pequeña explicación de la arquitectura seleccionada y las razones de su elección.
________________________________________
Backend
Se evaluará el uso adecuado de Laravel, incluyendo aspectos como:
•	Routing
•	Controllers
•	Form Requests
•	Models
•	Eloquent
•	Migrations
•	Services o Actions, si considera necesarios
•	Manejo de excepciones
•	Responses
•	Validaciones
•	Buenas prácticas
Debe explicar su decisión.
________________________________________
Frontend
La interfaz deberá desarrollarse utilizando Vue.js.
No buscamos un diseño gráfico complejo.
La aplicación deberá funcionar correctamente tanto en escritorio como en dispositivos móviles.
________________________________________
Validaciones
El candidato deberá proponer e implementar las validaciones que considere apropiadas.
Queremos evaluar el criterio del desarrollador para definirlas.
________________________________________
Pruebas automatizadas
Implementar al menos algunas pruebas automatizadas, usar PEST.
Se evaluará qué decide probar el desarrollador y cómo estructura las pruebas.
________________________________________
README
El repositorio deberá incluir un README con:
Instalación
Pasos necesarios para ejecutar el proyecto.
Por ejemplo:
•	clonar repositorio;
•	instalar dependencias;
•	configurar .env;
•	crear base de datos;
•	ejecutar migraciones;
•	ejecutar seeders;
•	instalar dependencias frontend;
•	levantar proyecto.
Arquitectura
Explicar brevemente:
•	arquitectura utilizada;
•	organización de la lógica de negocio;
•	integración con REST Countries;
•	decisiones técnicas relevantes.
Mejoras futuras *
Indicar qué aspectos mejoraría si el proyecto evolucionara hacia un sistema de producción.
________________________________________
Entregables
El candidato deberá compartir:
1.	Enlace al repositorio de GitHub, compartido con vrubio@gestionsegura.com.ec.
2.	README con instrucciones de instalación.
3.	Migraciones necesarias para levantar la base de datos.
4.	Código backend Laravel.
5.	Código frontend Vue.
6.	Pruebas automatizadas.
7.	Breve explicación de la arquitectura propuesta.
No incluir credenciales, contraseñas ni archivos .env con información sensible.
________________________________________
Tiempo sugerido
La prueba está diseñada para realizarse aproximadamente en:
6 a 8 horas.
No buscamos una aplicación terminada a nivel comercial.
En caso de no completar alguna funcionalidad, el candidato puede indicarlo en el README explicando:
•	qué falta;
•	cómo lo implementaría;
•	qué decisión tomaría.
Preferimos una solución sencilla, bien estructurada y entendible antes que una solución excesivamente compleja.
________________________________________
Criterios de evaluación
Área	Peso
Arquitectura y organización del código	20%
Laravel / Backend	20%
Vue.js / Frontend	15%
Modelado de base de datos y migraciones	10%
Lógica de negocio y validaciones	15%
Integración con API externa	5%
Pruebas automatizadas	10%
Git, documentación y calidad general	5%
Total: 100%
________________________________________
Bonus
Los siguientes puntos son opcionales y no son necesarios para aprobar la prueba:
•	Docker.
•	TypeScript.
•	Pinia.
•	Autenticación.
•	CI con GitHub Actions.
•	Factories.
•	Mayor cobertura de pruebas.
•	Caché de la API externa.
•	Logs estructurados.
•	Diseño UI más elaborado.
Estos elementos únicamente serán considerados si aportan valor real a la solución y no agregan complejidad innecesaria.
