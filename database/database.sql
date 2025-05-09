PRAGMA FOREIGN KEYS ON;

DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS service_image;
DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS cate_serv;

CREATE TABLE users (
    username VARCHAR PRIMARY KEY,
    name VARCHAR NOTNULL,
    password VARCHAR NOTNULL,
    email VARCHAR NOTNULL UNIQUE,
    admin BOOLEAN
);

CREATE TABLE services (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    pricing INTEGER NOTNULL,
    delivery_time VARCHAR,
    description VARCHAR,
    rating INTEGER,
    --status VARCHAR, -- talvez seja na transactions
    user VARCHAR REFERENCES users NOTNULL
);

CREATE TABLE service_image (
    id varchar PRIMARY KEY,
    services INTEGER REFERENCES services
);

CREATE TABLE comments (
    id INTEGER PRIMARY KEY,
    comment VARCHAR,
    service_id INTEGER REFERENCES services,
    user VARCHAR REFERENCES users NOTNULL,
    replie_to INTEGER REFERENCES comments
);

CREATE TABLE transactions (
    id INTEGER PRIMARY KEY,
    user VARCHAR REFERENCES users,
    service_id INTEGER REFERENCES services,
    status VARCHAR,
    rating INTEGER --don't know
);

CREATE TABLE categories (
    category VARCHAR PRIMARY KEY
    --services INTEGER REFERENCES services
);

CREATE TABLE cate_serv (
    service_id INTEGER REFERENCES services,
    category VARCHAR REFERENCES categories
);