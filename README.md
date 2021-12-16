# Odoo Wordpress Plugin

## Intro

ES:

Este plugin fue desarrollado para sincronizar Usuarios de Wordpress con Contactos de Odoo. El plugin se puede extender en base a la API de Odoo, se uso la v13:

https://www.odoo.com/documentation/13.0/webservices/odoo.html

[Referencia de modelos Odoo](https://www.odoo.com/documentation/13.0/reference/orm.html#reference-orm-model)

Se usa la biblioteca Ripcord: [Ver en GitHub](https://github.com/poef/ripcord)

El servidor en caso de ser apache debe tener activa la extensión XML-RPC.


EN:

This plugin was developed to synchronize Wordpress Users with Odoo Contacts. The plugin can be extended based on the Odoo API, v13 was used:

https://www.odoo.com/documentation/13.0/webservices/odoo.html

[Model Reference Odoo](https://www.odoo.com/documentation/13.0/reference/orm.html#reference-orm-model)

Ripcord library is used: [Go to GitHub](https://github.com/poef/ripcord)

The server, if it is Apache, must have the XML-RPC extension active.


## Activación / Activate

ES:

Una vez instalado el plugin, ir al menu Odoo Plugin del administrador y agregar los datos de conexión a Odoo.

**Odoo URL** debe tener el formato **_http://localhost:8069_** donde **_localhost_** puede ser una IP, la barra **"/"** al final de la url NO se usa.

**Odoo USER** y **Odoo PASSWORD** deben ser de un usuario administrador de Odoo.

**Odoo DATABASE** es el nombre de la base de datos en uso de Odoo. [Cómo encontrar el nombre de la base](https://www.odoo.com/documentation/user/13.0/es/general/developer_mode/activate.html#:~:text=Vaya%20a%20Configuraci%C3%B3n%20%2D%3E%20Activar%20el,el%20modo%20desarrollador%20est%C3%A1%20disponible)


EN: 

Once the plugin is installed, go to the administrator's Odoo Plugin menu and add the connection data to Odoo.

**Odoo URL** must have the format **_http: // localhost: 8069_** where **_localhost_** can be an IP, the slash **"/"** at the end of the url is NOT used.

**Odoo USER** and **Odoo PASSWORD** must be from an Odoo administrator user.

**Odoo DATABASE** is the name of the Odoo database in use. [How to find the base name](https://www.odoo.com/documentation/user/13.0/es/general/developer_mode/activate.html#:~:text=Vaya%20a%20Configuraci%C3%B3n%20%2D%3E%20Activar%20el,el%20modo%20desarrollador%20est%C3%A1%20disponible)


## Uso / Usage

ES:

Una vez configurado el plugin, en el menú **_Contactos_** podemos sincronizar todos los contactos. Dependiendo de la cantidad de contactos a sincronizar y la velocidad del servidor de Odoo puede demorar este proceso. Paciencia.

En caso de usar **Woocommerce**, los clientes que completen los pedidos también ingresan a los clientes de Odoo.

EN: 

Once the plugin is configured, in the **__Contacts_** menu we can synchronize all the contacts. Depending on the number of contacts to be synchronized and the speed of the Odoo server, this process may take time. You must have patience.

In case of using **Woocommerce**, customers who complete the orders also enter Odoo customers.

### Thank you!

Juan Iriart - [Genosha DEV](https://genosha.com.ar)

![Genosha](genosha-logo.png)