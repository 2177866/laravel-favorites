# Laravel Favorites
[![Latest Stable Version](https://img.shields.io/packagist/v/alyakin/favorites.svg?style=flat-square)](https://packagist.org/packages/alyakin/favorites)
[![Latest Unstable Version](https://img.shields.io/packagist/vpre/alyakin/favorites.svg?style=flat-square)](https://packagist.org/packages/alyakin/favorites)
[![Total Downloads](https://img.shields.io/packagist/dt/alyakin/favorites.svg?style=flat-square)](https://packagist.org/packages/alyakin/favorites)
[![Laravel Version](https://img.shields.io/badge/laravel-8+-orange.svg?style=flat-square)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/php-8.1+-blue.svg?style=flat-square)](https://php.net)
[![License](https://img.shields.io/github/license/2177866/laravel-favorites.svg?style=flat-square)](LICENSE)
[![Tests](https://github.com/2177866/laravel-favorites/actions/workflows/favorites-tests.yml/badge.svg)](https://github.com/2177866/laravel-favorites/actions)

## 📦 Описание

`Laravel Favorites` — это универсальный модуль для создания полиморфных связей между владельцем и любыми избираемыми сущностями.
Позволяет одному владельцу (профилю, пользователю и т.п.) добавлять в избранное объекты разных моделей, таких как товары, статьи, профили, комментарии и др.

Пакет поддерживает:
- организацию избранного по папкам с уникальными названиями,
- работу с UUID и timestamps "из коробки",
- строгую привязку избранного к конкретному владельцу.

## 🎯 Подходит для

- 💼 **Электронной коммерции** — покупатель может сохранять избранные товары, бренды или подборки.
- 📚 **Контент-платформ** — читатель может добавлять в избранное статьи, авторов или хештеги.
- 📊 **B2B/CRM систем** — пользователь может группировать клиентов, документы или сделки в "избранное" для быстрого доступа.

## 🧭 Оглавление

- [Системные требования](#-системные-требования)
- [Установка](#-установка)
- [Как использовать](#-как-использовать)
  - [➕ Добавление в избранное](#-добавление-в-избранное)
  - [➖ Удаление из избранного](#-удаление-из-избранного)
  - [📂 Работа с папками](#-работа-с-папками)
  - [📄 Получение избранного](#-получение-избранного)
- [Кастомизация](#-кастомизация)
  - [🔄 Пример: замена логики добавления](#-пример-замена-логики-добавления)
- [🤝 Сотрудничество](#-сотрудничество)
- [📄 Лицензия](#-лицензия)

## 🛠 Системные требования

- PHP **8.1** или выше
- Laravel **10.x** или выше
- Composer для управления зависимостями

## 📥 Установка

Установите пакет через Composer:

```bash
composer require alyakin/favorites
```

Файл конфигурации отсутствует — модуль готов к использованию сразу после установки и регистрации провайдера.

## ⚙️ Как использовать

Сервисы можно получить через сервис-контейнер Laravel:

```php
use \Alyakin\Favorites\Services\{FavoriteService, FavoriteFolderService};

$favoritesService = app(FavoriteService::class);
$folderService = app(FavoriteFolderService::class);
```

Ниже представлены базовые сценарии использования.

### ➕ Добавление в избранное

```php
// Добавить объект в избранное
$favoritesService->addToFavorites($ownerId, $model);

// Добавить в конкретную папку:
$favoritesService->addToFavorites($ownerId, $model, 'read later');
```

### ➖ Удаление из избранного

```php
// Удалить объект из избранного:
$favoritesService->removeFromFavorites($ownerId, $model);
```

### 📂 Работа с папками

```php
// Создать папку
$folderService->createFolder($ownerId, 'Папка');

// Переименовать папку
$folderService->renameFolder($ownerId, $folderId, $newName);

// Переместить элемент в папку:
$favoritesService->moveToFolder($favoriteId, $folderId);

// Получить все папки владельца:
$folderService->getAllFoldersForOwner($ownerId);

// Удалить папку:
// ⚠️ ВНИМАНИЕ! При удалении папки будут также удалены все избранные элементы внутри неё.
$folderService->deleteFolder($ownerId, $folderId);
```

### 📄 Получение избранного

```php
// Получить все избранные элементы владельца:
$favoritesService->getFavorites($ownerId);

// Получить избранные в конкретной папке:
$favoritesService->getFavorites($ownerId, 'funy');

// Проверить, находится ли объект в избранном:
$favoritesService->isFavorited($ownerId, $model);
```

## 🎛 Кастомизация

Пакет спроектирован с учетом расширяемости и адаптации под проект.

- Вы можете заменить модель владельца, передавая любой UUID-совместимый `ownerId`.
- Папки не являются обязательными — можно использовать избранное и без них.
- Вся бизнес-логика изолирована в сервисах (`FavoriteService`, `FavoriteFolderService`) и может быть легко расширена или переопределена через DI-контейнер.
- Названия таблиц, столбцов и ключей соответствуют общим стандартам Laravel и могут быть переопределены при необходимости.

### 🔄 Пример: замена логики добавления

Создайте свой кастомный сервис:

```php
class CustomFavoriteService extends \Alyakin\Favorites\Services\FavoriteService {
    // ...
}
```

Переопределите нужный метод:

```php
public function addToFavorites(string $ownerId, Model $model, ?string $folderName = null): Favorite
{
    // своя логика — например, логирование
    \Log::info('Добавление в избранное', ['owner_id' => $ownerId]);

    return parent::addToFavorites($ownerId, $model, $folderName);
}
```

Зарегистрируйте в AppServiceProvider:

```php
$this->app->bind(
    \Alyakin\Favorites\Services\FavoriteService::class,
    \App\Services\CustomFavoriteService::class
);
```

Теперь Laravel будет использовать вашу реализацию вместо базовой.

## 🤝 Сотрудничество

Мы открыты к предложениям и улучшениям!

- Сообщайте об ошибках через [Issues](https://github.com/2177866/laravel-favorites/issues)
- Присылайте Pull Requests с улучшениями
- Предлагайте идеи по расширению функциональности

## 📄 Лицензия

This package is open-source and available under the [MIT License](LICENSE).