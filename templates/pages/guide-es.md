# Guía en Español

Esta guía explica cómo crear un nuevo sitio web usando esta base y los proyectos de [Docker](https://derafu.org/docker-php-caddy-server) y [PHP Deployer](https://derafu.org/deployer) para el desarrollo local y despliegue en el servidor de producción.

[TOC]

## Revisar lo que necesitas hacer, instalar o saber

### Clave SSH en equipo local

Es necesario tener una clave SSH en el equipo local, esta clave será utilizada para configurar tu contenedor Docker.

Con el siguiente comando se obtiene una clave preexistente o se crea una nueva en `$HOME/.ssh/id_rsa` y `$HOME/.ssh/id_rsa.pub`. Si se crea una nueva deberás ingresar el correo para identificar al usuario dueño de la clave. Si lo prefieres, puedes ingresar tu usuario y nombre de equipo (en caso que tengas múltiples claves SSH en diferentes ambientes).

```shell
if [ -f $HOME/.ssh/id_rsa.pub ]; then
    cat $HOME/.ssh/id_rsa.pub
else
    read -p "Ingresa tu correo: " COMMENT
    ssh-keygen -t rsa -b 4096 -N "" -C "$COMMENT" -f $HOME/.ssh/id_rsa
    cat $HOME/.ssh/id_rsa.pub
fi
```

Con este comando la clave pública será mostrada en pantalla. Si la necesitas volver a ver en el futuro ejecuta:

```shell
cat $HOME/.ssh/id_rsa.pub
```

**Importante**: La clave en `$HOME/.ssh/id_rsa` es **privada** y jamás debe ser compartida.

### Agregar clave SSH a GitHub

1. Ir a [GitHub](https://github.com/settings/ssh/new).
2. En `Title` ingresar lo mismo que hayas elegido como correo o comentario de la clave al crearla.
3. En `Key` pegar la clave pública extraída con `cat $HOME/.ssh/id_rsa.pub`.
4. Haz click en `Add SSH key`.

### Contenedor Docker con PHP y Caddy

Instalar [Docker](https://derafu.org/docker-php-caddy-server) en `$DOCKER_DIR` en el equipo local:

```shell
DEV_DIR=$HOME/dev
DOCKER_DIR=$DEV_DIR/docker-sites-php
mkdir -p $DEV_DIR
git clone https://github.com/derafu/docker-php-caddy-server.git $DOCKER_DIR
cat $HOME/.ssh/id_rsa.pub > $DOCKER_DIR/config/ssh/authorized_keys
cd $DOCKER_DIR
docker-compose up -d
```

Con esta configuración quedará en `$DOCKER_DIR/sites` la carpeta donde se instalarán los sitios a desarrollar. Esta carpeta estará compartida entre el equipo local y el contenedor Docker.

### Conectar al contenedor Docker

Ingresa al contenedor Docker directamente con:

```shell
docker exec -it derafu-sites-server-php-caddy bash
su - admin
```

También puedes ingresar al contenedor Docker mediante SSH:

```shell
ssh admin@localhost -p 2222
```

O bien, si tienes configurado el alias `dev` en `$HOME/.ssh/config` de tu equipo local:

```shell
ssh dev
```

Si prefieres acceder con el alias `dev` en tu equipo local, deberás configurar el alias en tu equipo local:

```shell
echo "
Host dev
    HostName localhost
    User admin
    Port 2222
    IdentityFile $HOME/.ssh/id_rsa
" >> $HOME/.ssh/config
```

**Nota**: El nombre del alias puede ser el que quieras, en este caso se usó `dev`.

### PHP Deployer

Conectate al contenedor Docker e instala [PHP Deployer](https://derafu.org/deployer) en `$DEPLOYER_DIR` dentro del contenedor:

```shell
#DEPLOYER_DIR=$HOME/deployer # Esta variable ya está definida en el contenedor.
git clone git@github.com:derafu/deployer.git $DEPLOYER_DIR
cd $DEPLOYER_DIR
composer update
```

### Configuración de sitios web

El archivo `$DEPLOYER_DIR/sites.php` contiene la configuración de los sitios web que se desplegarán:

```php
return [
    'www.example.com' => 'git@github.com:example/example.git',
];
```

En este caso el sitio web es `www.example.com` y el repositorio es `git@github.com:example/example.git`. Deberás agregar a este archivo la configuración de todos los sitios web que podrías desplegar.

### Clave SSH en el contenedor Docker

Para hacer deploy al servidor de producción, es necesario que la clave SSH en el equipo local sea agregada al contenedor Docker.

En tu equipo local, ejecuta:

```shell
scp -P 2222 $HOME/.ssh/id_rsa* admin@localhost:.ssh/
```

O bien, si tienes configurado el alias `dev` en `$HOME/.ssh/config` de tu equipo local:

```shell
scp $HOME/.ssh/id_rsa* dev:.ssh/
```

**Nota**: Si prefieres mantener claves SSH diferentes para el contenedor Docker deberás crear una nueva clave SSH. Puedes usar el mismo comando que se usó para crear la clave SSH en el equipo local.

## Desarrollar un sitio web

### Crear un nuevo sitio web

Ingresar al contenedor y ejecuta:

```shell
#SITES_DIR=$HOME/sites # Esta variable ya está definida en el contenedor.
cd $SITES_DIR
composer create-project derafu/website www.example.com --stability=dev
```

### Clonar un sitio web

Ingresar al contenedor y ejecuta:

```shell
#SITES_DIR=$HOME/sites # Esta variable ya está definida en el contenedor.
cd $SITES_DIR
git clone git@github.com:example/example.git www.example.com
cd www.example.com
composer install
```

**Nota**: Si el nombre del repositorio no es el mismo que el nombre del sitio web, deberás asignar, como en este ejemplo, explícitamente el nombre de la carpeta del sitio web. Si el nombre del repositorio es el mismo que el nombre del sitio web, puedes omitir el nombre de la carpeta.

## Desplegar un sitio web a producción

Todas estas instrucciones se ejecutan en el contenedor Docker.

### Agregar sitio web en el archivo de configuración

Asegurate de que el sitio web esté agregado en el archivo `$DEPLOYER_DIR/sites.php`. Puedes validar esto ejecutando:

```shell
cd $DEPLOYER_DIR
cat sites.php | grep 'www.example.com'
```

Si el sitio web no está agregado, deberás agregarlo en el archivo `$DEPLOYER_DIR/sites.php`.

### Pruebas de estilo, calidad de código y pruebas unitarias

Realiza pruebas de estilo, calidad de código y pruebas unitarias en el contenedor Docker con:

```shell
cd $SITES_DIR/www.example.com
composer phpcs
composer phpstan
composer tests
```

### Subir cambios a GitHub

Si todo está correcto sube los cambios a GitHub:

```shell
git add .
git commit -m "Actualización de sitio web"
git push
```

### Despliegue

Si no hay errores, puedes hacer deploy al servidor de producción con:

```shell
cd $DEPLOYER_DIR
DEPLOYER_HOST=hosting.example.com
vendor/bin/dep derafu:deploy:single --site=www.example.com
```

Acá debes modificar el host de producción y el sitio web que se desplegará.

- `DEPLOYER_HOST`: host de producción, en este ejemplo es `hosting.example.com`.
- `--site`: sitio web que se desplegará, en este ejemplo es `www.example.com`.

Si ocurre algún error al hacer el deploy y tratas de hacer un deploy nuevo, es muy probable que el deploy esté bloqueado. Si esto sucede, puedes desbloquear y hacer deploy nuevamente con:

```shell
DEPLOYER_HOST=hosting.example.com
vendor/bin/dep derafu:deploy:single --site=www.example.com --unlock
```

## Actualizar componentes

### Actualizar Docker

En tu equipo local, ejecuta:

```shell
cd $DOCKER_DIR
git pull
docker-compose build --no-cache
docker-compose up -d
```

**Nota**: Deberás volver a configurar el contenedor Docker con los pasos previos.

### Actualizar PHP Deployer

Ingresa al contenedor y ejecuta:

```shell
cd $DEPLOYER_DIR
git pull
composer update
```

### Actualizar sitio web

Ingresar al contenedor y ejecuta:

```shell
cd $SITES_DIR/www.example.com
git pull
composer install
```

## Uso de herramientas de desarrollo

### Composer

Instalar dependencias:

```shell
composer install
```

Agregar dependencia de desarrollo:

```shell
composer require --dev nombre-de-la-dependencia
```

Agregar dependencia de producción:

```shell
composer require nombre-de-la-dependencia
```

Eliminar dependencia:

```shell
composer remove nombre-de-la-dependencia
```

Actualizar dependencias:

```shell
composer update
```

**Nota**: Normalmente solo se usa `composer install` para instalar dependencias.

### NPM

Instalar dependencias:

```shell
npm install
```

Agregar dependencia de desarrollo:

```shell
npm install --save-dev nombre-de-la-dependencia
```

Agregar dependencia de producción:

```shell
npm install --save nombre-de-la-dependencia
```

Eliminar dependencia:

```shell
npm uninstall nombre-de-la-dependencia
```

Actualizar dependencias:

```shell
npm update
```

**Nota**: Normalmente solo se usa `npm install` para instalar dependencias.

### Git

Ver el estado de los cambios:

```shell
git status
```

Agregar cambios:

```shell
git add .
```

Hacer commit:

```shell
git commit -m "Mensaje del commit"
```

Subir cambios a GitHub:

```shell
git push
```

Actualizar repositorio local:

```shell
git pull
```

Deshacer cambios:

```shell
git checkout -- .
```

**Nota**: En vez de utilizar punto `.` puedes especificar los archivos que deseas agregar, hacer commit o revertir.

### PHP CS Fixer

Revisar estilo de código:

```shell
composer phpcs
```

Corregir estilo de código:

```shell
composer phpcs-fix
```

### PHP Unit

Ejecutar pruebas unitarias:

```shell
composer tests
```

## Uso de terminal en el contenedor Docker

Ingresar al directorio de un sitio web:

```shell
cd $SITES_DIR/www.example.com
```

Salir del directorio:

```shell
cd ..
```

Listar archivos:

```shell
ls -la
```

Ver contenido de un archivo:

```shell
cat $DEPLOYER_DIR/sites.php
```

Editar un archivo:

```shell
nano $DEPLOYER_DIR/sites.php
```

Guardar y salir en `nano`:

```shell
Ctrl + X
```
