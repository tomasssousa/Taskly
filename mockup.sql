CREATE TABLE users (
    username VARCHAR PRIMARY KEY,
    name VARCHAR NOTNULL,
    password VARCHAR NOTNULL,
    email VARCHAR NOTNULL,
    admin_power BOOLEAN
);

CREATE TABLE services (
    id INTEGER PRIMARY KEY,
    --category VARCHAR REFERENCES categories,
    pricing INTEGER NOTNULL,
    delivery_time VARCHAR,
    description VARCHAR,
    images VARCHAR, --??????????? table imagens que sabem o serviço?
    rating INTEGER,
    status VARCHAR, -- talvez seja na transactions
    user VARCHAR REFERENCES users NOTNULL
);

CREATE TABLE comments (
    id INTEGER PRIMARY KEY,
    text VARCHAR,
    service_id INTEGER REFERENCES services,
    user VARCHAR REFERENCES users NOTNULL,
    replie_to INTEGER REFERENCES comments
);

CREATE TABLE transactions (
    id INTEGER PRIMARY KEY,
    user VARCHAR REFERENCES users,
    service_id INTEGER REFERENCES services,
    rating INTEGER, --don't know
);

CREATE TABLE categories (
    name VARCHAR PRIMARY KEY
);

CREATE TABLE cate_serv (
    service_id INTEGER REFERENCES services,
    categorie VARCHAR REFERENCES categories
);