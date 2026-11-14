# Support Desk

[![Clean install](https://github.com/imexfil/support-system/actions/workflows/clean-install.yml/badge.svg)](https://github.com/imexfil/support-system/actions/workflows/clean-install.yml) [![MIT License](https://img.shields.io/badge/license-MIT-blue)](LICENSE)

[Download the latest release](https://github.com/imexfil/support-system/releases/latest)

[English](#english) · [Русский](#русский)

## English

Support Desk is a standalone Laravel demo for private customer support. Customers create and track tickets, agents work from a shared inbox, and administrators manage the support team.

- Search tickets by subject or number and switch between open and closed queues.
- Triage tickets by department, priority, and status.
- Follow replies as they appear automatically every four seconds.
- Distinguish Customer, Support, and Administrator replies by role and name.

### Run locally

Requires PHP 8.4+, Composer, and the PDO SQLite extension. This app uses Blade and ships with its CSS and JavaScript, so no Node.js build is needed. Run these commands in a fresh checkout:

```sh
git clone https://github.com/imexfil/support-system.git
cd support-system
composer install
php -r "copy('.env.example', '.env'); touch('database/database.sqlite');"
php artisan key:generate
php artisan migrate
php artisan desk:admin
php artisan serve --host=127.0.0.1 --port=8090
```

Open [http://127.0.0.1:8090](http://127.0.0.1:8090). The administrator command prompts for your name, email, and password.

### Walkthrough

1. Register a customer in a private browser window. Open **Support**, enter a subject and description, choose a department and priority, and send the ticket.
2. Sign in as administrator in another window. Open **Support inbox → Open**, select the ticket, send a reply, and set its status to **In review**.
3. Return to the customer window. The reply and status update appear automatically. Close the ticket as administrator to move it to **Closed**.
4. Register a separate agent account. As administrator, open **Team**, find the account by name or email, and select **Assign Support role**. The agent can open **Support inbox** directly after signing in.

The demo uses a fresh SQLite database and initials for avatars. It ships without accounts or credentials. Email delivery and production deployment are outside the scope of this example.

## Русский

Support Desk — самостоятельный пример службы поддержки на Laravel. Клиенты создают приватные обращения, сотрудники работают с общей очередью, а администраторы управляют командой.

- Поиск по теме или номеру. Отдельные списки открытых и закрытых обращений.
- Отделы, приоритеты и статусы, включая «На рассмотрении» и «Приостановлено».
- Переписка обновляется каждые четыре секунды.
- У ответов указаны имя автора и роль: клиент, сотрудник поддержки или администратор.

### Локальный запуск

Нужны PHP 8.4+, Composer и расширение PDO SQLite. В свежей копии репозитория выполни команды из раздела [Run locally](#run-locally). Команда `php artisan desk:admin` попросит ввести имя, email и пароль первого администратора. После запуска открой [http://127.0.0.1:8090](http://127.0.0.1:8090).

### Как попробовать

1. В приватном окне зарегистрируй клиента. На странице **Support** заполни тему и описание, выбери отдел и приоритет и отправь обращение.
2. В другом окне войди как администратор. Открой **Support inbox → Open**, выбери обращение, отправь ответ и установи **In review**.
3. Вернись в окно клиента. Ответ и новый статус появятся автоматически. После закрытия администратором обращение перейдёт в **Closed**.
4. Зарегистрируй отдельный аккаунт сотрудника. Войдя как администратор, открой **Team**, найди его по имени или email и нажми **Assign Support role**. Сотрудник сможет открыть **Support inbox** прямо после входа.

Для демо создаётся отдельная SQLite-база. Вместо фотографий используются инициалы. Аккаунты и пароли создаются локально. Интерфейс демо на английском, отправка почты и настройки для продакшена в пример не входят.

### Первый запуск из архива

Распакуй архив релиза, открой терминал в папке проекта и начни с `composer install`. При первом запуске миграции создают пустые таблицы пользователей, обращений и сообщений. Если хочешь посмотреть только клиентский интерфейс, можно сразу зарегистрироваться и пропустить создание администратора.

### License / Лицензия

Project source is distributed under the [MIT License](LICENSE). The bundled Inter font retains its [SIL Open Font License](public/assets/Inter-LICENSE.txt).
