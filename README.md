<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Proyecto Parroquia Santo Domingo

Este proyecto incluye sitio público (Blade + Tailwind) y stack interno (Jetstream Livewire, Filament futuro, Spatie Permission).

### Livewire 404 (/livewire/livewire.js)
Si ves un 404 en producción para `livewire/livewire.js` revisa la configuración Nginx.

#### Nginx ejemplo
```
server {
	server_name basilicadelrosario.gt www.basilicadelrosario.gt;
	root /var/www/santo-domingo/public;
	index index.php;

	location / {
		try_files $uri $uri/ /index.php?$query_string;
	}

	# (Opcional) refuerzo explícito
	location ^~ /livewire/ {
		try_files $uri /index.php?$query_string;
	}

	location ~* \.(?:css|js|mjs|json|map|jpg|jpeg|png|webp|gif|svg|ico|woff2?)$ {
		try_files $uri =404;
		access_log off;
		expires 30d;
		add_header Cache-Control "public, immutable";
	}

	location ~ \.php$ {
		include snippets/fastcgi-php.conf;
		fastcgi_pass unix:/run/php/php8.4-fpm.sock; # ajustar versión
		fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
		include fastcgi_params;
	}

	location ~* /(\.env|artisan|composer\.(?:json|lock)|vendor|storage|\.git) {
		deny all;
		return 404;
	}
}
```

Pasos tras desplegar cambios:
```
php artisan optimize:clear
php artisan config:clear
```

### Usuarios internos y contraseñas

Los seeders crean los usuarios **superadmin**, **Secretaría** y **Padre** basados en las variables definidas en `.env` (`SUPERADMIN_*`, `SECRETARIA_*`, `PADRE_*`).

- Si el usuario ya existe, la contraseña **no se sobreescribe** automáticamente.
- Para forzar un cambio masivo pon `SEED_FORCE_PASSWORDS=true` (o usa los flags específicos `*_PASSWORD_FORCE=true`). Úsalo solo de forma puntual.
- Para regenerar credenciales manualmente utiliza los comandos:
	- `php artisan app:superadmin-reset --password="NuevaClave"`
	- `php artisan user:superadmin-password --password="NuevaClave"`

Recuerda actualizar las variables en `.env` para que nuevos entornos compartan la misma información base.

---

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
