# Event Flow — кастомный модуль для Drupal 10/11

Модуль управления мероприятиями с регистрацией участников и интеграцией с Weather API.

## Возможности

- Создание и управление событиями (CRUD)
- Регистрация авторизованных пользователей на события через кнопку
- Автоматическое закрытие регистрации при достижении максимума участников
- Просмотр списка зарегистрированных участников (только для администраторов)
- Виджет текущей погоды на странице события (по координатам места проведения)
- Кеширование данных погоды на 10 минут

## Поля сущности Event

| Поле | Тип | Описание |
|------|-----|----------|
| title | string | Название события |
| description | text_long | Описание события |
| start_date | datetime | Дата и время начала |
| end_date | datetime | Дата и время окончания |
| latitude | float | Широта места проведения |
| longitude | float | Долгота места проведения |
| max_participants | integer | Максимальное количество участников |
| status | list_string | Статус: active / completed |

## Права доступа

| Действие | Кто имеет доступ |
|----------|-----------------|
| Создание/редактирование/удаление событий | Администратор |
| Просмотр списка участников | Администратор |
| Просмотр событий | Все пользователи |
| Регистрация на событие | Авторизованные пользователи |

## Маршруты

| URL | Описание |
|-----|----------|
| `/events` | Публичный список всех событий |
| `/event/add` | Создание нового события |
| `/event/{id}` | Просмотр события (с кнопкой регистрации и погодой) |
| `/event/{id}/edit` | Редактирование события |
| `/event/{id}/delete` | Удаление события |
| `/event/{id}/register` | Регистрация/отмена регистрации |
| `/admin/content/events` | Админ-панель: список событий |
| `/admin/content/events/{id}/participants` | Просмотр участников события |
| `/event/{id}/weather` | JSON-эндпоинт погоды |

## Требования

- Drupal 10 или 11
- PHP 8.1+
- PostgreSQL
- Модули: `datetime`, `text`, `options`

## Установка

### С Docker (рекомендуется)

```bash
git clone <repository-url>
cd EventFlow
docker compose up -d
```

1. Открыть http://localhost:8181
2. Установить Drupal:
   - Профиль: Standard
   - База данных: PostgreSQL
   - Database: `drupal`, User: `drupal`, Password: `drupal`
   - Advanced → Host: `postgres`
3. Перейти в `/admin/modules` → найти **Event Flow** → Install
4. Готово

### Без Docker

1. Скопировать папку `event_flow/` в `modules/custom/` Drupal-сайта
2. Включить модуль:
   ```bash
   drush en event_flow
   ```

## Структура модуля

```
event_flow/
├── event_flow.info.yml              # Метаданные модуля
├── event_flow.install               # Хуки установки/удаления
├── event_flow.module                # Хуки: тема, extra fields, view
├── event_flow.permissions.yml       # Права доступа
├── event_flow.routing.yml           # Маршруты
├── event_flow.services.yml          # Сервисы (WeatherService)
├── event_flow.libraries.yml         # CSS-библиотека
├── event_flow.links.menu.yml        # Ссылки в меню
├── event_flow.links.task.yml        # Вкладки на странице события
├── event_flow.links.action.yml      # Кнопка "Add event"
├── css/
│   └── event-flow.css               # Стили модуля
├── src/
│   ├── Entity/
│   │   ├── Event.php                # Сущность Event
│   │   └── EventRegistration.php    # Сущность регистрации
│   ├── Controller/
│   │   ├── EventListController.php          # Публичный список событий
│   │   ├── EventRegistrationController.php  # Регистрация/отмена
│   │   ├── EventParticipantsController.php  # Список участников
│   │   └── EventWeatherController.php       # JSON-эндпоинт погоды
│   ├── Form/
│   │   ├── EventForm.php            # Форма создания/редактирования
│   │   └── EventDeleteForm.php      # Форма подтверждения удаления
│   ├── Service/
│   │   └── WeatherService.php       # Интеграция с Open-Meteo API
│   ├── EventAccessControlHandler.php
│   ├── EventRegistrationAccessControlHandler.php
│   └── EventListBuilder.php         # Админ-список событий
└── templates/
    ├── event-list.html.twig                 # Шаблон списка (карточки)
    ├── event-weather.html.twig              # Виджет погоды
    └── event-registration-button.html.twig  # Кнопка регистрации
```

## Weather API

Модуль использует бесплатный API [Open-Meteo](https://open-meteo.com/) — ключ не требуется.

Погода запрашивается по координатам события и кешируется на 10 минут. На странице события отображается:
- Температура
- Описание погоды
- Скорость ветра

## Технические детали

- **База данных**: используется Entity API Drupal, все таблицы создаются автоматически. Совместимо с PostgreSQL.
- **Кеширование**: погода кешируется через стандартный `cache.default` backend Drupal с TTL 600 секунд.
- **Автозакрытие регистрации**: после каждой регистрации проверяется количество участников. При достижении максимума статус события меняется на `completed`.
- **Каскадное удаление**: при удалении события все связанные регистрации удаляются автоматически.
- **CSRF-защита**: маршрут регистрации защищён токеном.