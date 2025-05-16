CREATE TABLE users (
    username VARCHAR PRIMARY KEY,
    name VARCHAR NOT NULL,
    password VARCHAR NOT NULL,
    email VARCHAR NOT NULL,
    admin_power BOOLEAN,
    bio VARCHAR,
    profile_photo VARCHAR(255), 
    skills TEXT                  
);

CREATE TABLE services (
    id INTEGER PRIMARY KEY,
    pricing INTEGER NOT NULL,
    delivery_time VARCHAR,
    description VARCHAR,
    images VARCHAR,
    rating INTEGER,
    status VARCHAR,
    user VARCHAR REFERENCES users NOT NULL
);

CREATE TABLE comments (
    id INTEGER PRIMARY KEY,
    text VARCHAR,
    service_id INTEGER REFERENCES services,
    user VARCHAR REFERENCES users NOT NULL,
    replie_to INTEGER REFERENCES comments
);

CREATE TABLE transactions (
    id INTEGER PRIMARY KEY,
    user VARCHAR REFERENCES users,
    service_id INTEGER REFERENCES services,
    rating INTEGER
);

CREATE TABLE categories (
    name VARCHAR PRIMARY KEY
);

CREATE TABLE cate_serv (
    service_id INTEGER REFERENCES services,
    categorie VARCHAR REFERENCES categories
);

CREATE TABLE media (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    service_id INTEGER NOT NULL REFERENCES services(id),
    file_path TEXT NOT NULL,
    media_type TEXT CHECK(media_type IN ('image', 'video')) NOT NULL
);