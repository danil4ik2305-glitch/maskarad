-- База данных веб-приложения «Маскарад» (прокат костюмов)
DROP DATABASE IF EXISTS costume_rental;
CREATE DATABASE costume_rental CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE costume_rental;

CREATE TABLE roles (
  role_id INT AUTO_INCREMENT PRIMARY KEY,
  role_name VARCHAR(30) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  role_id INT NOT NULL DEFAULT 2,
  first_name VARCHAR(50) NOT NULL,
  last_name VARCHAR(50) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  phone VARCHAR(20) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (role_id) REFERENCES roles(role_id)
) ENGINE=InnoDB;

CREATE TABLE categories (
  category_id INT AUTO_INCREMENT PRIMARY KEY,
  category_name VARCHAR(50) NOT NULL,
  description VARCHAR(255),
  image_url VARCHAR(255)
) ENGINE=InnoDB;

CREATE TABLE costumes (
  costume_id INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT NOT NULL,
  costume_name VARCHAR(100) NOT NULL,
  description TEXT,
  gender ENUM('Женский','Мужской','Унисекс','Детский') NOT NULL,
  sizes VARCHAR(50) NOT NULL,
  price_per_day DECIMAL(8,2) NOT NULL,
  deposit DECIMAL(8,2) NOT NULL,
  image_url VARCHAR(255),
  FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE booking_statuses (
  status_id INT AUTO_INCREMENT PRIMARY KEY,
  status_name VARCHAR(30) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE bookings (
  booking_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  costume_id INT NOT NULL,
  status_id INT NOT NULL DEFAULT 1,
  size VARCHAR(10) NOT NULL,
  date_from DATE NOT NULL,
  date_to DATE NOT NULL,
  total_price DECIMAL(10,2) NOT NULL,
  comment VARCHAR(255),
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (costume_id) REFERENCES costumes(costume_id) ON DELETE CASCADE,
  FOREIGN KEY (status_id) REFERENCES booking_statuses(status_id)
) ENGINE=InnoDB;

CREATE TABLE reviews (
  review_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  costume_id INT NOT NULL,
  rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
  review_text TEXT NOT NULL,
  is_approved TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (costume_id) REFERENCES costumes(costume_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE feedback (
  feedback_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  name VARCHAR(50) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  message TEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------- Данные ----------
INSERT INTO roles (role_name) VALUES ('Администратор'), ('Пользователь');

INSERT INTO users (role_id, first_name, last_name, email, phone, password_hash, created_at) VALUES
(1, 'Админ', 'Маскарад', 'admin@maskarad.ru', '+7 (495) 123-45-67', '$2y$10$u0QOL0lJbk/utpPHsnKzSu4sNXINTrmfhprtRHbe9MNSq59SN1uj6', '2026-09-21 10:05:00'),
(2, 'Даниил', 'Сулайманов', 'daniil@mail.ru', '+7 (916) 555-12-34', '$2y$10$a9/ylwd3jIiaeqTAcFUxxutAe0oGrqIDG6zRum.NeUJBtjRGsM4r6', '2026-09-21 12:14:00'),
(2, 'Анна', 'Петрова', 'anna@mail.ru', '+7 (903) 111-22-33', '$2y$10$a9/ylwd3jIiaeqTAcFUxxutAe0oGrqIDG6zRum.NeUJBtjRGsM4r6', '2026-09-21 15:40:00'),
(2, 'Игорь', 'Смирнов', 'igor@mail.ru', '+7 (925) 444-55-66', '$2y$10$a9/ylwd3jIiaeqTAcFUxxutAe0oGrqIDG6zRum.NeUJBtjRGsM4r6', '2026-09-22 09:22:00'),
(2, 'Мария', 'Ли', 'maria@mail.ru', '+7 (977) 777-88-99', '$2y$10$a9/ylwd3jIiaeqTAcFUxxutAe0oGrqIDG6zRum.NeUJBtjRGsM4r6', '2026-09-22 11:47:00');

INSERT INTO categories (category_name, description, image_url) VALUES
('Исторические', 'Костюмы разных эпох для балов и реконструкций', 'https://images.unsplash.com/photo-1783231607195-38662c407218?w=600&q=70'),
('Карнавальные', 'Венецианские и маскарадные образы', 'https://images.unsplash.com/photo-1533022586528-2e09bde0959b?w=600&q=70'),
('Детские', 'Костюмы для утренников и праздников', 'https://images.unsplash.com/photo-1519340241574-2cec6aef0c01?w=600&q=70'),
('Хэллоуин', 'Ведьмы, вампиры и другие мистические образы', 'https://images.unsplash.com/photo-1601830976337-e32f60eba315?w=600&q=70'),
('Тематические', 'Образы для тематических вечеринок', 'https://images.unsplash.com/photo-1667129702857-74261f4243a9?w=600&q=70');

INSERT INTO costumes (category_id, costume_name, description, gender, sizes, price_per_day, deposit, image_url) VALUES
(1, 'Бальное платье «Екатерина»', 'Пышное платье в стиле эпохи Екатерины II из атласа с кружевной отделкой. В комплекте кринолин, веер и перчатки.', 'Женский', 'S,M', 3500, 5000, 'https://images.unsplash.com/photo-1783231607195-38662c407218?w=600&q=70'),
(2, 'Венецианский костюм «Казанова»', 'Камзол, плащ, треуголка и маска в венецианском стиле.', 'Мужской', 'M,L', 2800, 4000, 'https://images.unsplash.com/photo-1533022586528-2e09bde0959b?w=600&q=70'),
(3, 'Костюм супергероя (детский)', 'Комбинезон с маской, ткань стрейч.', 'Детский', '110,122,128', 1200, 1500, 'https://images.unsplash.com/photo-1519340241574-2cec6aef0c01?w=600&q=70'),
(4, 'Костюм ведьмы «Мортиция»', 'Платье, шляпа и накидка для Хэллоуина.', 'Женский', 'S,M,L', 1800, 2500, 'https://images.unsplash.com/photo-1601830976337-e32f60eba315?w=600&q=70'),
(3, 'Костюм тыквы (детский)', 'Объёмный костюм тыквы с шапочкой.', 'Детский', '98,104,116', 900, 1000, 'https://images.unsplash.com/photo-1509163245925-f4255dea7727?w=600&q=70'),
(2, 'Маска «Коломбина» с плащом', 'Золотая маска ручной работы и бархатный плащ.', 'Унисекс', 'ONE', 1500, 2000, 'https://images.unsplash.com/photo-1745429806387-6224c9940799?w=600&q=70'),
(5, 'Костюм гангстера 1920-х', 'Костюм-тройка в полоску, шляпа федора и подтяжки.', 'Мужской', 'M,L,XL', 2200, 3000, 'https://images.unsplash.com/photo-1783231607706-5163185f4b3a?w=600&q=70'),
(1, 'Платье «Бал дебютанток»', 'Белое платье с корсетом для исторических балов.', 'Женский', 'XS,S,M', 3200, 5000, 'https://images.unsplash.com/photo-1781408453044-e0185c42425d?w=600&q=70');

INSERT INTO booking_statuses (status_name) VALUES ('Новая'), ('Подтверждена'), ('Выдан'), ('Завершена'), ('Отменена');

INSERT INTO bookings (user_id, costume_id, status_id, size, date_from, date_to, total_price, comment, created_at) VALUES
(2, 1, 1, 'M', '2026-10-30', '2026-11-01', 7000, 'Нужна подгонка по длине', '2026-09-23 10:15:00'),
(2, 3, 3, '122', '2026-09-22', '2026-09-23', 1200, NULL, '2026-09-22 13:30:00'),
(2, 4, 4, 'M', '2025-10-30', '2025-11-01', 3600, NULL, '2026-09-21 16:02:00'),
(3, 2, 2, 'L', '2026-10-10', '2026-10-11', 2800, NULL, '2026-09-22 17:45:00'),
(4, 7, 3, 'L', '2026-09-23', '2026-09-25', 4400, 'Для корпоратива', '2026-09-22 12:10:00'),
(5, 6, 4, 'ONE', '2026-02-20', '2026-02-21', 1500, NULL, '2026-09-21 18:20:00'),
(3, 8, 4, 'S', '2026-01-15', '2026-01-16', 3200, NULL, '2026-09-21 19:05:00'),
(4, 5, 5, '104', '2025-12-25', '2025-12-26', 900, 'Отменили утренник', '2026-09-22 10:00:00');

INSERT INTO reviews (user_id, costume_id, rating, review_text, is_approved, created_at) VALUES
(3, 1, 5, 'Брали костюм на исторический бал — сидел идеально, всё чистое. Бронировали через сайт, очень удобно.', 1, '2026-09-21 20:10:00'),
(4, 3, 5, 'Сыну на утренник взяли костюм супергероя. Дёшево и быстро, забрали за 10 минут.', 1, '2026-09-22 14:30:00'),
(5, 4, 4, 'Большой выбор для Хэллоуина, помогли подобрать размер по телефону.', 1, '2026-09-22 19:55:00'),
(2, 4, 5, 'Отличный костюм, все были в восторге!', 0, '2026-09-23 11:20:00');

INSERT INTO feedback (user_id, name, phone, message, created_at) VALUES
(NULL, 'Ольга', '+7 (926) 000-11-22', 'Есть ли костюм Снегурочки размера L на 31 декабря?', '2026-09-22 16:40:00'),
(2, 'Даниил', '+7 (916) 555-12-34', 'Можно ли продлить прокат на один день?', '2026-09-23 09:35:00');

-- ---------- Представления ----------
-- 1. Каталог костюмов с категорией и рейтингом
CREATE VIEW v_costume_catalog AS
SELECT c.costume_id, c.costume_name, cat.category_name, c.gender, c.sizes, c.price_per_day,
       ROUND(AVG(r.rating),1) AS avg_rating, COUNT(r.review_id) AS reviews_count
FROM costumes c JOIN categories cat ON cat.category_id = c.category_id
LEFT JOIN reviews r ON r.costume_id = c.costume_id AND r.is_approved = 1
GROUP BY c.costume_id;

-- 2. Активные бронирования (не завершены и не отменены)
CREATE VIEW v_active_bookings AS
SELECT b.booking_id, CONCAT(u.last_name,' ',u.first_name) AS client, u.phone, c.costume_name, b.size,
       b.date_from, b.date_to, b.total_price, s.status_name
FROM bookings b JOIN users u ON u.user_id = b.user_id JOIN costumes c ON c.costume_id = b.costume_id
JOIN booking_statuses s ON s.status_id = b.status_id
WHERE s.status_name IN ('Новая','Подтверждена','Выдан');

-- 3. Выручка по категориям (без отменённых)
CREATE VIEW v_revenue_by_category AS
SELECT cat.category_name, COUNT(b.booking_id) AS bookings_count, SUM(b.total_price) AS revenue
FROM categories cat JOIN costumes c ON c.category_id = cat.category_id
JOIN bookings b ON b.costume_id = c.costume_id
WHERE b.status_id <> 5
GROUP BY cat.category_id;

-- 4. Самые популярные костюмы
CREATE VIEW v_popular_costumes AS
SELECT c.costume_name, cat.category_name, COUNT(b.booking_id) AS times_rented
FROM costumes c JOIN categories cat ON cat.category_id = c.category_id
LEFT JOIN bookings b ON b.costume_id = c.costume_id AND b.status_id <> 5
GROUP BY c.costume_id ORDER BY times_rented DESC;

-- 5. Статистика клиентов
CREATE VIEW v_client_stats AS
SELECT u.user_id, CONCAT(u.last_name,' ',u.first_name) AS client, u.email,
       COUNT(b.booking_id) AS bookings_count, COALESCE(SUM(b.total_price),0) AS total_spent
FROM users u LEFT JOIN bookings b ON b.user_id = u.user_id AND b.status_id <> 5
WHERE u.role_id = 2
GROUP BY u.user_id;
